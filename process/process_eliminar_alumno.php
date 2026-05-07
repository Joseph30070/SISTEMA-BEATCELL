<?php

require_once __DIR__ . '/../config/auth.php';

checkRole(['ADMINISTRADOR']);

$pdo = require __DIR__ . '/../config/db.php';

try {

    // Validar ID
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("ID de alumno no válido");
    }

    $id_alumno = (int) $_GET['id'];

    // =========================
    // VERIFICAR SI EXISTE
    // =========================
    $stmt = $pdo->prepare("
        SELECT id_alumno
        FROM alumnos
        WHERE id_alumno = ?
    ");

    $stmt->execute([$id_alumno]);

    if (!$stmt->fetch()) {
        throw new Exception("Alumno no encontrado");
    }

    // =========================
    // INICIAR TRANSACCIÓN
    // =========================
    $pdo->beginTransaction();

    // =========================
    // ELIMINAR PROMOCIONES
    // =========================
    $stmt = $pdo->prepare("
        DELETE mp
        FROM matriculas_promociones mp
        INNER JOIN matriculas m
            ON mp.id_matricula = m.id_matricula
        WHERE m.id_alumno = ?
    ");

    $stmt->execute([$id_alumno]);

    // =========================
    // ELIMINAR MOVIMIENTOS FINANCIEROS
    // =========================
    $stmt = $pdo->prepare("
        DELETE mf
        FROM movimientos_financieros mf
        INNER JOIN matriculas m
            ON mf.id_matricula = m.id_matricula
        WHERE m.id_alumno = ?
    ");

    $stmt->execute([$id_alumno]);

    // =========================
    // ELIMINAR CUOTAS
    // =========================
    $stmt = $pdo->prepare("
        DELETE c
        FROM cuotas c
        INNER JOIN planes_pago pp
            ON c.id_plan = pp.id_plan
        INNER JOIN matriculas m
            ON pp.id_matricula = m.id_matricula
        WHERE m.id_alumno = ?
    ");

    $stmt->execute([$id_alumno]);

    // =========================
    // ELIMINAR PLANES DE PAGO
    // =========================
    $stmt = $pdo->prepare("
        DELETE pp
        FROM planes_pago pp
        INNER JOIN matriculas m
            ON pp.id_matricula = m.id_matricula
        WHERE m.id_alumno = ?
    ");

    $stmt->execute([$id_alumno]);

    // =========================
    // ELIMINAR MATRÍCULAS
    // =========================
    $stmt = $pdo->prepare("
        DELETE FROM matriculas
        WHERE id_alumno = ?
    ");

    $stmt->execute([$id_alumno]);

    // =========================
    // ELIMINAR ALUMNO
    // =========================
    $stmt = $pdo->prepare("
        DELETE FROM alumnos
        WHERE id_alumno = ?
    ");

    $stmt->execute([$id_alumno]);

    // =========================
    // CONFIRMAR
    // =========================
    $pdo->commit();

    header("Location: ../public/registro_alumnos.php?success=Alumno eliminado correctamente");
    exit;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header("Location: ../public/registro_alumnos.php?error=" . urlencode($e->getMessage()));
    exit;
}