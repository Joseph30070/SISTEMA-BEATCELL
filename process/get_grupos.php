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
    // Día actual en español
    // =========================
    $dias_es = [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];

    $hoy = $dias_es[date('l')];

    // =========================
    // NUEVO QUERY (MODELO PROFESIONAL)
    // =========================
    $stmt = $pdo->prepare("
        SELECT 
            g.id_grupo,
            g.nombre_grupo,
            gh.dia_semana,
            TIME_FORMAT(gh.hora_inicio, '%H:%i') AS hora_inicio,
            TIME_FORMAT(gh.hora_fin, '%H:%i') AS hora_fin
        FROM grupos g
        INNER JOIN grupo_horarios gh 
            ON gh.id_grupo = g.id_grupo
        WHERE g.id_curso = ?
        AND gh.dia_semana = ?
        ORDER BY g.nombre_grupo ASC, gh.hora_inicio ASC
    ");

    $stmt->execute([$id_curso, $hoy]);

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // =========================
    // AGRUPAR RESULTADOS
    // =========================
    $grupos = [];

    foreach ($result as $row) {

        $id = $row['id_grupo'];

        if (!isset($grupos[$id])) {
            $grupos[$id] = [
                'id_grupo' => $id,
                'nombre_grupo' => $row['nombre_grupo'],
                'horarios' => []
            ];
        }

        $grupos[$id]['horarios'][] = [
            'dia' => $row['dia_semana'],
            'hora_inicio' => $row['hora_inicio'],
            'hora_fin' => $row['hora_fin']
        ];
    }

    // Reindexar array
    $grupos = array_values($grupos);

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