<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

$pdo = require __DIR__ . '/../config/db.php';

$id = $_GET['id'] ?? null;

if(!$id){
    die("ID inválido");
}

// =========================
// OBTENER ALUMNO
// =========================
$stmt = $pdo->prepare("
SELECT a.*, m.id_grupo
FROM alumnos a
LEFT JOIN matriculas m ON m.id_alumno = a.id_alumno
WHERE a.id_alumno = ?
");
$stmt->execute([$id]);
$alumno = $stmt->fetch();

if(!$alumno){
    die("Alumno no encontrado");
}

// =========================
// CURSOS
// =========================
$cursos = $pdo->query("SELECT * FROM cursos")->fetchAll();

$title = "Editar Alumno";
$active = "registro";

ob_start();
?>

<h2 class="text-2xl font-bold mb-6">Editar Alumno</h2>

<form action="../process/process_editar_alumno.php" method="POST" class="space-y-6">

<input type="hidden" name="id_alumno" value="<?= $alumno['id_alumno'] ?>">

<!-- DATOS -->
<div class="bg-white p-6 rounded shadow">

<label>Nombre</label>
<input name="nombre" value="<?= htmlspecialchars($alumno['nombre']) ?>"
class="w-full border px-3 py-2 rounded">

<label>DNI</label>
<input name="dni" value="<?= $alumno['dni'] ?>"
class="w-full border px-3 py-2 rounded">

<label>Teléfono</label>
<input name="telefono" value="<?= $alumno['telefono'] ?>"
class="w-full border px-3 py-2 rounded">

<label>Teléfono Padres</label>
<input name="telefonopadres" value="<?= $alumno['telefonopadres'] ?>"
class="w-full border px-3 py-2 rounded">

<label>Teléfono Apoderado</label>
<input name="telefonoapoderado" value="<?= $alumno['telefonoapoderado'] ?>"
class="w-full border px-3 py-2 rounded">

<label>Contacto Pago</label>
<select name="contacto_pago" class="w-full border px-3 py-2 rounded">
    <option <?= $alumno['contacto_pago']=='Alumno'?'selected':'' ?>>Alumno</option>
    <option <?= $alumno['contacto_pago']=='Padre'?'selected':'' ?>>Padre</option>
    <option <?= $alumno['contacto_pago']=='Apoderado'?'selected':'' ?>>Apoderado</option>
</select>

</div>

<div class="flex justify-end">
<button class="bg-teal-600 text-white px-5 py-2 rounded">
Guardar cambios
</button>
</div>

</form>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
