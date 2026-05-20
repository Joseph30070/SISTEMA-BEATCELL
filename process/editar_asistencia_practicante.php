<?php

header("Content-Type: application/json; charset=utf-8");

try {
    $pdo = require __DIR__ . '/../config/db.php';
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión a la base de datos"
    ]);
    exit;
}

$id_asistencia = $_POST['id_asistencia'] ?? null;
$estado = $_POST['estado'] ?? null;
$hora_entrada = $_POST['hora_entrada'] ?? null;
$hora_salida = $_POST['hora_salida'] ?? null;
$observacion = trim($_POST['observacion'] ?? '');

if (!$id_asistencia || !$estado) {
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos"
    ]);
    exit;
}

$estadosPermitidos = ['Pendiente', 'Asistió', 'Ausente', 'Tarde', 'Justificado'];

if (!in_array($estado, $estadosPermitidos)) {
    echo json_encode([
        "success" => false,
        "message" => "Estado no permitido"
    ]);
    exit;
}

$hora_entrada = $hora_entrada !== '' ? $hora_entrada : null;
$hora_salida = $hora_salida !== '' ? $hora_salida : null;

if (($estado === 'Justificado' || $observacion !== '') && strlen($observacion) < 3) {
    echo json_encode([
        "success" => false,
        "message" => "Debe ingresar una observación válida"
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE asistencias_practicantes
        SET 
            estado = :estado,
            hora_entrada = :hora_entrada,
            hora_salida = :hora_salida,
            observacion = :observacion
        WHERE id_asistencia = :id_asistencia
    ");

    $stmt->execute([
        ':estado' => $estado,
        ':hora_entrada' => $hora_entrada,
        ':hora_salida' => $hora_salida,
        ':observacion' => $observacion,
        ':id_asistencia' => $id_asistencia
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Asistencia del practicante actualizada correctamente"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}