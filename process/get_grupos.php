<?php

require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

header('Content-Type: application/json');

$pdo = require __DIR__ . '/../config/db.php';

try {

    $id_curso = $_GET['id_curso'] ?? null;

    if (!$id_curso) {
        throw new Exception("ID de curso requerido");
    }

    // Obtener grupos del curso (con valores por defecto si faltan columnas)
    $stmt = $pdo->prepare("
        SELECT 
            g.id_grupo,
            g.nombre_grupo,
            COALESCE(g.dias, 'Sin especificar') as dias,
            COALESCE(g.hora_inicio, '08:00') as hora_inicio,
            COALESCE(g.hora_fin, '12:00') as hora_fin
        FROM grupos g
        WHERE g.id_curso = ? AND g.id_grupo IS NOT NULL
        ORDER BY g.nombre_grupo ASC
    ");

    $stmt->execute([$id_curso]);
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'grupos' => $grupos,
        'count' => count($grupos)
    ]);

} catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        'error' => 'Error en BD: ' . $e->getMessage(),
        'code' => $e->getCode()
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);

}
