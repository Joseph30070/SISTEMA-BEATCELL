<?php

require_once "../config/db.php";

header("Content-Type: application/json");

try {

    $pdo->beginTransaction();

    /* =========================
       RECIBIR DATOS DEL FORMULARIO
       ========================= */

    $id_matricula        = $_POST['id_matricula']        ?? 0;
    $monto_base          = $_POST['monto_base']          ?? 0;
    $tipo_descuento      = $_POST['tipo_descuento']      ?? 'Ninguno';
    $porcentaje_descuento= $_POST['porcentaje_descuento']?? 0;
    $monto_final         = $_POST['monto_final']         ?? 0;
    $cantidad_cuotas     = $_POST['cantidad_cuotas']     ?? 1;
    $es_becado           = $_POST['es_becado']           ?? 0;
    $fecha_inicio        = $_POST['fecha_inicio']        ?? date("Y-m-d");
    $id_promocion        = $_POST['id_promocion']        ?? null;

    // cuotas viene como JSON: [{monto_cuota, fecha_vencimiento}, ...]
    $cuotas_json         = $_POST['cuotas']              ?? '[]';
    $cuotas              = json_decode($cuotas_json, true);

    /* =========================
       VALIDACIONES BÁSICAS
       ========================= */

    if (!$id_matricula) {
        throw new Exception("id_matricula requerido");
    }

    if (!$id_matricula || $monto_final <= 0) {
        throw new Exception("Monto final inválido");
    }

    if (empty($cuotas) || !is_array($cuotas)) {
        throw new Exception("Debes definir al menos una cuota");
    }

    if (count($cuotas) != $cantidad_cuotas) {
        throw new Exception("La cantidad de cuotas no coincide");
    }

    /* =========================
       VERIFICAR QUE NO TENGA PLAN YA
       ========================= */

    $sqlCheck = "
        SELECT id_plan FROM planes_pago
        WHERE id_matricula = ?
        LIMIT 1
    ";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$id_matricula]);

    if ($stmtCheck->fetch()) {
        throw new Exception("Esta matrícula ya tiene un plan de pago");
    }

    /* =========================
       VERIFICAR QUE LA MATRÍCULA
       ESTÉ PAGADA
       ========================= */

    $sqlMat = "
        SELECT monto_matricula, monto_pagado, id_alumno
        FROM matriculas
        WHERE id_matricula = ?
    ";
    $stmtMat = $pdo->prepare($sqlMat);
    $stmtMat->execute([$id_matricula]);
    $matricula = $stmtMat->fetch(PDO::FETCH_ASSOC);

    if (!$matricula) {
        throw new Exception("Matrícula no encontrada");
    }

    if ($matricula['monto_pagado'] < $matricula['monto_matricula']) {
        throw new Exception("La matrícula aún no ha sido pagada");
    }

    /* =========================
       INSERTAR PLAN DE PAGO
       ========================= */

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
        $es_becado ? 1 : 0,
        $fecha_inicio
    ]);

    $id_plan = $pdo->lastInsertId();

    /* =========================
       INSERTAR CUOTAS
       ========================= */

    $sqlCuota = "
        INSERT INTO cuotas (
            id_plan,
            numero_cuota,
            monto_cuota,
            monto_pagado,
            fecha_vencimiento,
            estado
        ) VALUES (
            ?, ?, ?, 0.00, ?, 'Pendiente'
        )
    ";

    $stmtCuota = $pdo->prepare($sqlCuota);

    foreach ($cuotas as $i => $cuota) {

        $numero       = $i + 1;
        $monto_cuota  = $cuota['monto_cuota']       ?? 0;
        $fecha_venc   = $cuota['fecha_vencimiento'] ?? null;

        if ($monto_cuota <= 0) {
            throw new Exception("Monto inválido en cuota " . $numero);
        }

        if (!$fecha_venc) {
            throw new Exception("Fecha de vencimiento requerida en cuota " . $numero);
        }

        $stmtCuota->execute([
            $id_plan,
            $numero,
            $monto_cuota,
            $fecha_venc
        ]);
    }

    /* =========================
       GUARDAR PROMOCIÓN SI APLICA
       ========================= */

    if ($id_promocion) {

        // Verificar que la promo existe y está activa
        $sqlPromo = "
            SELECT id_promocion FROM promociones
            WHERE id_promocion = ? AND activa = TRUE
        ";
        $stmtPromo = $pdo->prepare($sqlPromo);
        $stmtPromo->execute([$id_promocion]);

        if (!$stmtPromo->fetch()) {
            throw new Exception("Promoción no válida o inactiva");
        }

        // Verificar que no tenga ya esa promo asignada
        $sqlCheckPromo = "
            SELECT id FROM matriculas_promociones
            WHERE id_matricula = ? AND id_promocion = ?
        ";
        $stmtCheckPromo = $pdo->prepare($sqlCheckPromo);
        $stmtCheckPromo->execute([$id_matricula, $id_promocion]);

        if (!$stmtCheckPromo->fetch()) {

            $sqlInsertPromo = "
                INSERT INTO matriculas_promociones (
                    id_matricula,
                    id_promocion,
                    fecha_asignacion
                ) VALUES (?, ?, CURDATE())
            ";

            $stmtInsertPromo = $pdo->prepare($sqlInsertPromo);
            $stmtInsertPromo->execute([$id_matricula, $id_promocion]);
        }
    }

    $pdo->commit();

    echo json_encode([
        "status"  => "success",
        "id_plan" => $id_plan,
        "message" => "Plan de pago creado correctamente"
    ]);

} catch (Exception $e) {

    $pdo->rollBack();

    echo json_encode([
        "status"  => "error",
        "message" => $e->getMessage()
    ]);
}