<?php

header("Content-Type: application/json; charset=utf-8");

// =========================
// CONEXIÓN A MYSQL
// =========================
try {
    $pdo = require __DIR__ . '/../config/db.php';
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión a la base de datos"
    ]);
    exit;
}

// =========================
// RECIBIR DATOS
// =========================
$id_asistencia = $_POST['id_asistencia'] ?? null;

if (!$id_asistencia) {
    echo json_encode([
        "success" => false,
        "message" => "ID de asistencia no recibido"
    ]);
    exit;
}

// =========================
// REGISTRAR SALIDA SOLO PRACTICANTES
// =========================
try {
    $stmt = $pdo->prepare("
        UPDATE asistencias_practicantes
        SET hora_salida = CURTIME()
        WHERE id_asistencia = ?
    ");

    $stmt->execute([$id_asistencia]);

    if ($stmt->rowCount() === 0) {
        echo json_encode([
            "success" => false,
            "message" => "No se encontró la asistencia del practicante para registrar salida"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Salida registrada correctamente"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}