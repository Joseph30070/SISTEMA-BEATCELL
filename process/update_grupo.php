<?php
$pdo = require __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// =========================
// VALIDAR DATOS
// =========================
if (
    !isset($_POST['id_grupo']) ||
    !isset($_POST['nombre_grupo']) ||
    !isset($_POST['hora_inicio']) ||
    !isset($_POST['hora_fin'])
) {
    echo json_encode([
        "success" => false,
        "message" => "Faltan datos"
    ]);
    exit;
}

$id = $_POST['id_grupo'];
$nombre = $_POST['nombre_grupo'];
$inicio = $_POST['hora_inicio'];
$fin = $_POST['hora_fin'];

// días (checkbox)
$dias = isset($_POST['dias']) ? $_POST['dias'] : [];
$dias_texto = implode(", ", $dias);

// =========================
// UPDATE
// =========================
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

if ($ok) {
    echo json_encode([
        "success" => true,
        "message" => "Grupo actualizado correctamente"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error al actualizar"
    ]);
}

