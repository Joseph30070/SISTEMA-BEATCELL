<?php

require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

$pdo = require __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// 🔥 IMPORTANTE: zona horaria correcta
date_default_timezone_set('America/Lima');

try {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['asistencias'])) {
        throw new Exception("No se recibieron datos");
    }

    $asistencias = $data['asistencias'];

    $pdo->beginTransaction();

    foreach ($asistencias as $ast) {

        $id_alumno = $ast['id_alumno'] ?? null;
        $fecha = $ast['fecha'] ?? null;

        $hora_entrada = $ast['hora_entrada'] ?? null;
        $hora_salida  = $ast['hora_salida'] ?? null;

        if (!$id_alumno || !$fecha) {
            throw new Exception("Datos incompletos");
        }

        // =========================
        // VALIDAR ALUMNO ACTIVO
        // =========================
        $stmt = $pdo->prepare("
            SELECT id_alumno 
            FROM alumnos 
            WHERE id_alumno = ? AND fecha_baja IS NULL
        ");
        $stmt->execute([$id_alumno]);

        if (!$stmt->fetch()) {
            throw new Exception("Alumno no válido o dado de baja");
        }

        // =========================
        // VALIDAR HORARIO (ANTES DE GUARDAR)
        // =========================
        $stmt = $pdo->prepare("
            SELECT g.hora_inicio, g.hora_fin
            FROM matriculas m
            INNER JOIN grupos g ON g.id_grupo = m.id_grupo
            WHERE m.id_alumno = ?
            LIMIT 1
        ");

        $stmt->execute([$id_alumno]);
        $grupo = $stmt->fetch();

        if (!$grupo) {
            throw new Exception("Alumno sin grupo asignado");
        }

        $hora_actual = date('H:i:s');

        if ($hora_actual < $grupo['hora_inicio']) {
            throw new Exception("⏳ La clase aún no empieza");
        }

        if ($hora_actual > $grupo['hora_fin']) {
            throw new Exception("⚠ La clase ya terminó");
        }

        // =========================
        // VERIFICAR SI YA EXISTE REGISTRO
        // =========================
        $stmt = $pdo->prepare("
            SELECT id_asistencia, hora_salida 
            FROM asistencias 
            WHERE id_alumno = ? AND fecha = ?
        ");
        $stmt->execute([$id_alumno, $fecha]);
        $existe = $stmt->fetch();

        if ($existe) {

            // =========================
            // REGISTRAR SALIDA (SIN SOBREESCRIBIR)
            // =========================
            if ($hora_salida) {

                if ($existe['hora_salida']) {
                    throw new Exception("La salida ya fue registrada");
                }

                $stmt = $pdo->prepare("
                    UPDATE asistencias 
                    SET hora_salida = ?
                    WHERE id_asistencia = ?
                ");

                $stmt->execute([$hora_salida, $existe['id_asistencia']]);

            }

            // =========================
            // OPCIONAL: actualizar entrada (si quieres)
            // =========================
            elseif ($hora_entrada) {

                $stmt = $pdo->prepare("
                    UPDATE asistencias 
                    SET hora_entrada = ?
                    WHERE id_asistencia = ?
                ");

                $stmt->execute([$hora_entrada, $existe['id_asistencia']]);
            }

        } else {

            // =========================
            // INSERT NUEVO (SOLO ENTRADA)
            // =========================
            if ($hora_entrada) {

                $stmt = $pdo->prepare("
                    INSERT INTO asistencias 
                    (id_alumno, fecha, hora_entrada)
                    VALUES (?, ?, ?)
                ");

                $stmt->execute([$id_alumno, $fecha, $hora_entrada]);
            }

        }

    }

    $pdo->commit();

    echo json_encode([
        "success" => true
    ]);

} catch (Exception $e) {

    $pdo->rollBack();

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);

}