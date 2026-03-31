<?php

require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

$pdo = require __DIR__ . '/../config/db.php';

try {

    $id_alumno = $_POST['id_alumno'] ?? null;
    $fecha = $_POST['fecha'] ?? null;
    $hora_entrada = $_POST['hora_entrada'] ?? null;
    $hora_salida = $_POST['hora_salida'] ?? null;
    $tareas_asignadas = $_POST['tareas_asignadas'] ?? null;
    $tareas_terminadas = $_POST['tareas_terminadas'] ?? null;

    if (!$id_alumno || !$fecha || !$hora_entrada) {
        throw new Exception("Faltan datos obligatorios");
    }

    // Validar que el alumno exista
    $stmt = $pdo->prepare("SELECT id_alumno FROM alumnos WHERE id_alumno = ?");
    $stmt->execute([$id_alumno]);
    if (!$stmt->fetch()) {
        throw new Exception("El alumno no existe");
    }

    // Insertar asistencia
    $stmt = $pdo->prepare("
        INSERT INTO asistencias 
        (id_alumno, fecha, hora_entrada, hora_salida, tareas_asignadas, tareas_terminadas)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $id_alumno,
        $fecha,
        $hora_entrada,
        $hora_salida ?: null,
        $tareas_asignadas ?: null,
        $tareas_terminadas ?: null
    ]);

    header("Location: ../public/registro_alumnos.php?success=Asistencia registrada correctamente");
    exit;

} catch (Exception $e) {

    header("Location: ../public/registro_alumnos.php?error=" . urlencode($e->getMessage()));
    exit;
}
