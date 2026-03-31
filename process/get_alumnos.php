<?php

require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

header('Content-Type: application/json');

$pdo = require __DIR__ . '/../config/db.php';

try {

    $id_grupo = $_GET['id_grupo'] ?? null;

    if (!$id_grupo) {
        throw new Exception("ID de grupo requerido");
    }

    // Obtener alumnos del grupo
    $stmt = $pdo->prepare("
        SELECT 
            a.id_alumno,
            a.nombre,
            a.dni,
            a.telefono,
            a.telefonopadres,
            a.telefonoapoderado,
            a.contacto_pago,
            m.id_matricula
        FROM alumnos a
        INNER JOIN matriculas m ON m.id_alumno = a.id_alumno
        WHERE m.id_grupo = ? AND a.fecha_baja IS NULL
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
