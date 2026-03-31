<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

$pdo = require __DIR__ . '/../config/db.php';

try {

$id = $_POST['id_alumno'];

$nombre = $_POST['nombre'];
$dni = $_POST['dni'];
$telefono = $_POST['telefono'];
$telefonopadres = $_POST['telefonopadres'];
$telefonoapoderado = $_POST['telefonoapoderado'];
$contacto_pago = $_POST['contacto_pago'];

// =========================
// UPDATE
// =========================
$stmt = $pdo->prepare("
UPDATE alumnos SET
nombre = ?,
dni = ?,
telefono = ?,
telefonopadres = ?,
telefonoapoderado = ?,
contacto_pago = ?
WHERE id_alumno = ?
");

$stmt->execute([
$nombre,
$dni,
$telefono,
$telefonopadres,
$telefonoapoderado,
$contacto_pago,
$id
]);

header("Location: ../public/registro_alumnos.php?success=Alumno actualizado");
exit;

} catch(Exception $e){

header("Location: ../public/registro_alumnos.php?error=" . urlencode($e->getMessage()));
exit;

}