<?php
require_once __DIR__ . '/../config/auth.php';
require __DIR__ . '/../config/db.php';

checkRole(['ADMINISTRADOR']);

$pdo = require __DIR__ . '/../config/db.php';

// OBTENER CARRERAS
$stmt = $pdo->query("SELECT * FROM carreras ORDER BY nombre_carrera ASC");
$carreras = $stmt->fetchAll();
try {
    $stmt = $pdo->query("
        SELECT p.*, c.nombre_carrera AS carrera_nombre
        FROM practicantes p
        LEFT JOIN carreras c ON p.id_carrera = c.id_carrera
        ORDER BY p.fecha_registro DESC, p.id_practicante DESC
    ");
    $practicantes = $stmt->fetchAll();
} catch (PDOException $e) {
    $practicantes = [];
}
$title = 'Gestión de Practicantes';
$active = 'practicantes';

ob_start();
?>

<style>
.tab-content {
  transition: all 0.3s ease;
  opacity: 1;
}
.tab-content.hidden {
  opacity: 0;
  transform: translateY(10px);
  pointer-events: none;
  position: absolute;
  width: 100%;
}

.loader {
  border: 4px solid #e5e7eb;
  border-top: 4px solid #14b8a6;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.section-card:hover {
  transform: translateY(-3px);
}
</style>

<h2 class="text-3xl font-bold text-gray-800 mb-6">
  Gestión de Practicantes
</h2>

<!-- TABS -->
<div class="flex border-b mb-6">
  <button id="tab-registrar" class="px-4 py-2 bg-teal-600 text-white"
    onclick="mostrarTab('registrar')">
    Registrar
  </button>

  <button id="tab-gestionar" class="px-4 py-2 text-gray-600"
    onclick="mostrarTab('gestionar')">
    Gestionar
  </button>
</div>

<!-- TAB REGISTRAR -->
<div id="contenido-registrar" class="tab-content">

<form id="formRegistrarPracticante" class="space-y-8">
<input type="hidden" name="action" value="registrar">
<input type="hidden" name="horario" id="inputHorario">

<!-- ================= DATOS ================= -->
<section class="bg-white rounded-2xl shadow-md p-8 border">

<h3 class="text-xl font-semibold mb-6">Datos del Practicante</h3>

<div class="grid md:grid-cols-3 gap-5">

<div class="md:col-span-2">
<label>Nombre Completo *</label>
<input name="nombre" required class="w-full border rounded px-3 py-2">
</div>

<div>
<label>DNI *</label>
<input name="dni" maxlength="8" required
class="w-full border rounded px-3 py-2"
oninput="this.value=this.value.replace(/[^0-9]/g,'')">
</div>

<div>
<label>Edad</label>
<input type="number" name="edad" class="w-full border rounded px-3 py-2">
</div>

<div>
<label>Correo</label>
<input type="email" name="email" class="w-full border rounded px-3 py-2">
</div>

<div class="md:col-span-2">
<label>Dirección</label>
<input name="direccion" class="w-full border rounded px-3 py-2">
</div>

<div>
<label>Teléfono</label>
<input name="telefono" maxlength="9"
class="w-full border rounded px-3 py-2"
oninput="this.value=this.value.replace(/[^0-9]/g,'')">
</div>

<div>
<label>Teléfono Emergencia</label>
<input name="telefono_emergencia" maxlength="9"
class="w-full border rounded px-3 py-2"
oninput="this.value=this.value.replace(/[^0-9]/g,'')">
</div>

<div>
<label>Modalidad *</label>
<select name="modalidad_horario" required class="w-full border rounded px-3 py-2">
<option value="">Seleccione</option>
<option>Presencial</option>
<option>Virtual</option>
<option>Semipresencial</option> <!-- 🔥 NUEVO -->
</select>
</div>

<div>
<label>Carrera *</label>
<select name="id_carrera" required class="w-full border rounded px-3 py-2">
<option value="">Seleccione</option>
<?php foreach($carreras as $c): ?>
<option value="<?= $c['id_carrera'] ?>">
<?= htmlspecialchars($c['nombre_carrera']) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div>
<label>Fecha Registro</label>
<input type="date" name="fecha_registro" value="<?= date('Y-m-d') ?>"
class="w-full border rounded px-3 py-2">
</div>

<div class="md:col-span-3">
<label>Observación</label>
<textarea name="observacion" class="w-full border rounded px-3 py-2"></textarea>
</div>

</div>
</section>

<!-- ================= HORARIO ================= -->
<section class="bg-green-50 rounded-2xl shadow-md p-8 border">

<h3 class="text-xl font-semibold mb-6">Horario</h3>

<div class="flex gap-2 mb-4">
<button type="button" id="btnNormal"
onclick="cambiarModo('normal')"
class="px-4 py-2 bg-teal-600 text-white rounded">
Normal
</button>

<button type="button" id="btnFlexible"
onclick="cambiarModo('flexible')"
class="px-4 py-2 bg-gray-200 rounded">
Flexible
</button>
</div>

<!-- NORMAL -->
<div id="modo-normal">
<div class="grid md:grid-cols-2 gap-5">

<div>
<label>Hora Inicio</label>
<input type="time" name="hora_inicio" class="w-full border rounded px-3 py-2">
</div>

<div>
<label>Hora Fin</label>
<input type="time" name="hora_fin" class="w-full border rounded px-3 py-2">
</div>

<div class="md:col-span-2">
<label>Días</label>
<div class="flex gap-3 flex-wrap mt-2">

<label><input type="checkbox" name="dias[]" value="Lunes"> Lunes</label>
<label><input type="checkbox" name="dias[]" value="Martes"> Martes</label>
<label><input type="checkbox" name="dias[]" value="Miércoles"> Miércoles</label>
<label><input type="checkbox" name="dias[]" value="Jueves"> Jueves</label>
<label><input type="checkbox" name="dias[]" value="Viernes"> Viernes</label>
<label><input type="checkbox" name="dias[]" value="Sábado"> Sábado</label> <!-- 🔥 -->
<label><input type="checkbox" name="dias[]" value="Domingo"> Domingo</label> <!-- 🔥 -->

</div>
</div>

</div>
</div>

<!-- FLEXIBLE -->
<div id="modo-flexible" class="hidden">
<div class="grid md:grid-cols-3 gap-4">

<?php 
$dias = ["lunes","martes","miercoles","jueves","viernes","sabado","domingo"];
foreach($dias as $d): ?>
<div class="bg-white p-4 rounded shadow">
<label class="font-semibold">
<input type="checkbox" value="<?= ucfirst($d) ?>"> <?= ucfirst($d) ?>
</label>

<input type="time" name="<?= $d ?>_inicio" class="w-full border mt-2">
<input type="time" name="<?= $d ?>_fin" class="w-full border mt-2">
</div>
<?php endforeach; ?>

</div>
</div>

</section>

<!-- ================= APODERADO ================= -->
<section class="bg-blue-50 rounded-2xl shadow-md p-8 border">

<h3 class="text-xl font-semibold mb-6">Datos del Apoderado</h3>

<div class="grid md:grid-cols-3 gap-5">

<div>
<label>Nombre</label>
<input name="nombre_apoderado" class="w-full border rounded px-3 py-2">
</div>

<div>
<label>DNI</label>
<input name="dni_apoderado" maxlength="8"
class="w-full border rounded px-3 py-2"
oninput="this.value=this.value.replace(/[^0-9]/g,'')">
</div>

<div>
<label>Correo</label>
<input name="correo_apoderado" class="w-full border rounded px-3 py-2">
</div>

<div>
<label>Teléfono</label>
<input name="telefono_apoderado" maxlength="9"
class="w-full border rounded px-3 py-2"
oninput="this.value=this.value.replace(/[^0-9]/g,'')">
</div>

<div>
<label>Notificar en emergencia</label>
<select name="notificar_emergencia" class="w-full border rounded px-3 py-2">
  <option value="">Seleccione</option>
  <option value="Si">Si</option>
  <option value="No">No</option>
</select>
</div>

</div>
</section>

<div class="flex justify-end gap-3">
<button type="reset" class="px-4 py-2 border rounded">Cancelar</button>
<button type="submit" class="px-5 py-2 bg-teal-600 text-white rounded">Guardar</button>
</div>

</form>
</div>

<!-- TAB GESTIONAR -->
<div id="contenido-gestionar" class="tab-content hidden">
  <div class="bg-white rounded-2xl shadow-md p-8 border">
    <div class="relative mb-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <div>
          <h3 class="text-xl font-semibold">Listado de Practicantes</h3>
          <p class="text-sm text-gray-500">Filtra por nombre o DNI, estado y carrera.</p>
        </div>
        <div class="flex items-center gap-2 relative">
          <button id="btnToggleFiltros" type="button" onclick="toggleFiltrosPracticantes()"
            class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 flex items-center gap-2">
            <i class="fas fa-filter"></i>
            Filtro
          </button>
          <button type="button" onclick="exportarExcelPracticantes()"
            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center gap-2">
            <i class="fas fa-file-excel"></i>
            Excel
          </button>
          <button type="button" onclick="exportarPDFPracticantes()"
            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 flex items-center gap-2">
            <i class="fas fa-file-pdf"></i>
            PDF
          </button>

          <div id="panelFiltrosPracticantes" class="hidden absolute top-full mt-2 right-0 z-[9999] w-80 bg-white border border-gray-200 rounded-xl shadow-2xl p-5">
            <div class="flex justify-between items-center mb-4">
              <div>
                <h4 class="font-semibold text-gray-800">Filtros</h4>
                <p class="text-xs text-gray-500">Aplicar valores para reducir el listado.</p>
              </div>
              <button type="button" onclick="toggleFiltrosPracticantes()"
                class="text-gray-500 hover:text-gray-800">✕</button>
            </div>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-semibold mb-1">Nombre o DNI</label>
                <input id="filtroNombreDni" type="text" placeholder="Buscar..."
                  class="w-full border border-gray-300 rounded px-3 py-2"
                  oninput="aplicarFiltroPracticantes()">
              </div>
              <div>
                <label class="block text-sm font-semibold mb-1">Estado</label>
                <select id="filtroEstadoPracticante" class="w-full border border-gray-300 rounded px-3 py-2"
                  onchange="aplicarFiltroPracticantes()">
                  <option value="Todos">Todos</option>
                  <option value="Activo">Activo</option>
                  <option value="Baja">Baja</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-semibold mb-1">Carrera</label>
                <select id="filtroCarreraPracticante" class="w-full border border-gray-300 rounded px-3 py-2"
                  onchange="aplicarFiltroPracticantes()">
                  <option value="Todos">Todas</option>
                  <?php foreach ($carreras as $c): ?>
                    <option value="<?= htmlspecialchars($c['nombre_carrera']) ?>"><?= htmlspecialchars($c['nombre_carrera']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="flex items-center gap-2">
                <button type="button" onclick="aplicarFiltroPracticantes()"
                  class="flex-1 bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">Aplicar</button>
                <button type="button" onclick="limpiarFiltroPracticantes()"
                  class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">Limpiar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="overflow-auto max-h-[500px] border border-gray-200 rounded">

    <table class="w-full border border-gray-200 rounded-lg">
      <thead class="bg-green-100 text-sm text-left text-gray-700">
        <tr>
          <th class="p-3">Nombre</th>
          <th class="p-3">DNI</th>
          <th class="p-3">Edad</th>
          <th class="p-3">Email</th>
          <th class="p-3">Teléfono</th>
          <th class="p-3">Teléfono Emergencia</th>
          <th class="p-3">Dirección</th>
          <th class="p-3">Carrera</th>
          <th class="p-3">Modalidad</th>
          <th class="p-3">Horario</th>
          <th class="p-3">Estado</th>
          <th class="p-3">Apoderado</th>
          <th class="p-3">DNI Apoderado</th>
          <th class="p-3">Correo Apoderado</th>
          <th class="p-3">Teléfono Apoderado</th>
          <th class="p-3">Notificar</th>
          <th class="p-3">Observación</th>
          <th class="p-3">Registro</th>
          <th class="p-3">Baja</th>
          <th class="p-3">Acciones</th>
        </tr>
      </thead>
      <tbody id="tablaPracticantesBody">
        <?php if (empty($practicantes)): ?>
          <tr>
            <td colspan="19" class="p-6 text-center text-gray-500">No hay practicantes registrados</td>
          </tr>
        <?php else: ?>
          <?php foreach ($practicantes as $p): ?>
            <tr class="border-t hover:bg-gray-50 text-sm" data-estado="<?= $p['fecha_baja'] ? 'Baja' : 'Activo' ?>" data-carrera="<?= htmlspecialchars($p['carrera_nombre'] ?? 'Sin Carrera') ?>">
              <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($p['nombre']) ?></td>
              <td class="p-3"><?= htmlspecialchars($p['dni'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['edad'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['email'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['telefono'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['telefono_emergencia'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['direccion'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['carrera_nombre'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['modalidad_horario'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['horario'] ?? '-') ?></td>
              <td class="p-3"><?= $p['fecha_baja'] ? 'Baja' : 'Activo' ?></td>
              <td class="p-3"><?= htmlspecialchars($p['nombre_apoderado'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['dni_apoderado'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['correo_apoderado'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['telefono_apoderado'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['notificar_emergencia'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['observacion'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['fecha_registro'] ?? '-') ?></td>
              <td class="p-3"><?= htmlspecialchars($p['fecha_baja'] ?? '-') ?></td>
              <td class="p-3 flex gap-2">
                <button type="button" onclick="window.abrirEditarPracticante(<?= $p['id_practicante'] ?>)"
                  class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">Editar</button>
                <?php if(!$p['fecha_baja']): ?>
                <button type="button" onclick="window.darBajaPracticante(<?= $p['id_practicante'] ?>)"
                  class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Baja</button>
                <?php else: ?>
                <button type="button" onclick="window.reactivarPracticante(<?= $p['id_practicante'] ?>)"
                  class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700">Reactivar</button>
                <button type="button" onclick="window.eliminarPracticante(<?= $p['id_practicante'] ?>)"
                  class="bg-orange-600 text-white px-3 py-1 rounded text-xs hover:bg-orange-700">Eliminar</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
function mostrarTab(tab){
  document.querySelectorAll('.tab-content').forEach(el=>el.classList.add('hidden'));
  document.getElementById(`contenido-${tab}`).classList.remove('hidden');
  document.querySelectorAll('#tab-registrar, #tab-gestionar').forEach(btn=>btn.classList.remove('bg-teal-600','text-white'));
  document.getElementById(`tab-${tab}`).classList.add('bg-teal-600','text-white');
}

function cambiarModo(tipo){
  let normal=document.getElementById('modo-normal');
  let flexible=document.getElementById('modo-flexible');
  let btnN=document.getElementById('btnNormal');
  let btnF=document.getElementById('btnFlexible');

  if(tipo==='normal'){
    normal.classList.remove('hidden');
    flexible.classList.add('hidden');
    btnN.classList.add('bg-teal-600','text-white');
    btnF.classList.remove('bg-teal-600','text-white');
  }

  if(tipo==='flexible'){
    flexible.classList.remove('hidden');
    normal.classList.add('hidden');
    btnF.classList.add('bg-teal-600','text-white');
    btnN.classList.remove('bg-teal-600','text-white');
  }
}

function getHorarioValue() {
  const modalidad = document.querySelector('[name="modalidad_horario"]').value;
  if (!modalidad) {
    return '';
  }

  const flexibleMode = !document.getElementById('modo-flexible').classList.contains('hidden');
  let horarioValue = modalidad;

  if (flexibleMode) {
    const entries = [];
    document.querySelectorAll('#modo-flexible input[type="checkbox"]').forEach(checkbox => {
      if (!checkbox.checked) return;
      const day = checkbox.value;
      const card = checkbox.closest('div');
      const inicio = card.querySelector(`[name="${day.toLowerCase()}_inicio"]`).value;
      const fin = card.querySelector(`[name="${day.toLowerCase()}_fin"]`).value;
      if (inicio && fin) {
        entries.push(`${day} ${inicio}-${fin}`);
      }
    });
    if (entries.length) {
      horarioValue += ' | ' + entries.join('; ');
    }
  } else {
    const inicio = document.querySelector('[name="hora_inicio"]').value;
    const fin = document.querySelector('[name="hora_fin"]').value;
    const dias = Array.from(document.querySelectorAll('[name="dias[]"]:checked')).map(c => c.value);
    const parts = [];
    if (inicio && fin) parts.push(`${inicio}-${fin}`);
    if (dias.length) parts.push(dias.join(', '));
    if (parts.length) horarioValue += ' | ' + parts.join(' | ');
  }

  return horarioValue;
}

function actualizarHorarioOculto() {
  document.getElementById('inputHorario').value = getHorarioValue();
}

function validarFormularioPracticante() {
  // Validar nombre
  const nombre = document.querySelector('[name="nombre"]').value.trim();
  if (!nombre) {
    alert('El nombre es requerido');
    return false;
  }

  // Validar DNI
  const dni = document.querySelector('[name="dni"]').value.trim();
  if (!dni || dni.length < 8) {
    alert('DNI requerido (mínimo 8 dígitos)');
    return false;
  }

  // Validar modalidad
  const modalidad = document.querySelector('[name="modalidad_horario"]').value;
  if (!modalidad) {
    alert('Seleccione una modalidad (Presencial, Virtual o Semipresencial)');
    return false;
  }

  // Validar carrera
  const carrera = document.querySelector('[name="id_carrera"]').value;
  if (!carrera) {
    alert('Seleccione una carrera');
    return false;
  }

  // Validar horario según el modo
  const flexibleMode = !document.getElementById('modo-flexible').classList.contains('hidden');
  
  if (flexibleMode) {
    // Modo flexible: verificar que al menos un día tenga horario
    const checkboxes = document.querySelectorAll('#modo-flexible input[type="checkbox"]');
    let tieneHorario = false;
    
    for (let checkbox of checkboxes) {
      if (checkbox.checked) {
        const day = checkbox.value.toLowerCase();
        const inicio = checkbox.closest('div').querySelector(`[name="${day}_inicio"]`).value;
        const fin = checkbox.closest('div').querySelector(`[name="${day}_fin"]`).value;
        if (inicio && fin) {
          tieneHorario = true;
          break;
        }
      }
    }
    
    if (!tieneHorario) {
      alert('En modo flexible, debe seleccionar al menos un día con horario de inicio y fin');
      return false;
    }
  } else {
    // Modo normal: validar horas y días
    const horaInicio = document.querySelector('[name="hora_inicio"]').value;
    const horaFin = document.querySelector('[name="hora_fin"]').value;
    
    if (!horaInicio || !horaFin) {
      alert('Debe ingresar hora de inicio y hora de fin');
      return false;
    }

    // Validar que al menos un día esté seleccionado
    const diasSeleccionados = document.querySelectorAll('[name="dias[]"]:checked');
    if (diasSeleccionados.length === 0) {
      alert('Debe seleccionar al menos un día de asistencia');
      return false;
    }
  }

  // Validar teléfono si está lleno
  const telefono = document.querySelector('[name="telefono"]').value.trim();
  if (telefono && telefono.length < 6) {
    alert('Teléfono inválido (mínimo 6 dígitos)');
    return false;
  }

  // Validar email si está lleno
  const email = document.querySelector('[name="email"]').value.trim();
  if (email && !email.includes('@')) {
    alert('Email inválido');
    return false;
  }

  return true;
}

async function enviarFormularioPracticante(event) {
  event.preventDefault();
  
  // Validar antes de enviar
  if (!validarFormularioPracticante()) {
    return;
  }

  actualizarHorarioOculto();
  const form = event.target;
  const formData = new FormData(form);

  // Determinar la ruta correcta dinámicamente
const processUrl = '../process/process_practicantes.php';

  try {
    const response = await fetch(processUrl, { method: 'POST', body: formData });
    const responseText = await response.text();
    
    console.log('Respuesta del servidor:', responseText);
    
    let data;
    try {
      data = JSON.parse(responseText);
    } catch (parseError) {
      console.error('Error al parsear JSON:', parseError);
      console.error('Respuesta recibida:', responseText);
      alert('Error del servidor: ' + responseText);
      return;
    }

    if (!data.success) {
      alert(data.message || 'Error al registrar practicante');
      return;
    }

    alert(data.message || 'Practicante registrado exitosamente');
    form.reset();
    cambiarModo('normal');
    window.location.href = window.location.pathname + '?tab=gestionar';
  } catch (error) {
    console.error('Error completo:', error);
    alert('Error al registrar practicante: ' + error.message);
  }
}

function toggleFiltrosPracticantes() {
  const panel = document.getElementById('panelFiltrosPracticantes');
  panel.classList.toggle('hidden');
}

function aplicarFiltroPracticantes() {
  const texto = document.getElementById('filtroNombreDni').value.toLowerCase().trim();
  const estado = document.getElementById('filtroEstadoPracticante').value;
  const carrera = document.getElementById('filtroCarreraPracticante').value;
  const filas = document.querySelectorAll('#tablaPracticantesBody tr[data-estado]');

  filas.forEach(fila => {
    const nombre = fila.children[0].innerText.toLowerCase();
    const dni = fila.children[1].innerText.toLowerCase();
    const filaEstado = fila.getAttribute('data-estado');
    const filaCarrera = fila.getAttribute('data-carrera');

    const matchesTexto = !texto || nombre.includes(texto) || dni.includes(texto);
    const matchesEstado = estado === 'Todos' || filaEstado === estado;
    const matchesCarrera = carrera === 'Todos' || filaCarrera === carrera;

    fila.style.display = matchesTexto && matchesEstado && matchesCarrera ? '' : 'none';
  });
}

function limpiarFiltroPracticantes() {
  document.getElementById('filtroNombreDni').value = '';
  document.getElementById('filtroEstadoPracticante').value = 'Todos';
  document.getElementById('filtroCarreraPracticante').value = 'Todos';
  aplicarFiltroPracticantes();
}

// Cerrar panel de filtros al hacer click fuera
document.addEventListener('click', function(e){
  const panel = document.getElementById('panelFiltrosPracticantes');
  const botonFiltro = e.target.closest('[onclick="toggleFiltrosPracticantes()"]');

  if (panel && !panel.contains(e.target) && !botonFiltro) {
    panel.classList.add('hidden');
  }
});

// Datos de practicantes para exportación
const practicantesData = <?= json_encode($practicantes, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;

function getVisiblePracticantes() {
  const texto = document.getElementById('filtroNombreDni').value.toLowerCase().trim();
  const estado = document.getElementById('filtroEstadoPracticante').value;
  const carrera = document.getElementById('filtroCarreraPracticante').value;

  return practicantesData.filter(p => {
    const nombre = (p.nombre ?? '').toLowerCase();
    const dni = (p.dni ?? '').toLowerCase();
    const pEstado = p.fecha_baja ? 'Baja' : 'Activo';
    const pCarrera = (p.carrera_nombre ?? '').toLowerCase();

    const matchesTexto = !texto || nombre.includes(texto) || dni.includes(texto);
    const matchesEstado = estado === 'Todos' || pEstado === estado;
    const matchesCarrera = carrera === 'Todos' || pCarrera === carrera;

    return matchesTexto && matchesEstado && matchesCarrera;
  });
}

function exportarExcelPracticantes(){
  const visiblePracticantes = getVisiblePracticantes();

  let contenido = `
    <meta charset="UTF-8">
    <table border="1">
    <tr style="background:#9b00ff; color:white;">
        <th>Nombre</th>
        <th>DNI</th>
        <th>Edad</th>
        <th>Email</th>
        <th>Teléfono</th>
        <th>Teléfono Emergencia</th>
        <th>Dirección</th>
        <th>Carrera</th>
        <th>Modalidad</th>
        <th>Horario</th>
        <th>Estado</th>
        <th>Apoderado</th>
        <th>DNI Apoderado</th>
        <th>Correo Apoderado</th>
        <th>Teléfono Apoderado</th>
        <th>Notificar Emergencia</th>
        <th>Observación</th>
        <th>Fecha Registro</th>
        <th>Fecha Baja</th>
    </tr>
    `;

  visiblePracticantes.forEach(p => {
    const estado = p.fecha_baja ? 'Baja' : 'Activo';
    contenido += `
    <tr>
        <td>${p.nombre ?? ''}</td>
        <td>${p.dni ?? ''}</td>
        <td>${p.edad ?? ''}</td>
        <td>${p.email ?? ''}</td>
        <td>${p.telefono ?? ''}</td>
        <td>${p.telefono_emergencia ?? ''}</td>
        <td>${p.direccion ?? ''}</td>
        <td>${p.carrera_nombre ?? ''}</td>
        <td>${p.modalidad_horario ?? ''}</td>
        <td>${p.horario ?? ''}</td>
        <td>${estado}</td>
        <td>${p.nombre_apoderado ?? ''}</td>
        <td>${p.dni_apoderado ?? ''}</td>
        <td>${p.correo_apoderado ?? ''}</td>
        <td>${p.telefono_apoderado ?? ''}</td>
        <td>${p.notificar_emergencia ?? ''}</td>
        <td>${p.observacion ?? ''}</td>
        <td>${p.fecha_registro ?? ''}</td>
        <td>${p.fecha_baja ?? ''}</td>
    </tr>`;
  });

  contenido += "</table>";

  const blob = new Blob(["\ufeff", contenido], {
    type: "application/vnd.ms-excel;charset=utf-8;"
  });

  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = "reporte_practicantes.xls";
  a.click();
}

function exportarPDFPracticantes(){
  const visiblePracticantes = getVisiblePracticantes();
  const fecha = new Date().toLocaleDateString();

  let rowsHtml = visiblePracticantes.map(p => {
    const estado = p.fecha_baja ? 'Baja' : 'Activo';
    return `
    <tr>
        <td>${p.nombre ?? ''}</td>
        <td>${p.dni ?? ''}</td>
        <td>${p.edad ?? ''}</td>
        <td>${p.email ?? ''}</td>
        <td>${p.telefono ?? ''}</td>
        <td>${p.telefono_emergencia ?? ''}</td>
        <td>${p.direccion ?? ''}</td>
        <td>${p.carrera_nombre ?? ''}</td>
        <td>${p.modalidad_horario ?? ''}</td>
        <td>${p.horario ?? ''}</td>
        <td>${estado}</td>
        <td>${p.nombre_apoderado ?? ''}</td>
        <td>${p.dni_apoderado ?? ''}</td>
        <td>${p.correo_apoderado ?? ''}</td>
        <td>${p.telefono_apoderado ?? ''}</td>
        <td>${p.notificar_emergencia ?? ''}</td>
        <td>${p.observacion ?? ''}</td>
        <td>${p.fecha_registro ?? ''}</td>
        <td>${p.fecha_baja ?? ''}</td>
    </tr>
    `;
  }).join('');

  const html = `
    <div style="font-family: Arial; padding:20px;">

        <!-- HEADER -->
        <div style="display:flex; align-items:center; gap:15px; border-bottom:3px solid #9b00ff; padding-bottom:10px;">
            <img src="../img/logo-beatcell.png" width="70">

            <div>
                <h2 style="margin:0; color:#9b00ff;">BEATCELL</h2>
                <small style="color:#555;">Sistema de Gestión de Practicantes</small>
            </div>
        </div>

        <p style="margin-top:10px;"><strong>Fecha:</strong> ${fecha}</p>

        <!-- TABLA -->
        <table border="1"
               style="width:100%;
               border-collapse: collapse;
               font-size:10px;
               margin-top:10px;">

            <thead>
                <tr style="background:#0d1b2a; color:white;">
                    <th>Nombre</th>
                    <th>DNI</th>
                    <th>Edad</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Tel. Emerg.</th>
                    <th>Dirección</th>
                    <th>Carrera</th>
                    <th>Modalidad</th>
                    <th>Horario</th>
                    <th>Estado</th>
                    <th>Apoderado</th>
                    <th>DNI Apod.</th>
                    <th>Correo Apod.</th>
                    <th>Tel. Apod.</th>
                    <th>Notif.</th>
                    <th>Observación</th>
                    <th>Registro</th>
                    <th>Baja</th>
                </tr>
            </thead>

            <tbody>
                ${rowsHtml}
            </tbody>

        </table>

        <!-- FOOTER -->
        <p style="margin-top:15px; font-size:11px; color:#9b00ff;">
            Reporte generado automáticamente por BEATCELL
        </p>

    </div>
    `;

  html2pdf().set({
    margin: 0.3,
    filename: 'reporte_practicantes.pdf',
    html2canvas: { scale: 2 },
    jsPDF: { orientation: 'landscape' }
  }).from(html).save();
}

document.addEventListener("DOMContentLoaded",()=>{
  const params = new URLSearchParams(window.location.search);
  const tab = params.get('tab') || 'registrar';
  mostrarTab(tab);

  const form = document.getElementById('formRegistrarPracticante');
  form.addEventListener('submit', enviarFormularioPracticante);

  // Evitar envío con Enter en inputs normales, sólo submit con botón Guardar
  form.addEventListener('keydown', function(event) {
    const target = event.target;
    if (event.key === 'Enter' && target.tagName.toLowerCase() === 'input' && target.type !== 'submit' && target.type !== 'button') {
      event.preventDefault();
    }
  });
});

window.abrirEditarPracticante = function(id){
    let modal = document.getElementById('modalEditarPracticante');
    let contenedor = document.getElementById('contenidoEditarPracticante');

    if(!modal || !contenedor){
        alert("Modal no encontrado");
        return;
    }

    modal.classList.remove('hidden');
    contenedor.innerHTML = '<div class="text-center py-10">Cargando...</div>';

    fetch(`editar_practicante.php?id=${id}`)
        .then(res => res.text())
        .then(html => {
            contenedor.innerHTML = html;
            setTimeout(inicializarEditarPracticante, 0);
        })
        .catch(() => {
            contenedor.innerHTML = '<div>Error al cargar</div>';
        });
}

window.cerrarModalEditarPracticante = function(){
    let modal = document.getElementById('modalEditarPracticante');
    let contenedor = document.getElementById('contenidoEditarPracticante');
    if(modal){
        modal.classList.add('hidden');
    }
    if(contenedor){
        contenedor.innerHTML = '';
    }
}

window.darBajaPracticante = function(id){
    if(confirm('¿Está seguro que desea dar de baja este practicante?')){
        const formData = new FormData();
        formData.append('action', 'dar_baja');
        formData.append('id_practicante', id);

        const processUrl = '../process/process_practicantes.php';

        fetch(processUrl, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    alert('Practicante dado de baja exitosamente');
                    location.reload();
                } else {
                    alert(data.message || 'Error al dar de baja');
                }
            })
            .catch(() => alert('Error al procesar solicitud'));
    }
}

window.reactivarPracticante = function(id){
    if(confirm('¿Está seguro que desea reactivar este practicante?')){
        const formData = new FormData();
        formData.append('action', 'reactivar');
        formData.append('id_practicante', id);

        const processUrl = '../process/process_practicantes.php';

        fetch(processUrl, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    alert('Practicante reactivado exitosamente');
                    location.reload();
                } else {
                    alert(data.message || 'Error al reactivar');
                }
            })
            .catch(() => alert('Error al procesar solicitud'));
    }
}

window.eliminarPracticante = function(id){
    if(confirm('¿Está seguro que desea eliminar este practicante? Esta acción no se puede deshacer.')){
        const formData = new FormData();
        formData.append('action', 'eliminar');
        formData.append('id_practicante', id);

        const processUrl = '../process/process_practicantes.php';

        fetch(processUrl, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    alert('Practicante eliminado exitosamente');
                    location.reload();
                } else {
                    alert(data.message || 'Error al eliminar');
                }
            })
            .catch(() => alert('Error al procesar solicitud'));
    }
}

function mostrarModoEditar(tipo) {
    const normal = document.getElementById('modo-normal-editar');
    const flexible = document.getElementById('modo-flexible-editar');
    const btnNormal = document.getElementById('btnNormalEditar');
    const btnFlexible = document.getElementById('btnFlexibleEditar');

    if (!normal || !flexible || !btnNormal || !btnFlexible) {
        return;
    }

    if (tipo === 'normal') {
        normal.classList.remove('hidden');
        flexible.classList.add('hidden');
        btnNormal.classList.add('bg-teal-600', 'text-white');
        btnFlexible.classList.remove('bg-teal-600', 'text-white');
    } else {
        flexible.classList.remove('hidden');
        normal.classList.add('hidden');
        btnFlexible.classList.add('bg-teal-600', 'text-white');
        btnNormal.classList.remove('bg-teal-600', 'text-white');
    }
}

function normalizarNombreDia(dia) {
    return dia.toLowerCase()
        .replace(/á/g, 'a')
        .replace(/é/g, 'e')
        .replace(/í/g, 'i')
        .replace(/ó/g, 'o')
        .replace(/ú/g, 'u')
        .replace(/ñ/g, 'n')
        .replace(/\s+/g, '_');
}

function parseHorarioEditar() {
    const horarioInput = document.getElementById('inputHorarioEditar');
    if (!horarioInput) return;

    const horarioValue = horarioInput.value.trim();
    if (!horarioValue) return;

    const parts = horarioValue.split('|').map(p => p.trim()).filter(Boolean);
    const modalidadSelect = document.querySelector('#contenidoEditarPracticante [name="modalidad_horario"]');
    if (modalidadSelect && parts.length) {
        modalidadSelect.value = parts[0];
    }

    const horasInicio = document.getElementById('horaInicioEditar');
    const horasFin = document.getElementById('horaFinEditar');
    const diasCheckboxes = document.querySelectorAll('#contenidoEditarPracticante [name="dias[]"]');

    const flexibleSection = document.getElementById('modo-flexible-editar');
    const normalSection = document.getElementById('modo-normal-editar');

    const rest = parts.slice(1).join(' | ').trim();
    if (!rest) {
        mostrarModoEditar('normal');
        return;
    }

    const flexibleSegments = rest.split(';').map(s => s.trim()).filter(Boolean);
    const allFlexibleEntries = flexibleSegments.every(seg => /^(.+?)\s+[0-9]{2}:[0-9]{2}-[0-9]{2}:[0-9]{2}$/i.test(seg));
    const isFlexible = flexibleSegments.length > 1 || allFlexibleEntries && !/^[0-9]{2}:[0-9]{2}-[0-9]{2}:[0-9]{2}$/.test(flexibleSegments[0]);

    if (isFlexible) {
        mostrarModoEditar('flexible');
        flexibleSegments.forEach(segment => {
            const trimmed = segment.trim();
            const match = trimmed.match(/^(.+?)\s+([0-9]{2}:[0-9]{2})-([0-9]{2}:[0-9]{2})$/i);
            if (!match) return;
            const dayName = match[1].trim();
            const inicio = match[2];
            const fin = match[3];
            const key = normalizarNombreDia(dayName);
            const checkbox = document.getElementById(`flex_${key}_editar`);
            const inicioInput = document.getElementById(`inicio_${key}_editar`);
            const finInput = document.getElementById(`fin_${key}_editar`);
            if (checkbox) checkbox.checked = true;
            if (inicioInput) inicioInput.value = inicio;
            if (finInput) finInput.value = fin;
        });
    } else {
        mostrarModoEditar('normal');
        if (parts.length > 0 && horasInicio && horasFin) {
            const horaPart = parts[1] || '';
            const match = horaPart.match(/^([0-9]{2}:[0-9]{2})-([0-9]{2}:[0-9]{2})$/);
            if (match) {
                horasInicio.value = match[1];
                horasFin.value = match[2];
            }
        }
        if (parts.length > 1) {
            const diasPart = parts[2] || parts[1];
            const dias = diasPart.split(',').map(d => d.trim());
            diasCheckboxes.forEach(cb => {
                cb.checked = dias.includes(cb.value);
            });
        }
    }
}

function actualizarHorarioOcultoEditar() {
    const horarioInput = document.getElementById('inputHorarioEditar');
    if (!horarioInput) return;

    const modalidad = document.querySelector('#contenidoEditarPracticante [name="modalidad_horario"]')?.value;
    if (!modalidad) {
        horarioInput.value = '';
        return;
    }

    let horarioValue = modalidad;
    const flexibleActive = !document.getElementById('modo-flexible-editar')?.classList.contains('hidden');

    if (flexibleActive) {
        const entries = [];
        const checkboxes = document.querySelectorAll('#modo-flexible-editar input[type="checkbox"]');
        checkboxes.forEach(cb => {
            if (!cb.checked) return;
            const key = normalizarNombreDia(cb.value);
            const inicio = document.getElementById(`inicio_${key}_editar`)?.value;
            const fin = document.getElementById(`fin_${key}_editar`)?.value;
            if (inicio && fin) {
                entries.push(`${cb.value} ${inicio}-${fin}`);
            }
        });
        if (entries.length) {
            horarioValue += ' | ' + entries.join('; ');
        }
    } else {
        const inicio = document.querySelector('#contenidoEditarPracticante [name="hora_inicio"]')?.value;
        const fin = document.querySelector('#contenidoEditarPracticante [name="hora_fin"]')?.value;
        const dias = Array.from(document.querySelectorAll('#contenidoEditarPracticante [name="dias[]"]:checked')).map(cb => cb.value);
        const parts = [];
        if (inicio && fin) parts.push(`${inicio}-${fin}`);
        if (dias.length) parts.push(dias.join(', '));
        if (parts.length) {
            horarioValue += ' | ' + parts.join(' | ');
        }
    }

    horarioInput.value = horarioValue;
}

function validarFormularioEditar() {
    const nombre = document.querySelector('#contenidoEditarPracticante [name="nombre"]')?.value.trim();
    if (!nombre) {
        alert('El nombre es requerido');
        return false;
    }

    const dni = document.querySelector('#contenidoEditarPracticante [name="dni"]')?.value.trim();
    if (!dni || dni.length < 8) {
        alert('DNI requerido (mínimo 8 dígitos)');
        return false;
    }

    const modalidad = document.querySelector('#contenidoEditarPracticante [name="modalidad_horario"]')?.value;
    if (!modalidad) {
        alert('Seleccione una modalidad (Presencial, Virtual o Semipresencial)');
        return false;
    }

    const carrera = document.querySelector('#contenidoEditarPracticante [name="id_carrera"]')?.value;
    if (!carrera) {
        alert('Seleccione una carrera');
        return false;
    }

    const flexibleMode = !document.getElementById('modo-flexible-editar')?.classList.contains('hidden');
    if (flexibleMode) {
        const checkboxes = document.querySelectorAll('#modo-flexible-editar input[type="checkbox"]');
        let tieneHorario = false;

        checkboxes.forEach(checkbox => {
            if (!checkbox.checked) return;
            const key = normalizarNombreDia(checkbox.value);
            const inicio = document.getElementById(`inicio_${key}_editar`)?.value;
            const fin = document.getElementById(`fin_${key}_editar`)?.value;
            if (inicio && fin) {
                tieneHorario = true;
            }
        });

        if (!tieneHorario) {
            alert('En modo flexible, debe seleccionar al menos un día con horario de inicio y fin');
            return false;
        }
    } else {
        const horaInicio = document.querySelector('#contenidoEditarPracticante [name="hora_inicio"]')?.value;
        const horaFin = document.querySelector('#contenidoEditarPracticante [name="hora_fin"]')?.value;
        const diasSeleccionados = document.querySelectorAll('#contenidoEditarPracticante [name="dias[]"]:checked');

        if (!horaInicio || !horaFin) {
            alert('Debe ingresar hora de inicio y hora de fin');
            return false;
        }

        if (diasSeleccionados.length === 0) {
            alert('Debe seleccionar al menos un día de asistencia');
            return false;
        }
    }

    const telefono = document.querySelector('#contenidoEditarPracticante [name="telefono"]')?.value.trim();
    if (telefono && telefono.length < 6) {
        alert('Teléfono inválido (mínimo 6 dígitos)');
        return false;
    }

    const email = document.querySelector('#contenidoEditarPracticante [name="email"]')?.value.trim();
    if (email && !email.includes('@')) {
        alert('Email inválido');
        return false;
    }

    return true;
}

function inicializarEditarPracticante() {
    const form = document.getElementById('formEditarPracticante');
    if (!form) return;

    document.getElementById('btnNormalEditar')?.addEventListener('click', () => mostrarModoEditar('normal'));
    document.getElementById('btnFlexibleEditar')?.addEventListener('click', () => mostrarModoEditar('flexible'));

    form.addEventListener('submit', async function(e){
        e.preventDefault();
        if (!validarFormularioEditar()) {
            return;
        }

        actualizarHorarioOcultoEditar();
        const formData = new FormData(form);
        const processUrl = '../process/process_practicantes.php';

        try {
            const response = await fetch(processUrl, { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                alert('Practicante actualizado exitosamente');
                window.cerrarModalEditarPracticante();
                location.reload();
            } else {
                alert(data.message || 'Error al actualizar');
            }
        } catch (error) {
            console.error(error);
            alert('Error al procesar solicitud');
        }
    });

    parseHorarioEditar();
}

window.addEventListener("click", function(e){
    let modal = document.getElementById("modalEditarPracticante");
    if(modal && e.target === modal){
        window.cerrarModalEditarPracticante();
    }
});
</script>

<!-- MODAL EDITAR PRACTICANTE -->
<div id="modalEditarPracticante"
     class="fixed inset-0
            bg-black bg-opacity-70
            flex items-center justify-center
            hidden
            z-50">

    <div class="
        bg-gray-900
        text-white
        rounded-xl
        shadow-2xl
        w-full
        max-w-4xl
        p-6
        relative
        border border-teal-500
        max-h-[90vh]
        overflow-y-auto
    ">

        <!-- BOTON CERRAR -->
        <button
            onclick="window.cerrarModalEditarPracticante()"
            class="
                absolute
                top-3
                right-3
                text-gray-400
                hover:text-white
                text-xl
            ">
            ✕
        </button>

        <!-- TITULO -->
        <h2 class="
            text-2xl
            font-bold
            mb-4
            text-teal-400
        ">
            Editar Practicante
        </h2>

        <!-- CONTENIDO DINAMICO -->
        <div id="contenidoEditarPracticante">
            <div class="text-center py-10">
                Cargando...
            </div>
        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>