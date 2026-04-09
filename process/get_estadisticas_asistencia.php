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
    // CONSULTA ESTADISTICAS
    // =========================

    $sql = "

    SELECT 
        CASE DAYNAME(fecha)

            WHEN 'Monday' THEN 'Lunes'
            WHEN 'Tuesday' THEN 'Martes'
            WHEN 'Wednesday' THEN 'Miércoles'
            WHEN 'Thursday' THEN 'Jueves'
            WHEN 'Friday' THEN 'Viernes'
            WHEN 'Saturday' THEN 'Sábado'
            WHEN 'Sunday' THEN 'Domingo'

        END as dia,

        COUNT(*) as total

    FROM asistencias

    WHERE 
        fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        AND estado = 'Asistió'

    GROUP BY fecha

    ORDER BY fecha

    ";

    // =========================
    // EJECUCION
    // =========================

    $stmt =
        $pdo->prepare($sql);

    $stmt->execute();

    $data =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

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
