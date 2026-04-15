<?php
$pdo = require __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$idGrupo = $_POST['id_grupo'];

$dias = $_POST['dias'] ?? [];

if(!$idGrupo){
    echo json_encode(["success" => false]);
    exit;
}

/* 1. BORRAR TODO EL HORARIO ANTERIOR */
$stmt = $pdo->prepare("DELETE FROM horarios_especiales WHERE id_grupo = ?");
$stmt->execute([$idGrupo]);

/* 2. INSERTAR NUEVOS HORARIOS */
foreach($dias as $dia){

    $inicio = $_POST['hora_inicio'][$dia];
    $fin = $_POST['hora_fin'][$dia];

    $stmt = $pdo->prepare("
        INSERT INTO horarios_especiales
        (id_grupo, dia_semana, hora_inicio, hora_fin)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$idGrupo, $dia, $inicio, $fin]);
}

echo json_encode([
    "success" => true,
    "message" => "Horario actualizado"
]);

