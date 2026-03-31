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

    $id_grupo = $_POST['id_grupo'] ?? null;

    // Validación
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
    // 2. VERIFICAR DNI DUPLICADO
    // =========================
    $stmt = $pdo->prepare("SELECT id_alumno FROM alumnos WHERE dni = ? AND fecha_baja IS NULL");
    $stmt->execute([$dni]);
    if ($stmt->fetch()) {
        throw new Exception("Ya existe un alumno registrado con este DNI");
    }

    // =========================
    // 3. INSERT ALUMNO
    // =========================
    $stmt = $pdo->prepare("
        INSERT INTO alumnos 
        (nombre, dni, telefono, telefonopadres, telefonoapoderado, contacto_pago, fecha_registro)
        VALUES (?, ?, ?, ?, ?, ?, CURRENT_DATE)
    ");

    $stmt->execute([
        $nombre,
        $dni,
        $telefono,
        $telefonopadres,
        $telefonoapoderado,
        $contacto_pago
    ]);

    $id_alumno = $pdo->lastInsertId();

    // =========================
    // 4. INSERT MATRICULA
    // =========================
    $stmt = $pdo->prepare("
        INSERT INTO matriculas 
        (id_alumno, id_grupo, tipo, monto_matricula, estado, fecha_matricula)
        VALUES (?, ?, ?, ?, ?, CURRENT_DATE)
    ");

    $stmt->execute([
        $id_alumno,
        $id_grupo,
        'MATRICULA',
        0,
        'Activo'
    ]);

    $id_matricula = $pdo->lastInsertId();

    $pdo->commit();

    header("Location: ../public/registro_alumnos.php?success=Alumno registrado correctamente");
    exit;

} catch (Exception $e) {

    $pdo->rollBack();

    header("Location: ../public/registro_alumnos.php?error=" . urlencode($e->getMessage()));
    exit;
}
