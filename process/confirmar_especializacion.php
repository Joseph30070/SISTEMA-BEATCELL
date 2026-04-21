<?php

require_once "../config/db.php";

header("Content-Type: application/json");

try {

    $pdo->beginTransaction();

    $id_matricula = $_POST['id_matricula'] ?? 0;

    if (!$id_matricula) {
        throw new Exception("id_matricula requerido");
    }

    // Verificar que sea especialización
    $sqlCheckCiclo = "
        SELECT a.tipo_ciclo, m.monto_matricula, m.monto_pagado
        FROM matriculas m
        INNER JOIN alumnos a ON m.id_alumno = a.id_alumno
        WHERE m.id_matricula = ?
    ";
    $stmtCheckCiclo = $pdo->prepare($sqlCheckCiclo);
    $stmtCheckCiclo->execute([$id_matricula]);
    $matricula = $stmtCheckCiclo->fetch(PDO::FETCH_ASSOC);

    if (!$matricula) {
        throw new Exception("Matrícula no encontrada");
    }

    if ($matricula['tipo_ciclo'] !== 'Especialización') {
        throw new Exception("Esta función es solo para especialización");
    }

    // Verificar que no tenga plan ya
    $sqlCheckPlan = "
        SELECT id_plan FROM planes_pago
        WHERE id_matricula = ?
        LIMIT 1
    ";
    $stmtCheckPlan = $pdo->prepare($sqlCheckPlan);
    $stmtCheckPlan->execute([$id_matricula]);

    if ($stmtCheckPlan->fetch()) {
        throw new Exception("Esta matrícula ya tiene un plan de pago");
    }

    // Valores fijos para especialización
    $monto_base = 300;
    $tipo_descuento = 'Ninguno';
    $porcentaje_descuento = 0;
    $monto_final = 300;
    $cantidad_cuotas = 1;
    $es_becado = 0;
    $fecha_inicio = date("Y-m-d");

    // Insertar plan de pago
    $sqlPlan = "
        INSERT INTO planes_pago (
            id_matricula,
            monto_base,
            tipo_descuento,
            porcentaje_descuento,
            monto_final,
            cantidad_cuotas,
            es_becado,
            fecha_inicio,
            estado
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, 'Activo'
        )
    ";
    $stmtPlan = $pdo->prepare($sqlPlan);
    $stmtPlan->execute([
        $id_matricula,
        $monto_base,
        $tipo_descuento,
        $porcentaje_descuento,
        $monto_final,
        $cantidad_cuotas,
        $es_becado,
        $fecha_inicio
    ]);

    $id_plan = $pdo->lastInsertId();

    // Insertar cuota única
    $fecha_vencimiento = date("Y-m-d", strtotime("+7 days"));
    $sqlCuota = "
        INSERT INTO cuotas (
            id_plan,
            numero_cuota,
            monto_cuota,
            monto_pagado,
            fecha_vencimiento,
            estado
        ) VALUES (
            ?, 1, 300.00, 0.00, ?, 'Pendiente'
        )
    ";
    $stmtCuota = $pdo->prepare($sqlCuota);
    $stmtCuota->execute([$id_plan, $fecha_vencimiento]);

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Plan de pago creado exitosamente",
        "id_plan" => $id_plan
    ]);

} catch (Exception $e) {

    $pdo->rollBack();

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);

}