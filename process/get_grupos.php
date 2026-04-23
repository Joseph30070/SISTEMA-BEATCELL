<?php

require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

header('Content-Type: application/json');

$pdo = require __DIR__ . '/../config/db.php';

try {

    $id_curso = $_GET['id_curso'] ?? null;

    if (!$id_curso) {
        throw new Exception("ID de curso requerido");
    }

    // =========================
    // Traer todos los grupos del curso
    // =========================
    $stmt = $pdo->prepare("
        SELECT 
            g.id_grupo,
            g.nombre_grupo,
            g.dias,
            TIME_FORMAT(g.hora_inicio, '%H:%i') AS hora_inicio,
            TIME_FORMAT(g.hora_fin, '%H:%i') AS hora_fin
        FROM grupos g
        WHERE g.id_curso = ?
        ORDER BY g.nombre_grupo ASC
    ");

    $stmt->execute([$id_curso]);

    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtEspecial = $pdo->prepare("
        SELECT
            dia_semana AS dia,
            TIME_FORMAT(hora_inicio, '%H:%i') AS hora_inicio,
            TIME_FORMAT(hora_fin, '%H:%i') AS hora_fin
        FROM horarios_especiales
        WHERE id_grupo = ?
        ORDER BY dia_semana
    ");

    $stmtGrupoHorarios = $pdo->prepare("
        SELECT
            dia_semana AS dia,
            TIME_FORMAT(hora_inicio, '%H:%i') AS hora_inicio,
            TIME_FORMAT(hora_fin, '%H:%i') AS hora_fin
        FROM grupo_horarios
        WHERE id_grupo = ?
        ORDER BY dia_semana
    ");

    foreach ($grupos as &$grupo) {

        $horarios = [];

        $stmtEspecial->execute([$grupo['id_grupo']]);
        $horarios = $stmtEspecial->fetchAll(PDO::FETCH_ASSOC);

        if (!$horarios) {
            $stmtGrupoHorarios->execute([$grupo['id_grupo']]);
            $horarios = $stmtGrupoHorarios->fetchAll(PDO::FETCH_ASSOC);
        }

        if (!$horarios && $grupo['dias']) {
            $dias = array_map('trim', explode(',', $grupo['dias']));

            foreach ($dias as $dia) {
                if ($dia === '') {
                    continue;
                }

                $horarios[] = [
                    'dia' => $dia,
                    'hora_inicio' => $grupo['hora_inicio'],
                    'hora_fin' => $grupo['hora_fin']
                ];
            }
        }

        $grupo['horarios'] = $horarios;
    }
    unset($grupo);

    echo json_encode([
        'success' => true,
        'grupos' => $grupos,
        'count' => count($grupos)
    ]);

} catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        'error' => 'Error en BD: ' . $e->getMessage()
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}