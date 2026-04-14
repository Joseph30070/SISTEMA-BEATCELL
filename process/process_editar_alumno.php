<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

$pdo = require __DIR__ . '/../config/db.php';

try {

    if(!isset($_POST['id_alumno'])){
        throw new Exception("ID inválido");
    }

    $id = $_POST['id_alumno'];

    /* =========================
       DATOS
    ========================= */

    $nombre = $_POST['nombre'] ?? '';
    $dni = $_POST['dni'] ?? '';
    $edad = $_POST['edad'] ?? null;

    $telefono = $_POST['telefono'] ?? '';
    $telefonopadres = $_POST['telefonopadres'] ?? '';
    $telefonoapoderado = $_POST['telefonoapoderado'] ?? '';

    $email = $_POST['email'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    $nombre_apoderado = $_POST['nombre_apoderado'] ?? '';
    $dni_apoderado = $_POST['dni_apoderado'] ?? '';
    $correo_apoderado = $_POST['correo_apoderado'] ?? '';

    $notificar_emergencia = $_POST['notificar_emergencia'] ?? '';

    $contacto_pago = $_POST['contacto_pago'] ?? '';

    $tipo_ciclo = $_POST['tipo_ciclo'] ?? '';
    $medio_captacion = $_POST['medio_captacion'] ?? '';

    $id_grupo = $_POST['id_grupo'] ?? null;

    /* =========================
       TRANSACCIÓN
    ========================= */

    $pdo->beginTransaction();

    /* =========================
       UPDATE ALUMNO
    ========================= */

    $stmt = $pdo->prepare("
    UPDATE alumnos SET

        nombre = ?,
        dni = ?,
        edad = ?,

        telefono = ?,
        telefonopadres = ?,
        telefonoapoderado = ?,

        email = ?,
        direccion = ?,

        nombre_apoderado = ?,
        dni_apoderado = ?,
        correo_apoderado = ?,

        notificar_emergencia = ?,

        contacto_pago = ?,

        tipo_ciclo = ?,
        medio_captacion = ?

    WHERE id_alumno = ?
    ");

    $stmt->execute([

        $nombre,
        $dni,
        $edad,

        $telefono,
        $telefonopadres,
        $telefonoapoderado,

        $email,
        $direccion,

        $nombre_apoderado,
        $dni_apoderado,
        $correo_apoderado,

        $notificar_emergencia,

        $contacto_pago,

        $tipo_ciclo,
        $medio_captacion,

        $id

    ]);

    /* =========================
       UPDATE MATRÍCULA
    ========================= */

    if($id_grupo){

        $stmt = $pdo->prepare("
        UPDATE matriculas
        SET id_grupo = ?
        WHERE id_alumno = ?
        ");

        $stmt->execute([
            $id_grupo,
            $id
        ]);

    }

    /* =========================
       COMMIT
    ========================= */

    $pdo->commit();

    header("Location: ../public/registro_alumnos.php?success=Alumno actualizado correctamente");
    exit;

} catch(Exception $e){

    if($pdo->inTransaction()){
        $pdo->rollBack();
    }

    header("Location: ../public/registro_alumnos.php?error=" . urlencode($e->getMessage()));
    exit;

}
