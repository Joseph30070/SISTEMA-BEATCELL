<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR']);

$pdo = require __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$id = $_POST['id_grupo'] ?? null;
$nombre = trim($_POST['nombre_grupo'] ?? '');
$inicio = $_POST['hora_inicio'] ?? '';
$fin = $_POST['hora_fin'] ?? '';

if (!$id || $nombre === '' || $inicio === '' || $fin === '') {
    echo json_encode([
        "success" => false,
        "message" => "Faltan datos"
    ]);
    exit;
}

$dias = $_POST['dias'] ?? [];
$dias_texto = is_array($dias) ? implode(", ", $dias) : '';

$stmt = $pdo->prepare("
    UPDATE grupos
    SET 
        nombre_grupo = ?,
        hora_inicio = ?,
        hora_fin = ?,
        dias = ?
    WHERE id_grupo = ?
");

$ok = $stmt->execute([
    $nombre,
    $inicio,
    $fin,
    $dias_texto,
    $id
]);

echo json_encode([
    "success" => $ok,
    "message" => $ok ? "Grupo actualizado correctamente" : "Error al actualizar"
]);

