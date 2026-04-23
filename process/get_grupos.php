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

    // =========================
    // Traer todos los grupos del curso
    // =========================
    $stmt = $pdo->prepare("
        SELECT 
            g.id_grupo,
            g.nombre_grupo
        FROM grupos g
        WHERE g.id_curso = ?
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
        'error' => 'Error en BD: ' . $e->getMessage()
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}