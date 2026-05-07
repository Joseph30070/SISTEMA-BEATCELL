<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Solo admin puede registrar practicantes
    checkRole(['ADMINISTRADOR']);

    $action = $_POST['action'] ?? '';

    if ($action === 'registrar') {
        registrarPracticante();
    } elseif ($action === 'actualizar') {
        actualizarPracticante();
    } elseif ($action === 'dar_baja') {
        darBajaPracticante();
    } elseif ($action === 'reactivar') {
        reactivarPracticante();
    } elseif ($action === 'eliminar') {
        eliminarPracticante();
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    exit;
}

// ==================== REGISTRAR ====================
function registrarPracticante() {
    global $pdo;

    // Validar campos requeridos
    $nombre = trim($_POST['nombre'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $telefono_emergencia = trim($_POST['telefono_emergencia'] ?? '');
    $edad = trim($_POST['edad'] ?? null) ?: null;
    $email = trim($_POST['email'] ?? null) ?: null;
    $direccion = trim($_POST['direccion'] ?? null) ?: null;
    $id_carrera = $_POST['id_carrera'] ?? null;
    $modalidad_horario = $_POST['modalidad_horario'] ?? null;
    $horario = trim($_POST['horario'] ?? '');
    $nombre_apoderado = trim($_POST['nombre_apoderado'] ?? null) ?: null;
    $dni_apoderado = trim($_POST['dni_apoderado'] ?? null) ?: null;
    $correo_apoderado = trim($_POST['correo_apoderado'] ?? null) ?: null;
    $telefono_apoderado = trim($_POST['telefono_apoderado'] ?? null) ?: null;
    $notificar_emergencia = $_POST['notificar_emergencia'] ?? null;
    $observacion = trim($_POST['observacion'] ?? null);
    $fecha_registro = trim($_POST['fecha_registro'] ?? '') ?: date('Y-m-d');

    // Validaciones
    if (empty($nombre)) {
        echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
        exit;
    }

    if (!empty($dni) && strlen($dni) < 8) {
        echo json_encode(['success' => false, 'message' => 'DNI inválido (mínimo 8 dígitos)']);
        exit;
    }

    if (!empty($telefono) && !preg_match('/^[0-9]{6,}$/', $telefono)) {
        echo json_encode(['success' => false, 'message' => 'Teléfono inválido (mínimo 6 dígitos)']);
        exit;
    }

    if (!empty($telefono_emergencia) && !preg_match('/^[0-9]{6,}$/', $telefono_emergencia)) {
        echo json_encode(['success' => false, 'message' => 'Teléfono de emergencia inválido (mínimo 6 dígitos)']);
        exit;
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        exit;
    }

    if (!empty($correo_apoderado) && !filter_var($correo_apoderado, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email del apoderado inválido']);
        exit;
    }

    if (!empty($telefono_apoderado) && !preg_match('/^[0-9]{6,}$/', $telefono_apoderado)) {
        echo json_encode(['success' => false, 'message' => 'Teléfono del apoderado inválido (mínimo 6 dígitos)']);
        exit;
    }

    if (!empty($dni_apoderado) && strlen($dni_apoderado) < 8) {
        echo json_encode(['success' => false, 'message' => 'DNI del apoderado inválido (mínimo 8 dígitos)']);
        exit;
    }

    if (empty($modalidad_horario)) {
        echo json_encode(['success' => false, 'message' => 'La modalidad es requerida']);
        exit;
    }

    // Verificar DNI único
    if (!empty($dni)) {
        $stmt = $pdo->prepare("SELECT id_practicante FROM practicantes WHERE dni = ? AND fecha_baja IS NULL");
        $stmt->execute([$dni]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El DNI ya está registrado']);
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO practicantes 
            (nombre, dni, telefono, telefono_emergencia, edad, email, direccion, id_carrera, modalidad_horario, horario, nombre_apoderado, dni_apoderado, correo_apoderado, telefono_apoderado, notificar_emergencia, observacion, fecha_registro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $nombre,
            !empty($dni) ? $dni : null,
            !empty($telefono) ? $telefono : null,
            !empty($telefono_emergencia) ? $telefono_emergencia : null,
            !empty($edad) ? $edad : null,
            !empty($email) ? $email : null,
            !empty($direccion) ? $direccion : null,
            !empty($id_carrera) ? $id_carrera : null,
            !empty($modalidad_horario) ? $modalidad_horario : null,
            !empty($horario) ? $horario : null,
            !empty($nombre_apoderado) ? $nombre_apoderado : null,
            !empty($dni_apoderado) ? $dni_apoderado : null,
            !empty($correo_apoderado) ? $correo_apoderado : null,
            !empty($telefono_apoderado) ? $telefono_apoderado : null,
            !empty($notificar_emergencia) ? $notificar_emergencia : null,
            !empty($observacion) ? $observacion : null,
            $fecha_registro
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Practicante registrado exitosamente',
            'id' => $pdo->lastInsertId()
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al registrar: ' . $e->getMessage()]);
    }
}

// ==================== ACTUALIZAR ====================
function actualizarPracticante() {
    global $pdo;

    $id_practicante = $_POST['id_practicante'] ?? null;
    $nombre = trim($_POST['nombre'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $telefono_emergencia = trim($_POST['telefono_emergencia'] ?? '');
    $edad = trim($_POST['edad'] ?? null) ?: null;
    $email = trim($_POST['email'] ?? null) ?: null;
    $direccion = trim($_POST['direccion'] ?? null) ?: null;
    $id_carrera = $_POST['id_carrera'] ?? null;
    $modalidad_horario = $_POST['modalidad_horario'] ?? null;
    $horario = trim($_POST['horario'] ?? '');
    $nombre_apoderado = trim($_POST['nombre_apoderado'] ?? null) ?: null;
    $dni_apoderado = trim($_POST['dni_apoderado'] ?? null) ?: null;
    $correo_apoderado = trim($_POST['correo_apoderado'] ?? null) ?: null;
    $telefono_apoderado = trim($_POST['telefono_apoderado'] ?? null) ?: null;
    $notificar_emergencia = $_POST['notificar_emergencia'] ?? null;
    $observacion = trim($_POST['observacion'] ?? null);

    if (!$id_practicante || !$nombre) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        exit;
    }

    if (empty($modalidad_horario)) {
        echo json_encode(['success' => false, 'message' => 'La modalidad es requerida']);
        exit;
    }

    // Verificar que el DNI no esté usado por otro practicante
    if (!empty($dni)) {
        $stmt = $pdo->prepare("
            SELECT id_practicante FROM practicantes 
            WHERE dni = ? AND id_practicante != ? AND fecha_baja IS NULL
        ");
        $stmt->execute([$dni, $id_practicante]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El DNI ya está en uso']);
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE practicantes 
            SET nombre = ?, dni = ?, telefono = ?, telefono_emergencia = ?, 
                edad = ?, email = ?, direccion = ?, id_carrera = ?, modalidad_horario = ?, horario = ?, 
                nombre_apoderado = ?, dni_apoderado = ?, correo_apoderado = ?, telefono_apoderado = ?, 
                notificar_emergencia = ?, observacion = ?
            WHERE id_practicante = ?
        ");

        $stmt->execute([
            $nombre,
            !empty($dni) ? $dni : null,
            !empty($telefono) ? $telefono : null,
            !empty($telefono_emergencia) ? $telefono_emergencia : null,
            !empty($edad) ? $edad : null,
            !empty($email) ? $email : null,
            !empty($direccion) ? $direccion : null,
            !empty($id_carrera) ? $id_carrera : null,
            !empty($modalidad_horario) ? $modalidad_horario : null,
            !empty($horario) ? $horario : null,
            !empty($nombre_apoderado) ? $nombre_apoderado : null,
            !empty($dni_apoderado) ? $dni_apoderado : null,
            !empty($correo_apoderado) ? $correo_apoderado : null,
            !empty($telefono_apoderado) ? $telefono_apoderado : null,
            !empty($notificar_emergencia) ? $notificar_emergencia : null,
            !empty($observacion) ? $observacion : null,
            $id_practicante
        ]);

        echo json_encode(['success' => true, 'message' => 'Practicante actualizado exitosamente']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
    }
}

// ==================== DAR DE BAJA ====================
function darBajaPracticante() {
    global $pdo;

    $id_practicante = $_POST['id_practicante'] ?? null;

    if (!$id_practicante) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE practicantes SET fecha_baja = CURDATE() WHERE id_practicante = ?");
        $stmt->execute([$id_practicante]);

        echo json_encode(['success' => true, 'message' => 'Practicante dado de baja exitosamente']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al dar de baja: ' . $e->getMessage()]);
    }
}

function reactivarPracticante() {
    global $pdo;

    $id_practicante = $_POST['id_practicante'] ?? null;

    if (!$id_practicante) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE practicantes SET fecha_baja = NULL WHERE id_practicante = ?");
        $stmt->execute([$id_practicante]);

        echo json_encode(['success' => true, 'message' => 'Practicante reactivado exitosamente']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al reactivar: ' . $e->getMessage()]);
    }
}

// ==================== ELIMINAR ====================
function eliminarPracticante() {
    global $pdo;

    $id_practicante = $_POST['id_practicante'] ?? null;

    if (!$id_practicante) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }

    try {
        // Primero eliminar registros de asistencias
        $stmt = $pdo->prepare("DELETE FROM asistencias_practicantes WHERE id_practicante = ?");
        $stmt->execute([$id_practicante]);

        // Luego eliminar el practicante
        $stmt = $pdo->prepare("DELETE FROM practicantes WHERE id_practicante = ?");
        $stmt->execute([$id_practicante]);

        echo json_encode(['success' => true, 'message' => 'Practicante eliminado exitosamente']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
    }
}
?>
