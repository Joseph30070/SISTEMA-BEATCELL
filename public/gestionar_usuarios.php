<?php
require_once __DIR__ . '/../config/auth.php';
require __DIR__ . '/../config/db.php';

// Verificar que la conexión a la base de datos esté disponible
if (!$pdo) {
    die("Error de conexión a la base de datos");
}

/** @var PDO $pdo */

checkRole(['ADMINISTRADOR']);

// Obtener practicantes activos de la BD
try {
    $stmt = $pdo->query("
        SELECT p.*, c.nombre_carrera as carrera_nombre
        FROM practicantes p
        LEFT JOIN carreras c ON p.id_carrera = c.id_carrera
        WHERE p.fecha_baja IS NULL
        ORDER BY p.fecha_registro DESC
    ");
    $practicantes = $stmt->fetchAll();
} catch (PDOException $e) {
    $practicantes = [];
    $error = "Error al traer practicantes: " . $e->getMessage();
}
?>

<div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-100">

  <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">
    Gestionar Practicantes
  </h2>

  <div class="overflow-x-auto">
    <table class="w-full border border-gray-200 rounded-lg">

      <thead class="bg-teal-600 text-white text-sm">
        <tr>
          <th class="p-3 text-left">Nombre</th>
          <th class="p-3 text-left">DNI</th>
          <th class="p-3 text-left">Teléfono</th>
          <th class="p-3 text-left">Carrera</th>
          <th class="p-3 text-left">Horario</th>
          <th class="p-3 text-left">Observación</th>
          <th class="p-3 text-center">Acciones</th>
        </tr>
      </thead>

      <tbody>
        <?php if (empty($practicantes)): ?>
          <tr>
            <td colspan="7" class="p-6 text-center text-gray-500">
              No hay practicantes registrados
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($practicantes as $p): ?>
          <tr class="border-t hover:bg-gray-50 text-sm">
            <td class="p-3 font-semibold text-gray-800"><?= htmlspecialchars($p['nombre']) ?></td>
            <td class="p-3"><?= htmlspecialchars($p['dni'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['telefono'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['carrera_nombre'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['horario'] ?? '-') ?></td>
            <td class="p-3 text-xs max-w-xs truncate" title="<?= htmlspecialchars($p['observacion'] ?? '') ?>">
              <?= htmlspecialchars($p['observacion']) ? htmlspecialchars(substr($p['observacion'], 0, 30)) . '...' : '-' ?>
            </td>

            <td class="p-3 text-center flex justify-center gap-2">
              <button 
                onclick="editarPracticante(<?= $p['id_practicante'] ?>, '<?= addslashes($p['nombre']) ?>', '<?= $p['dni'] ?>', '<?= $p['telefono'] ?>', '<?= $p['telefono_emergencia'] ?>', '<?= $p['id_carrera'] ?>', '<?= $p['horario'] ?>', '<?= addslashes($p['observacion']) ?>')"
                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-xs font-semibold transition">
                ✏️ Editar
              </button>

              <button 
                onclick="darDeBaja(<?= $p['id_practicante'] ?>, '<?= addslashes($p['nombre']) ?>')"
                class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-xs font-semibold transition">
                🚫 Dar de Baja
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>

    </table>
  </div>

</div>

<!-- MODAL EDITAR -->
<div id="modalEditar" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
  <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
    <h2 class="text-xl font-bold mb-4">Editar Practicante</h2>
    
    <form id="formEditarPracticante" class="space-y-4">
      <input type="hidden" name="action" value="actualizar">
      <input type="hidden" name="id_practicante" id="editId">

      <div>
        <label class="block text-sm font-semibold mb-1">Nombre *</label>
        <input type="text" name="nombre" id="editNombre" required
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">DNI</label>
        <input type="text" name="dni" id="editDni"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Teléfono</label>
        <input type="text" name="telefono" id="editTelefono"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Teléfono Emergencia</label>
        <input type="text" name="telefono_emergencia" id="editTelefonoEmergencia"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Carrera</label>
        <select name="id_carrera" id="editCarrera"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
          <option value="">-- Seleccione --</option>
          <option value="1">Desarrollo de Software</option>
          <option value="2">Diseño Gráfico</option>
          <option value="3">Electrónica</option>
          <option value="5">Mecatrónica</option>
          <option value="4">Redes y Comunicaciones</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Horario</label>
        <input type="text" name="horario" id="editHorario"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Observación</label>
        <textarea name="observacion" id="editObservacion" rows="2"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none"></textarea>
      </div>

      <div class="flex gap-2 justify-end">
        <button type="button" onclick="cerrarModal()" 
          class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
          Cancelar
        </button>
        <button type="submit" 
          class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700 font-semibold">
          Guardar
        </button>
      </div>
    </form>

    <div id="mensajeModal" class="mt-3"></div>
  </div>
</div>

<script>
function editarPracticante(id, nombre, dni, telefono, tel_emergencia, carrera, horario, observacion) {
  document.getElementById('editId').value = id;
  document.getElementById('editNombre').value = nombre;
  document.getElementById('editDni').value = dni;
  document.getElementById('editTelefono').value = telefono;
  document.getElementById('editTelefonoEmergencia').value = tel_emergencia;
  document.getElementById('editCarrera').value = carrera || '';
  document.getElementById('editHorario').value = horario;
  document.getElementById('editObservacion').value = observacion;
  document.getElementById('modalEditar').classList.remove('hidden');
}

function cerrarModal() {
  document.getElementById('modalEditar').classList.add('hidden');
  document.getElementById('mensajeModal').innerHTML = '';
}

function darDeBaja(id, nombre) {
  if (confirm(`¿Está seguro de que desea dar de baja a ${nombre}?`)) {
    const formData = new FormData();
    formData.append('action', 'dar_baja');
    formData.append('id_practicante', id);

    fetch('../process/process_practicantes.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert(data.message);
        location.reload();
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => alert('Error: ' + error.message));
  }
}

// Manejar submit del formulario de editar
document.getElementById('formEditarPracticante').addEventListener('submit', async function(e) {
  e.preventDefault();

  const formData = new FormData(this);

  try {
    const response = await fetch('../process/process_practicantes.php', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();
    const mensajeDiv = document.getElementById('mensajeModal');

    if (data.success) {
      mensajeDiv.innerHTML = `
        <div class="bg-green-50 text-green-700 p-3 rounded border border-green-200 text-sm">
          ✅ ${data.message}
        </div>
      `;
      setTimeout(() => {
        cerrarModal();
        location.reload();
      }, 1500);
    } else {
      mensajeDiv.innerHTML = `
        <div class="bg-red-50 text-red-700 p-3 rounded border border-red-200 text-sm">
          ❌ ${data.message}
        </div>
      `;
    }

  } catch (error) {
    alert('Error: ' + error.message);
  }
});
</script>
