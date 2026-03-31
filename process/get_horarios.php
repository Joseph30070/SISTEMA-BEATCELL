<?php

require_once __DIR__ . '/../config/db.php';

$pdo = require __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// 🔥 obtener día actual en español
$dias_es = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sábado',
    'Sunday' => 'Domingo'
];

$hoy_en = date('l');
$hoy = $dias_es[$hoy_en];

try {

    $stmt = $pdo->query("
        SELECT 
            c.nombre_curso AS curso,
            g.nombre_grupo AS grupo,
            g.dias,
            TIME_FORMAT(g.hora_inicio, '%H:%i') AS hora_inicio,
            TIME_FORMAT(g.hora_fin, '%H:%i') AS hora_fin
        FROM grupos g
        INNER JOIN cursos c ON c.id_curso = g.id_curso
    ");

    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔥 FILTRAR SOLO LOS DEL DÍA
    $horarios = [];

    foreach ($todos as $h) {

        if (stripos($h['dias'], $hoy) !== false) {
            $horarios[] = $h;
        }

    }

    echo json_encode($horarios);

} catch (Exception $e) {

    echo json_encode([]);
}