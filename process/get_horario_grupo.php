<?php

$pdo = require __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (!isset($_GET['id_grupo'])) {

    echo json_encode([
        "success" => false,
        "message" => "ID no recibido"
    ]);
    exit;

}

$id_grupo = $_GET['id_grupo'];


// =========================
// VERIFICAR SI ES ESPECIAL
// =========================

$stmt = $pdo->prepare("
    SELECT 
        dia_semana,
        hora_inicio,
        hora_fin
    FROM horarios_especiales
    WHERE id_grupo = ?
    ORDER BY dia_semana
");

$stmt->execute([$id_grupo]);

$especiales = $stmt->fetchAll();


// =========================
// SI EXISTE HORARIO ESPECIAL
// =========================

if ($especiales) {

    $horarios = [];

    foreach ($especiales as $h) {

        $horarios[] =
            $h['dia_semana'] . ' ' .
            substr($h['hora_inicio'],0,5) .
            ' - ' .
            substr($h['hora_fin'],0,5);

    }

    echo json_encode([
        "success" => true,
        "tipo" => "especial",
        "horario" => implode(" | ", $horarios)
    ]);

    exit;

}


// =========================
// SI NO EXISTE → HORARIO NORMAL
// =========================

$stmt = $pdo->prepare("
    SELECT 
        dias,
        hora_inicio,
        hora_fin
    FROM grupos
    WHERE id_grupo = ?
");

$stmt->execute([$id_grupo]);

$grupo = $stmt->fetch();

if ($grupo) {

    $horario =
        $grupo['dias'] .
        ' ' .
        substr($grupo['hora_inicio'],0,5) .
        ' - ' .
        substr($grupo['hora_fin'],0,5);

    echo json_encode([
        "success" => true,
        "tipo" => "normal",
        "horario" => $horario
    ]);

} else {

    echo json_encode([
        "success" => false
    ]);

}
