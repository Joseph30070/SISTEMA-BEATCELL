<?php

require_once "../config/database.php";

try {

    $filtro = $_GET['filtro'] ?? 'pendientes';

    if ($filtro === 'pendientes') {

        $where = "c.estado = 'Pendiente'";

    } elseif ($filtro === 'pagados') {

        $where = "c.estado = 'Pagado'";

    } else {

        $where = "1 = 1";

    }

    $sql = "

        SELECT

            a.id_alumno,
            a.nombre,
            a.dni,
            a.telefono,

            m.id_matricula,

            c.id_cuota,
            c.numero_cuota,
            c.monto_cuota,
            c.monto_pagado,
            c.fecha_vencimiento,
            c.estado

        FROM alumnos a

        INNER JOIN matriculas m
            ON a.id_alumno = m.id_alumno

        INNER JOIN planes_pago p
            ON m.id_matricula = p.id_matricula

        INNER JOIN cuotas c
            ON p.id_plan = c.id_plan

        WHERE
            $where

        ORDER BY
            c.fecha_vencimiento ASC

    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $data
    ]);

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);

}
