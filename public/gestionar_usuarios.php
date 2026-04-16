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
        ORDER BY p.fecha_registro DESC
    ");
    $practicantes = $stmt->fetchAll();
} catch (PDOException $e) {
    $practicantes = [];
    $error = "Error al traer practicantes: " . $e->getMessage();
}
?>

<div class="flex justify-between items-center mb-6">

  <!-- Titulo Izquierda -->
  <h2 class="text-2xl font-bold text-gray-800">
    Gestionar Practicantes
  </h2>
  <!-- Botones Derecha -->
  <div class="flex gap-2">

    <button id="btnToggleFiltros" type="button"
      class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 flex items-center gap-2">
      <i class="fas fa-filter"></i>
      Filtros
    </button>

    <button onclick="exportarExcelPracticantes()"
      class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center gap-2">
      <i class="fas fa-file-excel"></i>
      Excel
    </button>

    <button onclick="exportarPDFPracticantes()"
      class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 flex items-center gap-2">
      <i class="fas fa-file-pdf"></i>
      PDF
    </button>
  </div>
</div>

    <div id="panelFiltrosPracticantes" class="hidden absolute right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg p-4 w-full md:w-80 z-50">
      <div class="mb-4 flex items-center justify-between">
        <h3 class="font-semibold">Filtros</h3>
        <button type="button" onclick="toggleFiltrosPracticantes()" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-semibold mb-1">Buscar por Nombre o DNI</label>
          <input id="buscarPracticante" type="text" placeholder="Nombre o DNI"
            class="w-full border border-gray-300 rounded px-3 py-2" oninput="filtrarPracticantes()">
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1">Estado</label>
          <select id="filtroEstadoPracticante" class="w-full border border-gray-300 rounded px-3 py-2" onchange="filtrarPracticantes()">
            <option value="Todos">Todos</option>
            <option value="Activo">Activo</option>
            <option value="Baja">Baja</option>
          </select>
        </div>
      </div>
    </div>

  <div class="overflow-x-auto">
    <table id="tablaPracticantes" class="w-full border border-gray-200 rounded-lg">

      <thead class="bg-teal-600 text-white text-sm">
        <tr>
          <th class="p-3 text-left">Nombre</th>
          <th class="p-3 text-left">DNI</th>
          <th class="p-3 text-left">Edad</th>
          <th class="p-3 text-left">Email</th>
          <th class="p-3 text-left">Teléfono</th>
          <th class="p-3 text-left">Teléfono Emergencia</th>
          <th class="p-3 text-left">Dirección</th>
          <th class="p-3 text-left">Carrera</th>
          <th class="p-3 text-left">Modalidad</th>
          <th class="p-3 text-left">Horario</th>
          <th class="p-3 text-left">Nombre Apoderado</th>
          <th class="p-3 text-left">DNI Apoderado</th>
          <th class="p-3 text-left">Correo Apoderado</th>
          <th class="p-3 text-left">Teléfono Apoderado</th>
          <th class="p-3 text-left">Notificar</th>
          <th class="p-3 text-left">Observación</th>
          <th class="p-3 text-left">Estado</th>
          <th class="p-3 text-left">Registro</th>
          <th class="p-3 text-left">Fecha Baja</th>
          <th class="p-3 text-center">Acciones</th>
        </tr>
      </thead>

      <tbody>
        <?php if (empty($practicantes)): ?>
          <tr>
            <td colspan="21" class="p-6 text-center text-gray-500">
              No hay practicantes registrados
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($practicantes as $p): ?>
          <?php $estado = $p['fecha_baja'] ? 'Baja' : 'Activo'; ?>
          <tr class="border-t hover:bg-gray-50 text-sm" data-estado="<?= $estado ?>">
            <td class="p-3 font-semibold text-gray-800"><?= htmlspecialchars($p['nombre']) ?></td>
            <td class="p-3"><?= htmlspecialchars($p['dni'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['edad'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['email'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['telefono'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['telefono_emergencia'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['direccion'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['carrera_nombre'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['modalidad_horario'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['horario'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['nombre_apoderado'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['dni_apoderado'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['correo_apoderado'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['telefono_apoderado'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['notificar_emergencia'] ?? '-') ?></td>
            <td class="p-3 text-xs max-w-xs truncate" title="<?= htmlspecialchars($p['observacion'] ?? '') ?>">
              <?= htmlspecialchars($p['observacion']) ? htmlspecialchars(substr($p['observacion'], 0, 30)) . '...' : '-' ?>
            </td>
            <td class="p-3">
              <?php if ($estado === 'Activo'): ?>
                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-semibold">Activo</span>
              <?php else: ?>
                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-semibold">Baja</span>
              <?php endif; ?>
            </td>
            <td class="p-3"><?= htmlspecialchars($p['fecha_registro'] ?? '-') ?></td>
            <td class="p-3"><?= htmlspecialchars($p['fecha_baja'] ?? '-') ?></td>

            <td class="p-3 text-center flex justify-center gap-2">
              <button 
                onclick="editarPracticante(
                  <?= $p['id_practicante'] ?>,
                  '<?= addslashes($p['nombre']) ?>',
                  '<?= addslashes($p['dni'] ?? '') ?>',
                  '<?= addslashes($p['edad'] ?? '') ?>',
                  '<?= addslashes($p['email'] ?? '') ?>',
                  '<?= addslashes($p['telefono'] ?? '') ?>',
                  '<?= addslashes($p['telefono_emergencia'] ?? '') ?>',
                  '<?= addslashes($p['direccion'] ?? '') ?>',
                  '<?= addslashes($p['id_carrera'] ?? '') ?>',
                  '<?= addslashes($p['modalidad_horario'] ?? '') ?>',
                  '<?= addslashes($p['horario'] ?? '') ?>',
                  '<?= addslashes($p['nombre_apoderado'] ?? '') ?>',
                  '<?= addslashes($p['dni_apoderado'] ?? '') ?>',
                  '<?= addslashes($p['correo_apoderado'] ?? '') ?>',
                  '<?= addslashes($p['telefono_apoderado'] ?? '') ?>',
                  '<?= addslashes($p['notificar_emergencia'] ?? '') ?>',
                  '<?= addslashes($p['observacion'] ?? '') ?>'
                )"
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
        <label class="block text-sm font-semibold mb-1">Edad</label>
        <input type="text" name="edad" id="editEdad"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Email</label>
        <input type="email" name="email" id="editEmail"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Teléfono</label>
        <input type="text" name="telefono" id="editTelefono"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Dirección</label>
        <input type="text" name="direccion" id="editDireccion"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Teléfono Emergencia</label>
        <input type="text" name="telefono_emergencia" id="editTelefonoEmergencia"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Modalidad</label>
        <select name="modalidad_horario" id="editModalidad"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
          <option value="">-- Seleccione --</option>
          <option value="Presencial">Presencial</option>
          <option value="Virtual">Virtual</option>
          <option value="Híbrido">Híbrido</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Dirección</label>
        <input type="text" name="direccion" id="editDireccion"
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
        <label class="block text-sm font-semibold mb-1">Nombre Apoderado</label>
        <input type="text" name="nombre_apoderado" id="editNombreApoderado"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">DNI Apoderado</label>
        <input type="text" name="dni_apoderado" id="editDniApoderado"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Correo Apoderado</label>
        <input type="email" name="correo_apoderado" id="editCorreoApoderado"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Teléfono Apoderado</label>
        <input type="text" name="telefono_apoderado" id="editTelefonoApoderado"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Notificar en Emergencia</label>
        <select name="notificar_emergencia" id="editNotificarEmergencia"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
          <option value="">-- Seleccione --</option>
          <option value="Sí">Sí</option>
          <option value="No">No</option>
        </select>
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
function editarPracticante(id, nombre, dni, edad, email, telefono, tel_emergencia, direccion, carrera, modalidad, horario, nombreApoderado, dniApoderado, correoApoderado, telefonoApoderado, notificar, observacion) {
  document.getElementById('editId').value = id;
  document.getElementById('editNombre').value = nombre;
  document.getElementById('editDni').value = dni;
  document.getElementById('editEdad').value = edad;
  document.getElementById('editEmail').value = email;
  document.getElementById('editTelefono').value = telefono;
  document.getElementById('editTelefonoEmergencia').value = tel_emergencia;
  document.getElementById('editDireccion').value = direccion;
  document.getElementById('editCarrera').value = carrera || '';
  document.getElementById('editModalidad').value = modalidad || '';
  document.getElementById('editHorario').value = horario;
  document.getElementById('editNombreApoderado').value = nombreApoderado;
  document.getElementById('editDniApoderado').value = dniApoderado;
  document.getElementById('editCorreoApoderado').value = correoApoderado;
  document.getElementById('editTelefonoApoderado').value = telefonoApoderado;
  document.getElementById('editNotificarEmergencia').value = notificar || '';
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

function filtrarPracticantes() {
  const filtro = document.getElementById('buscarPracticante').value.toLowerCase();
  const estado = document.getElementById('filtroEstadoPracticante').value;
  const filas = document.querySelectorAll('#tablaPracticantes tbody tr');

  filas.forEach(fila => {
    const nombre = fila.children[0].innerText.toLowerCase();
    const dni = fila.children[1].innerText.toLowerCase();
    const filaEstado = fila.dataset.estado;

    const coincideTexto = !filtro || nombre.includes(filtro) || dni.includes(filtro);
    const coincideEstado = estado === 'Todos' || filaEstado === estado;

    fila.style.display = coincideTexto && coincideEstado ? '' : 'none';
  });
}

function exportarExcelPracticantes() {
  const filas = document.querySelectorAll('#tablaPracticantes tbody tr');
  let contenido = `\n    <meta charset="UTF-8">\n    <table border="1">\n    <tr>\n        <th>Nombre</th>\n        <th>DNI</th>\n        <th>Teléfono</th>\n        <th>Teléfono Emergencia</th>\n        <th>Carrera</th>\n        <th>Horario</th>\n        <th>Observación</th>\n        <th>Estado</th>\n        <th>Registro</th>\n        <th>Fecha Baja</th>\n    </tr>\n    `;

  filas.forEach(fila => {
    if (fila.style.display === 'none') return;
    contenido += '<tr>'; 
    for (let i = 0; i < 10; i++) {
      contenido += `<td>${fila.children[i].innerText}</td>`;
    }
    contenido += '</tr>';
  });
  contenido += '</table>';

  const blob = new Blob(['\ufeff', contenido], {
    type: 'application/vnd.ms-excel;charset=utf-8;'
  });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'reporte_practicantes.xls';
  a.click();
}

function exportarPDFPracticantes() {
  const filas = document.querySelectorAll('#tablaPracticantes tbody tr');
  const fecha = new Date().toLocaleDateString();
  let contenido = `\n    <h3>Reporte de Practicantes</h3>\n    <p>Fecha: ${fecha}</p>\n    <table border="1" cellpadding="8">\n    <tr style="background-color:#0f766e;color:white;">\n        <th>Nombre</th>\n        <th>DNI</th>\n        <th>Teléfono</th>\n        <th>Teléfono Emergencia</th>\n        <th>Carrera</th>\n        <th>Horario</th>\n        <th>Observación</th>\n        <th>Estado</th>\n        <th>Registro</th>\n        <th>Fecha Baja</th>\n    </tr>\n    `;

  filas.forEach(fila => {
    if (fila.style.display === 'none') return;
    contenido += '<tr>';
    for (let i = 0; i < 10; i++) {
      contenido += `<td>${fila.children[i].innerText}</td>`;
    }
    contenido += '</tr>';
  });
  contenido += '</table>';

  const element = document.createElement('div');
  element.innerHTML = contenido;
  const opt = {
    margin: 10,
    filename: 'reporte_practicantes.pdf',
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
  };
  html2pdf().set(opt).from(element).save();
}

function toggleFiltrosPracticantes() {
  const panel = document.getElementById('panelFiltrosPracticantes');
  panel.classList.toggle('hidden');
}

document.getElementById('btnToggleFiltros').addEventListener('click', toggleFiltrosPracticantes);
</script>
