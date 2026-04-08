<?php

require_once __DIR__ . '/../config/auth.php';

checkRole([
    'ADMINISTRADOR',
    'ASESOR'
]);

header('Content-Type: application/json');

$pdo = require __DIR__ . '/../config/db.php';

try {

    // =========================
    // PARAMETROS
    // =========================

    $fecha =
        $_GET['fecha']
        ?? date('Y-m-d');

    $id_curso =
        $_GET['id_curso']
        ?? null;

    $id_grupo =
        $_GET['id_grupo']
        ?? null;

    // =========================
    // CONSULTA BASE
    // =========================

    $sql = "

    SELECT 
        a.nombre,
        c.nombre_curso,
        g.nombre_grupo,
        asl.fecha,
        asl.hora_entrada,
        asl.hora_salida,
        asl.estado

    FROM asistencias asl

    INNER JOIN alumnos a
        ON a.id_alumno = asl.id_alumno

    INNER JOIN grupos g
        ON g.id_grupo = asl.id_grupo

    INNER JOIN cursos c
        ON c.id_curso = g.id_curso

    WHERE asl.fecha = ?

    ";

    $params = [$fecha];

    // =========================
    // FILTRO POR CURSO
    // =========================

    if ($id_curso) {

        $sql .= "
            AND c.id_curso = ?
        ";

        $params[] =
            $id_curso;

    }

    // =========================
    // FILTRO POR GRUPO
    // =========================

    if ($id_grupo) {

        $sql .= "
            AND g.id_grupo = ?
        ";

        $params[] =
            $id_grupo;

    }

    // =========================
    // ORDENAMIENTO
    // =========================

    $sql .= "
        ORDER BY
            g.nombre_grupo ASC,
            a.nombre ASC
    ";

    // =========================
    // EJECUCION
    // =========================

    $stmt =
        $pdo->prepare($sql);

    $stmt->execute($params);

    $data =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

    // =========================
    // RESPUESTA
    // =========================

    echo json_encode([

        "success" => true,

        "data" => $data,

        "total" => count($data)

    ]);

} catch (Exception $e) {

    echo json_encode([

        "success" => false,

        "error" => $e->getMessage()

    ]);

}
