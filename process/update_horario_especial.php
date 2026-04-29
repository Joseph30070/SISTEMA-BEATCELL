<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR']);

$pdo = require __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$idGrupo = $_POST['id_grupo'] ?? null;
$dias = $_POST['dias'] ?? [];

if (!$idGrupo) {
    echo json_encode(["success" => false, "message" => "ID inválido"]);
    exit;
}

try {

    $pdo->beginTransaction();

    // 1. eliminar anteriores
    $stmt = $pdo->prepare("DELETE FROM horarios_especiales WHERE id_grupo = ?");
    $stmt->execute([$idGrupo]);

    // 2. insertar nuevos
    foreach ($dias as $dia) {

        $inicio = $_POST['hora_inicio'][$dia] ?? '';
        $fin = $_POST['hora_fin'][$dia] ?? '';

        if ($inicio === '' || $fin === '') {
            continue;
        }

        $stmt = $pdo->prepare("
            INSERT INTO horarios_especiales
            (id_grupo, dia_semana, hora_inicio, hora_fin)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$idGrupo, $dia, $inicio, $fin]);
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Horario actualizado"
    ]);

} catch (Exception $e) {

    $pdo->rollBack();

    echo json_encode([
        "success" => false,
        "message" => "Error al actualizar"
    ]);
}

