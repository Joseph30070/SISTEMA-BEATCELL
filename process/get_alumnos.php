<?php

require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

header('Content-Type: application/json');

$pdo = require __DIR__ . '/../config/db.php';

try {

    $id_grupo = $_GET['id_grupo'] ?? null;

    // VALIDAR PRIMERO

    if (!$id_grupo) {
        throw new Exception("ID de grupo requerido");
    }

    // ======================================
    // CREAR ASISTENCIAS DEL DÍA SI NO EXISTEN
    // ======================================

    $stmtCrear = $pdo->prepare("
        INSERT INTO asistencias (
            id_alumno,
            fecha,
            estado
        )
        SELECT 
            a.id_alumno,
            CURDATE(),
            'Pendiente'
        FROM alumnos a
        INNER JOIN matriculas m 
            ON m.id_alumno = a.id_alumno
        WHERE m.id_grupo = ?
        AND a.fecha_baja IS NULL
        AND NOT EXISTS (
            SELECT 1
            FROM asistencias s
            WHERE s.id_alumno = a.id_alumno
            AND s.fecha = CURDATE()
        )
    ");

    $stmtCrear->execute([$id_grupo]);

    // ======================================
    // OBTENER ALUMNOS
    // ======================================

    $stmt = $pdo->prepare("
        SELECT 
            a.id_alumno,
            a.nombre,
            a.dni,
            a.telefono,
            a.telefonopadres,
            a.telefonoapoderado,
            a.contacto_pago,
            m.id_matricula,

            asl.id_asistencia,
            asl.hora_entrada,
            asl.hora_salida,

            COALESCE(asl.estado, 'Pendiente') AS estado

        FROM alumnos a

        INNER JOIN matriculas m 
            ON m.id_alumno = a.id_alumno

        LEFT JOIN asistencias asl 
            ON asl.id_alumno = a.id_alumno 
            AND asl.fecha = CURDATE()

        WHERE m.id_grupo = ? 
        AND a.fecha_baja IS NULL

        ORDER BY a.nombre ASC
    ");

    $stmt->execute([$id_grupo]);

    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'alumnos' => $alumnos
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);

}

