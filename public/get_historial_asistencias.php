<?php

require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR','ASESOR']);

header('Content-Type: application/json');

$pdo = require __DIR__ . '/../config/db.php';

try {

    $fecha = $_GET['fecha'] ?? date('Y-m-d');
    $id_curso = $_GET['id_curso'] ?? null;
    $id_grupo = $_GET['id_grupo'] ?? null;

    $sql = "
    SELECT 
        a.nombre,
        c.nombre_curso,
        g.nombre_grupo,
        asl.fecha,
        asl.hora_entrada,
        asl.hora_salida
    FROM asistencias asl
    INNER JOIN alumnos a ON a.id_alumno = asl.id_alumno
    INNER JOIN matriculas m ON m.id_alumno = a.id_alumno
    INNER JOIN grupos g ON g.id_grupo = m.id_grupo
    INNER JOIN cursos c ON c.id_curso = g.id_curso
    WHERE asl.fecha = ?
    ";

    $params = [$fecha];

    if($id_curso){
        $sql .= " AND c.id_curso = ?";
        $params[] = $id_curso;
    }

    if($id_grupo){
        $sql .= " AND g.id_grupo = ?";
        $params[] = $id_grupo;
    }

    $sql .= " ORDER BY asl.hora_entrada ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);

}