<?php

require_once "../config/database.php";

try {

    $pdo->beginTransaction();

    $id_cuota = $_POST['id_cuota'];
    $monto = $_POST['monto'];
    $metodo_pago = $_POST['metodo_pago'];
    $numero_boleta = $_POST['numero_boleta'];

    /*
    Obtener datos de la cuota
    */

    $sql = "
        SELECT

            c.id_cuota,
            c.monto_cuota,
            c.monto_pagado,

            p.id_plan,
            m.id_matricula,
            a.id_alumno

        FROM cuotas c

        INNER JOIN planes_pago p
            ON c.id_plan = p.id_plan

        INNER JOIN matriculas m
            ON p.id_matricula = m.id_matricula

        INNER JOIN alumnos a
            ON m.id_alumno = a.id_alumno

        WHERE c.id_cuota = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_cuota]);

    $cuota = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cuota) {

        throw new Exception("Cuota no encontrada");

    }

    /*
    Calcular nuevo monto pagado
    */

    $nuevo_pagado =
        $cuota['monto_pagado'] + $monto;

    $estado =
        ($nuevo_pagado >= $cuota['monto_cuota'])
        ? 'Pagado'
        : 'Pendiente';

    /*
    Actualizar cuota
    */

    $sql = "
        UPDATE cuotas
        SET

            monto_pagado = ?,
            fecha_pago = CURRENT_DATE,
            metodo_pago = ?,
            estado = ?

        WHERE id_cuota = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nuevo_pagado,
        $metodo_pago,
        $estado,
        $id_cuota
    ]);

    /*
    Registrar movimiento financiero
    */

    $sql = "
        INSERT INTO movimientos_financieros (

            id_alumno,
            id_matricula,
            id_cuota,
            tipo_movimiento,
            monto,
            numero_boleta,
            observacion

        )

        VALUES (

            ?, ?, ?,
            'Pago de cuota',
            ?,
            ?,
            'Pago registrado en sistema'
        )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([

        $cuota['id_alumno'],
        $cuota['id_matricula'],
        $id_cuota,
        $monto,
        $numero_boleta

    ]);

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Pago registrado correctamente"
    ]);

} catch (Exception $e) {

    $pdo->rollBack();

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);

}
