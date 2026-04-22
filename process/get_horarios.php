<?php

require_once __DIR__ . '/../config/db.php';
$pdo = require __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Día actual en español
$dias_es = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sábado',
    'Sunday' => 'Domingo'
];

$fecha = $_GET['fecha'] ?? date('Y-m-d');

$timestamp = strtotime($fecha);
$dia_en = date('l', $timestamp);

$hoy = $dias_es[$dia_en];
// ======================
// NORMALES
// ======================
$stmt1 = $pdo->prepare("
    SELECT 
        c.nombre_curso AS curso,
        g.nombre_grupo AS grupo,
        g.dias,
        TIME_FORMAT(g.hora_inicio,'%H:%i') AS hora_inicio,
        TIME_FORMAT(g.hora_fin,'%H:%i') AS hora_fin,
        'NORMAL' AS tipo
    FROM grupos g
    JOIN cursos c ON c.id_curso = g.id_curso
    WHERE g.dias LIKE ?
");

$stmt1->execute(["%$hoy%"]);
$normales = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// ======================
// ESPECIALES
// ======================
$stmt2 = $pdo->prepare("
    SELECT 
        c.nombre_curso AS curso,
        g.nombre_grupo AS grupo,
        he.dia_semana AS dias,
        TIME_FORMAT(he.hora_inicio,'%H:%i') AS hora_inicio,
        TIME_FORMAT(he.hora_fin,'%H:%i') AS hora_fin,
        'ESPECIAL' AS tipo
    FROM horarios_especiales he
    JOIN grupos g ON g.id_grupo = he.id_grupo
    JOIN cursos c ON c.id_curso = g.id_curso
    WHERE he.dia_semana = ?
");

$stmt2->execute([$hoy]);
$especiales = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// ======================
// UNIR
// ======================
$horarios = array_merge($normales, $especiales);

echo json_encode($horarios);