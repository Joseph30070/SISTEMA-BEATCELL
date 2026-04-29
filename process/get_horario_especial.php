<?php
$pdo = require __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (!isset($_GET['id_grupo'])) {
    echo json_encode([
        "success" => false,
        "message" => "Falta id_grupo",
        "data" => []
    ]);
    exit;
}

$id = $_GET['id_grupo'];

$stmt = $pdo->prepare("
    SELECT he.*, g.nombre_grupo, c.id_curso
    FROM horarios_especiales he
    INNER JOIN grupos g ON g.id_grupo = he.id_grupo
    INNER JOIN cursos c ON c.id_curso = g.id_curso
    WHERE he.id_grupo = ?
");

$stmt->execute([$id]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "data" => $data ?? []
]);


