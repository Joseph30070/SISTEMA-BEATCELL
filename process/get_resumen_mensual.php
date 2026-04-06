<?php

header('Content-Type: application/json');

$pdo = require __DIR__ . '/../config/db.php';

try {

    // =========================
    // CONSULTA DEL MES ACTUAL
    // =========================

    $sql = "
        SELECT 
            DAY(fecha) as dia,

            COUNT(CASE 
                WHEN hora_entrada IS NOT NULL 
                THEN 1 
            END) as asistencias,

            COUNT(CASE 
                WHEN hora_entrada IS NULL 
                THEN 1 
            END) as ausentes

        FROM asistencias

        WHERE 
            MONTH(fecha) = MONTH(CURDATE())
            AND YEAR(fecha) = YEAR(CURDATE())

        GROUP BY fecha
        ORDER BY fecha
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $diasMes = date('t');

    $data = [];

    $totalAsistencias = 0;
    $totalAusentes = 0;

    for ($i = 1; $i <= $diasMes; $i++) {

        $asistencias = 0;
        $ausentes = 0;

        foreach ($resultados as $r) {

            if ($r['dia'] == $i) {

                $asistencias = (int)$r['asistencias'];
                $ausentes = (int)$r['ausentes'];

                $totalAsistencias += $asistencias;
                $totalAusentes += $ausentes;

            }

        }

        $data[] = [
            "dia" => $i,
            "asistencias" => $asistencias,
            "ausentes" => $ausentes
        ];

    }

    echo json_encode([
        "success" => true,
        "data" => $data,
        "total_asistencias" => $totalAsistencias,
        "total_ausentes" => $totalAusentes
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);

}

