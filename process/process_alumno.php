<?php

require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

$pdo = require __DIR__ . '/../config/db.php';

try {

    $pdo->beginTransaction();

    // =========================
    // 1. DATOS DEL FORM
    // =========================
    $nombre = trim($_POST['nombre'] ?? '');
    $dni = trim($_POST['dni'] ?? '');

    $telefono = trim($_POST['telefono'] ?? null) ?: null;
    $telefonopadres = trim($_POST['telefonopadres'] ?? null) ?: null;
    $telefonoapoderado = trim($_POST['telefonoapoderado'] ?? null) ?: null;

    $contacto_pago = $_POST['contacto_pago'] ?? 'Alumno';

    // NUEVOS CAMPOS
    $edad = trim($_POST['edad'] ?? null) ?: null;
    $email = trim($_POST['email'] ?? null) ?: null;
    $direccion = trim($_POST['direccion'] ?? null) ?: null;

    $nombre_apoderado = trim($_POST['nombre_apoderado'] ?? null) ?: null;
    $dni_apoderado = trim($_POST['dni_apoderado'] ?? null) ?: null;
    $correo_apoderado = trim($_POST['correo_apoderado'] ?? null) ?: null;

    $notificar_emergencia = $_POST['notificar_emergencia'] ?? null;
    $tipo_ciclo = $_POST['tipo_ciclo'] ?? null;
    $medio_captacion = $_POST['medio_captacion'] ?? null;

    $id_grupo = $_POST['id_grupo'] ?? null;

    // =========================
    // VALIDACIÓN BÁSICA
    // =========================
    if (!$nombre || !$dni || !$id_grupo) {
        throw new Exception("Faltan datos obligatorios (Nombre, DNI y Grupo)");
    }

    // Validar que el grupo exista
    $stmt = $pdo->prepare("SELECT id_grupo FROM grupos WHERE id_grupo = ?");
    $stmt->execute([$id_grupo]);

    if (!$stmt->fetch()) {
        throw new Exception("El grupo seleccionado no existe");
    }

    // =========================
    // VERIFICAR DNI DUPLICADO
    // =========================
    $stmt = $pdo->prepare("SELECT id_alumno FROM alumnos WHERE dni = ? AND fecha_baja IS NULL");
    $stmt->execute([$dni]);

    if ($stmt->fetch()) {
        throw new Exception("Ya existe un alumno registrado con este DNI");
    }

    // =========================
    // INSERT ALUMNO
    // =========================
    $stmt = $pdo->prepare(" 
        INSERT INTO alumnos (
            nombre,
            dni,
            telefono,
            telefonopadres,
            telefonoapoderado,
            contacto_pago,
            edad,
            email,
            direccion,
            nombre_apoderado,
            dni_apoderado,
            correo_apoderado,
            notificar_emergencia,
            tipo_ciclo,
            medio_captacion,
            fecha_registro
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE
        )
    ");

    $stmt->execute([
        $nombre,
        $dni,
        $telefono,
        $telefonopadres,
        $telefonoapoderado,
        $contacto_pago,
        $edad,
        $email,
        $direccion,
        $nombre_apoderado,
        $dni_apoderado,
        $correo_apoderado,
        $notificar_emergencia,
        $tipo_ciclo,
        $medio_captacion
    ]);

    $id_alumno = $pdo->lastInsertId();

    // =========================
    // INSERT PRIMERA MATRÍCULA
    // =========================
    $stmt = $pdo->prepare(" 
        INSERT INTO matriculas (
            id_alumno,
            id_grupo,
            tipo,
            monto_matricula,
            estado,
            fecha_matricula
        ) VALUES (
            ?, ?, ?, ?, ?, CURRENT_DATE
        )
    ");

    $stmt->execute([
        $id_alumno,
        $id_grupo,
        'MATRICULA',
        0,
        'Activo'
    ]);

    $pdo->commit();

    header("Location: ../public/registro_alumnos.php?success=Alumno registrado correctamente");
    exit;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header("Location: ../public/registro_alumnos.php?error=" . urlencode($e->getMessage()));
    exit;
}

