<?php

header("Content-Type: application/json");

// ======================================
// CONEXIÓN A MYSQL (USANDO db.php)
// ======================================

try {

    $pdo = require __DIR__ . '/../config/db.php';

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => "Error de conexión a la base de datos"
    ]);
    exit;
}

// ======================================
// RECIBIR DATOS
// ======================================

$id_asistencia = $_POST['id_asistencia'] ?? null;
$estado = $_POST['estado'] ?? null;
$tipo = $_POST['tipo'] ?? 'alumno';

if (!$id_asistencia || !$estado) {

    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos"
    ]);
    exit;
}

$tabla = 'asistencias';
if ($tipo === 'practicante') {
    $tabla = 'asistencias_practicantes';
}

// ======================================
// ACTUALIZAR ESTADO
// ======================================

try {

    if ($estado === "Asistió") {

        $sql = "
            UPDATE {$tabla}
            SET 
                estado = :estado,
                hora_entrada = NOW()
            WHERE id_asistencia = :id
        ";

    } else {

        $sql = "
            UPDATE {$tabla}
            SET 
                estado = :estado
            WHERE id_asistencia = :id
        ";

    }

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(":estado", $estado);
    $stmt->bindParam(":id", $id_asistencia);

    $stmt->execute();

    echo json_encode([
        "success" => true
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

}

