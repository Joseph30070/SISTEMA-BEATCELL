<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

checkRole(['ADMINISTRADOR']);

$pdo = require __DIR__ . '/../config/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo '<div class="text-red-400">ID inválido</div>';
    exit;
}

// Obtener datos del practicante
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.nombre_carrera AS carrera_nombre
        FROM practicantes p
        LEFT JOIN carreras c ON p.id_carrera = c.id_carrera
        WHERE p.id_practicante = ?
    ");
    $stmt->execute([$id]);
    $practicante = $stmt->fetch();

    if (!$practicante) {
        echo '<div class="text-red-400">Practicante no encontrado</div>';
        exit;
    }
} catch (PDOException $e) {
    echo '<div class="text-red-400">Error al cargar practicante: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}

// Obtener carreras
$stmt = $pdo->query("SELECT * FROM carreras ORDER BY nombre_carrera ASC");
$carreras = $stmt->fetchAll();
?>

<form id="formEditarPracticante" class="space-y-6">
<input type="hidden" name="action" value="actualizar">
<input type="hidden" name="id_practicante" value="<?= htmlspecialchars($id) ?>">
<input type="hidden" name="horario" id="inputHorarioEditar" value="<?= htmlspecialchars($practicante['horario'] ?? '') ?>">

<!-- ================= HORARIO ================= -->
<section class="bg-gray-800 rounded-lg p-6 border border-gray-700">
    <h3 class="text-lg font-semibold mb-4 text-teal-300">Horario</h3>
    <div class="flex flex-wrap gap-2 mb-4">
        <button type="button" id="btnNormalEditar" onclick="mostrarModoEditar('normal')" class="px-4 py-2 rounded bg-teal-600 text-white">Normal</button>
        <button type="button" id="btnFlexibleEditar" onclick="mostrarModoEditar('flexible')" class="px-4 py-2 rounded bg-gray-700 text-white hover:bg-gray-600">Flexible</button>
    </div>

    <div id="modo-normal-editar" class="space-y-4">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm mb-1">Hora Inicio *</label>
                <input type="time" id="horaInicioEditar" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm mb-1">Hora Fin *</label>
                <input type="time" id="horaFinEditar" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
            </div>
        </div>
        <div class="grid md:grid-cols-4 gap-3">
            <?php foreach (['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $dia): ?>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="dias[]" value="<?= $dia ?>" class="h-4 w-4 text-teal-500 rounded border-gray-600">
                <?= $dia ?>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="modo-flexible-editar" class="hidden space-y-4">
        <div class="grid md:grid-cols-2 gap-4">
            <label class="border border-gray-700 rounded-lg p-3">
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" id="flex_lunes_editar" name="lunes_activo" value="Lunes" class="h-4 w-4 text-teal-500 rounded border-gray-600">
                    <span class="text-sm">Lunes</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="time" id="inicio_lunes_editar" name="lunes_inicio" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                    <input type="time" id="fin_lunes_editar" name="lunes_fin" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                </div>
            </label>
            <label class="border border-gray-700 rounded-lg p-3">
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" id="flex_martes_editar" name="martes_activo" value="Martes" class="h-4 w-4 text-teal-500 rounded border-gray-600">
                    <span class="text-sm">Martes</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="time" id="inicio_martes_editar" name="martes_inicio" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                    <input type="time" id="fin_martes_editar" name="martes_fin" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                </div>
            </label>
            <label class="border border-gray-700 rounded-lg p-3">
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" id="flex_miercoles_editar" name="miercoles_activo" value="Miércoles" class="h-4 w-4 text-teal-500 rounded border-gray-600">
                    <span class="text-sm">Miércoles</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="time" id="inicio_miercoles_editar" name="miercoles_inicio" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                    <input type="time" id="fin_miercoles_editar" name="miercoles_fin" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                </div>
            </label>
            <label class="border border-gray-700 rounded-lg p-3">
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" id="flex_jueves_editar" name="jueves_activo" value="Jueves" class="h-4 w-4 text-teal-500 rounded border-gray-600">
                    <span class="text-sm">Jueves</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="time" id="inicio_jueves_editar" name="jueves_inicio" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                    <input type="time" id="fin_jueves_editar" name="jueves_fin" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                </div>
            </label>
            <label class="border border-gray-700 rounded-lg p-3">
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" id="flex_viernes_editar" name="viernes_activo" value="Viernes" class="h-4 w-4 text-teal-500 rounded border-gray-600">
                    <span class="text-sm">Viernes</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="time" id="inicio_viernes_editar" name="viernes_inicio" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                    <input type="time" id="fin_viernes_editar" name="viernes_fin" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                </div>
            </label>
            <label class="border border-gray-700 rounded-lg p-3">
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" id="flex_sabado_editar" name="sabado_activo" value="Sábado" class="h-4 w-4 text-teal-500 rounded border-gray-600">
                    <span class="text-sm">Sábado</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="time" id="inicio_sabado_editar" name="sabado_inicio" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                    <input type="time" id="fin_sabado_editar" name="sabado_fin" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                </div>
            </label>
            <label class="border border-gray-700 rounded-lg p-3">
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" id="flex_domingo_editar" name="domingo_activo" value="Domingo" class="h-4 w-4 text-teal-500 rounded border-gray-600">
                    <span class="text-sm">Domingo</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="time" id="inicio_domingo_editar" name="domingo_inicio" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                    <input type="time" id="fin_domingo_editar" name="domingo_fin" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                </div>
            </label>
        </div>
    </div>
</section>

<!-- ================= DATOS ================= -->
<section class="bg-gray-800 rounded-lg p-6 border border-gray-700">
    <h3 class="text-lg font-semibold mb-4 text-teal-300">Datos del Practicante</h3>

    <div class="grid md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Nombre Completo *</label>
            <input name="nombre" required value="<?= htmlspecialchars($practicante['nombre']) ?>" 
                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
        </div>

        <div>
            <label class="block text-sm mb-1">DNI *</label>
            <input name="dni" maxlength="8" required value="<?= htmlspecialchars($practicante['dni'] ?? '') ?>" 
                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white"
                oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        </div>

        <div>
            <label class="block text-sm mb-1">Edad</label>
            <input type="number" name="edad" value="<?= htmlspecialchars($practicante['edad'] ?? '') ?>" 
                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
        </div>

        <div>
            <label class="block text-sm mb-1">Correo</label>
            <input type="email" name="email" value="<?= htmlspecialchars($practicante['email'] ?? '') ?>" 
                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Dirección</label>
            <input name="direccion" value="<?= htmlspecialchars($practicante['direccion'] ?? '') ?>" 
                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
        </div>

        <div>
            <label class="block text-sm mb-1">Teléfono</label>
            <input name="telefono" maxlength="9" value="<?= htmlspecialchars($practicante['telefono'] ?? '') ?>" 
                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white"
                oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        </div>

        <div>
            <label class="block text-sm mb-1">Teléfono Emergencia</label>
            <input name="telefono_emergencia" maxlength="9" value="<?= htmlspecialchars($practicante['telefono_emergencia'] ?? '') ?>" 
                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white"
                oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        </div>

        <div>
            <label class="block text-sm mb-1">Modalidad *</label>
            <select name="modalidad_horario" required class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                <option value="">Seleccione</option>
                <option value="Presencial" <?= $practicante['modalidad_horario'] === 'Presencial' ? 'selected' : '' ?>>Presencial</option>
                <option value="Virtual" <?= $practicante['modalidad_horario'] === 'Virtual' ? 'selected' : '' ?>>Virtual</option>
                <option value="Semipresencial" <?= $practicante['modalidad_horario'] === 'Semipresencial' ? 'selected' : '' ?>>Semipresencial</option>
            </select>
        </div>

        <div>
            <label class="block text-sm mb-1">Carrera *</label>
            <select name="id_carrera" required class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                <option value="">Seleccione</option>
                <?php foreach($carreras as $c): ?>
                <option value="<?= $c['id_carrera'] ?>" <?= $practicante['id_carrera'] == $c['id_carrera'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre_carrera']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="md:col-span-3">
            <label class="block text-sm mb-1">Observación</label>
            <textarea name="observacion" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white"><?= htmlspecialchars($practicante['observacion'] ?? '') ?></textarea>
        </div>
    </div>
</section>

<!-- ================= APODERADO ================= -->
<section class="bg-gray-800 rounded-lg p-6 border border-gray-700">
    <h3 class="text-lg font-semibold mb-4 text-teal-300">Datos del Apoderado</h3>

    <div class="grid md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm mb-1">Nombre</label>
            <input name="nombre_apoderado" value="<?= htmlspecialchars($practicante['nombre_apoderado'] ?? '') ?>" 
                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
        </div>

        <div>
            <label class="block text-sm mb-1">DNI</label>
            <input name="dni_apoderado" maxlength="8" value="<?= htmlspecialchars($practicante['dni_apoderado'] ?? '') ?>" 
                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white"
                oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        </div>

        <div>
            <label class="block text-sm mb-1">Correo</label>
            <input name="correo_apoderado" value="<?= htmlspecialchars($practicante['correo_apoderado'] ?? '') ?>" 
                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
        </div>

        <div>
            <label class="block text-sm mb-1">Teléfono</label>
            <input name="telefono_apoderado" maxlength="9" value="<?= htmlspecialchars($practicante['telefono_apoderado'] ?? '') ?>" 
                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white"
                oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        </div>

        <div>
            <label class="block text-sm mb-1">Notificar en emergencia</label>
            <select name="notificar_emergencia" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                <option value="">Seleccione</option>
                <option value="Si" <?= $practicante['notificar_emergencia'] === 'Si' ? 'selected' : '' ?>>Si</option>
                <option value="No" <?= $practicante['notificar_emergencia'] === 'No' ? 'selected' : '' ?>>No</option>
            </select>
        </div>
    </div>
</section>

<div class="flex justify-end gap-3">
    <button type="button" onclick="window.cerrarModalEditarPracticante()" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Cancelar</button>
    <button type="submit" class="px-5 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">Guardar cambios</button>
</div>

</form>
