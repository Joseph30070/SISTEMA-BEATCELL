<?php

require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

header('Content-Type: application/json');

$pdo = require __DIR__ . '/../config/db.php';

try {

    $fecha = $_GET['fecha'] ?? date('Y-m-d');

    $stmtCrear = $pdo->prepare("INSERT INTO asistencias_practicantes (
            id_practicante,
            fecha,
            estado
        )
        SELECT
            p.id_practicante,
            ?,
            'Pendiente'
        FROM practicantes p
        WHERE p.fecha_baja IS NULL
        AND NOT EXISTS (
            SELECT 1
            FROM asistencias_practicantes ap
            WHERE ap.id_practicante = p.id_practicante
            AND ap.fecha = ?
        )");

    $stmtCrear->execute([$fecha, $fecha]);

    $stmt = $pdo->prepare("SELECT
            p.id_practicante,
            p.id_carrera,
            p.nombre,
            p.dni,
            p.telefono,
            c.nombre_carrera AS carrera,
            p.horario,
            ap.id_asistencia,
            ap.hora_entrada,
            ap.hora_salida,
            COALESCE(ap.estado, 'Pendiente') AS estado
        FROM practicantes p
        LEFT JOIN carreras c
            ON c.id_carrera = p.id_carrera
        LEFT JOIN asistencias_practicantes ap
            ON ap.id_practicante = p.id_practicante
            AND ap.fecha = ?
        WHERE p.fecha_baja IS NULL
        ORDER BY p.nombre ASC");

    $stmt->execute([$fecha]);
    $practicantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'practicantes' => $practicantes
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);

}
