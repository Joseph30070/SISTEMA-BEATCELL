<?php
$pdo = require __DIR__ . '/../config/db.php';


header('Content-Type: application/json');

if (!isset($_GET['id_grupo'])) {
    echo json_encode([
        "success" => false,
        "message" => "ID de grupo no proporcionado"
    ]);
    exit;
}

$id = $_GET['id_grupo'];

$stmt = $pdo->prepare("
    SELECT g.*, c.nombre_curso
    FROM grupos g
    INNER JOIN cursos c ON c.id_curso = g.id_curso
    WHERE g.id_grupo = ?
");

$stmt->execute([$id]);
$grupo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($grupo) {
    echo json_encode([
        "success" => true,
        "grupo" => $grupo
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Grupo no encontrado"
    ]);
}

