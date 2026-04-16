<?php

require_once "../config/database.php";

try {

    $estado = $_GET['estado'] ?? 'Pendiente';

    $sql = "
        SELECT

            c.id_cuota,
            c.numero_cuota,
            c.monto_cuota,
            c.monto_pagado,
            c.fecha_vencimiento,
            c.estado,

            a.id_alumno,
            a.nombre,
            a.dni,
            a.telefono,

            m.id_matricula,

            p.id_plan,
            p.cantidad_cuotas,
            p.monto_final

        FROM cuotas c

        INNER JOIN planes_pago p
            ON c.id_plan = p.id_plan

        INNER JOIN matriculas m
            ON p.id_matricula = m.id_matricula

        INNER JOIN alumnos a
            ON m.id_alumno = a.id_alumno

        WHERE c.estado = ?

        ORDER BY
            c.fecha_vencimiento ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$estado]);

    $cuotas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $cuotas
    ]);

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);

}

