<?php
 
require_once "../config/db.php";
 
header("Content-Type: application/json");
 
try {
 
    $id_matricula = $_GET['id_matricula'] ?? 0;
 
    if (!$id_matricula) {
        throw new Exception("id_matricula requerido");
    }
 
    /* =========================
       DATOS DEL ALUMNO Y MATRÍCULA
       ========================= */
 
    $sql = "
        SELECT
            m.id_matricula,
            m.monto_matricula,
            m.monto_pagado,
            m.fecha_matricula,
 
            a.id_alumno,
            a.nombre AS alumno,
            a.tipo_ciclo,
 
            g.nombre_grupo,
            c.nombre_curso
 
        FROM matriculas m
 
        INNER JOIN alumnos a
            ON m.id_alumno = a.id_alumno
 
        INNER JOIN grupos g
            ON m.id_grupo = g.id_grupo
 
        INNER JOIN cursos c
            ON g.id_curso = c.id_curso
 
        WHERE m.id_matricula = ?
    ";
 
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_matricula]);
    $matricula = $stmt->fetch(PDO::FETCH_ASSOC);
 
    if (!$matricula) {
        throw new Exception("Matrícula no encontrada");
    }
 
    /* =========================
       VERIFICAR SI YA TIENE PLAN
       ========================= */
 
    $sqlPlan = "
        SELECT id_plan, estado
        FROM planes_pago
        WHERE id_matricula = ?
        LIMIT 1
    ";
 
    $stmtPlan = $pdo->prepare($sqlPlan);
    $stmtPlan->execute([$id_matricula]);
    $plan = $stmtPlan->fetch(PDO::FETCH_ASSOC);
 
    /* =========================
       VERIFICAR PROMOCIÓN ACTIVA
       ========================= */
 
    $sqlPromo = "
        SELECT
            p.id_promocion,
            p.nombre_promocion,
            p.descripcion
 
        FROM matriculas_promociones mp
 
        INNER JOIN promociones p
            ON mp.id_promocion = p.id_promocion
 
        WHERE mp.id_matricula = ?
          AND p.activa = TRUE
 
        LIMIT 1
    ";
 
    $stmtPromo = $pdo->prepare($sqlPromo);
    $stmtPromo->execute([$id_matricula]);
    $promo = $stmtPromo->fetch(PDO::FETCH_ASSOC);
 
    /* =========================
       PROMOCIONES DISPONIBLES
       (para mostrar en el selector)
       ========================= */
 
    $sqlPromos = "
        SELECT id_promocion, nombre_promocion, descripcion
        FROM promociones
        WHERE activa = TRUE
        ORDER BY id_promocion ASC
    ";
 
    $stmtPromos = $pdo->prepare($sqlPromos);
    $stmtPromos->execute();
    $promosDisponibles = $stmtPromos->fetchAll(PDO::FETCH_ASSOC);
 
    /* =========================
       RESPUESTA
       ========================= */
 
    echo json_encode([
        "status"             => "success",
        "matricula"          => $matricula,
        "tiene_plan"         => $plan ? true : false,
        "plan"               => $plan ?: null,
        "promo_asignada"     => $promo ?: null,
        "promos_disponibles" => $promosDisponibles
    ]);
 
} catch (Exception $e) {
 
    echo json_encode([
        "status"  => "error",
        "message" => $e->getMessage()
    ]);
 
}