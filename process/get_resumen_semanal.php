<?php

header('Content-Type: application/json');

$pdo = require __DIR__ . '/../config/db.php';

try {

    // =========================
    // OBTENER TIPO DE SEMANA
    // =========================

    $tipoSemana = $_GET['semana'] ?? 'actual';

    if ($tipoSemana == 'anterior') {

        // Lunes de la semana anterior
        $inicio = "DATE_SUB(
                        DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY),
                        INTERVAL 7 DAY
                    )";

        // Domingo de la semana anterior
        $fin = "DATE_SUB(
                    DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY),
                    INTERVAL 1 DAY
                )";

    } else {

        // Lunes de la semana actual
        $inicio = "DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";

        // Domingo de la semana actual
        $fin = "DATE_ADD($inicio, INTERVAL 6 DAY)";
    }

    // =========================
    // CONSULTA SQL
    // =========================

    $sql = "
        SELECT 
            DAYOFWEEK(fecha) as dia_num,

            COUNT(CASE 
                WHEN hora_entrada IS NOT NULL 
                THEN 1 
            END) as presentes,

            COUNT(CASE 
                WHEN hora_entrada IS NULL 
                THEN 1 
            END) as ausentes

        FROM asistencias
        WHERE fecha BETWEEN $inicio AND $fin
        GROUP BY fecha
        ORDER BY fecha
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // =========================
    // DÍAS DE LA SEMANA
    // =========================

    $dias = [
        2 => 'Lunes',
        3 => 'Martes',
        4 => 'Miércoles',
        5 => 'Jueves',
        6 => 'Viernes',
        7 => 'Sábado',
        1 => 'Domingo'
    ];

    $data = [];

    // =========================
    // ARMAR RESULTADO COMPLETO
    // =========================

    foreach ($dias as $num => $nombre) {

        $pres = 0;
        $aus = 0;

        foreach ($resultados as $r) {

            if ($r['dia_num'] == $num) {

                $pres = (int)$r['presentes'];
                $aus = (int)$r['ausentes'];

            }

        }

        $data[] = [
            "dia" => $nombre,
            "presentes" => $pres,
            "ausentes" => $aus
        ];

    }

    // =========================
    // RESPUESTA
    // =========================

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



