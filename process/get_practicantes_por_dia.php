<?php

require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'SECRETARIO']);

header('Content-Type: application/json; charset=utf-8');

$pdo = require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers/horario_utils.php';

try {
    $fecha = $_GET['fecha'] ?? date('Y-m-d');
    $idCarrera = $_GET['id_carrera'] ?? '';

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode([
            'success' => false,
            'error' => 'Fecha inválida'
        ]);
        exit;
    }

    $params = [];

    $sql = "
        SELECT
            p.id_practicante,
            p.id_carrera,
            p.nombre,
            p.dni,
            p.telefono,
            p.modalidad_horario,
            p.tipo_horario,
            p.horario,
            c.nombre_carrera AS carrera
        FROM practicantes p
        LEFT JOIN carreras c
            ON c.id_carrera = p.id_carrera
        WHERE p.fecha_baja IS NULL
    ";

    if ($idCarrera !== '') {
        $sql .= " AND p.id_carrera = ? ";
        $params[] = (int)$idCarrera;
    }

    $sql .= " ORDER BY p.nombre ASC ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $practicantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $resultado = [];

    foreach ($practicantes as $p) {
        $infoHorario = extraerHorarioPorFecha($p['horario'] ?? '', $fecha);

        if (!$infoHorario['tiene_horario']) {
            continue;
        }

        $stmtBuscar = $pdo->prepare("
            SELECT
                id_asistencia,
                hora_entrada,
                hora_salida,
                estado,
                observacion
            FROM asistencias_practicantes
            WHERE id_practicante = ?
            AND fecha = ?
            ORDER BY id_asistencia DESC
            LIMIT 1
        ");

        $stmtBuscar->execute([
            $p['id_practicante'],
            $fecha
        ]);

        $asistencia = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

        if (!$asistencia) {
            $stmtCrear = $pdo->prepare("
                INSERT INTO asistencias_practicantes (
                    id_practicante,
                    fecha,
                    estado
                ) VALUES (?, ?, 'Pendiente')
            ");

            $stmtCrear->execute([
                $p['id_practicante'],
                $fecha
            ]);

            $idAsistencia = $pdo->lastInsertId();

            $asistencia = [
                'id_asistencia' => $idAsistencia,
                'hora_entrada' => null,
                'hora_salida' => null,
                'estado' => 'Pendiente'
            ];
        }

        $resultado[] = [
            'id_practicante' => $p['id_practicante'],
            'id_carrera' => $p['id_carrera'],
            'nombre' => $p['nombre'],
            'dni' => $p['dni'],
            'telefono' => $p['telefono'],
            'carrera' => $p['carrera'],
            'modalidad_horario' => $p['modalidad_horario'],
            'tipo_horario' => $p['tipo_horario'],
            'horario_original' => $p['horario'],

            'dia_semana' => $infoHorario['dia_semana'],
            'horario_hoy' => $infoHorario['horario_hoy'],
            'hora_inicio' => $infoHorario['hora_inicio'],
            'hora_fin' => $infoHorario['hora_fin'],

            'id_asistencia' => $asistencia['id_asistencia'],
            'hora_entrada' => $asistencia['hora_entrada'],
            'hora_salida' => $asistencia['hora_salida'],
            'estado' => $asistencia['estado'] ?: 'Pendiente',
            'observacion' => $asistencia['observacion'] ?? ''
        ];
    }

    echo json_encode([
        'success' => true,
        'fecha' => $fecha,
        'dia_semana' => obtenerDiaSemanaEspanol($fecha),
        'total' => count($resultado),
        'practicantes' => $resultado
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}