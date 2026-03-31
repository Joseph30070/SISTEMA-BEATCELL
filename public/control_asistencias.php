<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR']);

$title  = "Asistencia de Estudiantes";
$active = "asistencia";

$pdo = require __DIR__ . '/../config/db.php';

// Obtener cursos
$stmt = $pdo->query("SELECT id_curso, nombre_curso FROM cursos ORDER BY nombre_curso ASC");
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =========================
// HISTORIAL
// =========================
$stmt = $pdo->query("
SELECT 
    asl.id_asistencia,
    a.nombre,
    asl.fecha,
    asl.hora_entrada,
    asl.hora_salida
FROM asistencias asl
INNER JOIN alumnos a ON a.id_alumno = asl.id_alumno
ORDER BY asl.fecha DESC
LIMIT 100
");
$historial = $stmt->fetchAll();

ob_start();
?>

<style>
  .card{
    background:white;
    border-radius:12px;
    padding:25px;
    margin-bottom:25px;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
  }

  .grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
  }

  .stat{
    text-align:center;
  }

  .stat div{
    font-size:26px;
    font-weight:bold;
    color:#0f766e;
  }

  table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
    border-radius:10px;
    overflow:hidden;
  }

  thead{
    background:#0f766e;
    color:white;
  }

  th{
    padding:12px;
    text-align:left;
    font-weight:600;
    font-size:14px;
  }

  td{
    padding:12px;
    border-bottom:1px solid #e5e7eb;
    font-size:14px;
  }

  tbody tr:hover{
    background:#f9fafb;
    transition:0.2s;
  }

  .center{
    text-align:center;
  }

  .badge{
    background:#2563eb;
    color:white;
    padding:4px 10px;
    border-radius:12px;
    font-size:12px;
  }

  .text-gray-600{ color:#666; }
  .text-sm{ font-size:0.85rem; }

  .hidden{display:none;}
</style>

<h2 class="text-3xl font-bold mb-2">Control de Asistencia</h2>
<p class="text-gray-600 mb-6">Vista basada en cursos → grupos → alumnos</p>

<div class="card">
  <h3 class="font-semibold mb-3">📅 Horarios de Hoy</h3>
  <div id="horariosHoy" class="text-sm text-gray-600">
    Cargando horarios...
  </div>
</div>

<!-- TABS -->
<div class="flex border-b mb-6">

    <button id="tab-asistencia"
        class="tab-btn px-4 py-2 font-semibold bg-teal-600 text-white"
        onclick="mostrarTab('asistencia')">
        Registrar Asistencia
    </button>

    <button id="tab-historial"
        class="tab-btn px-4 py-2 font-semibold text-gray-600 hover:bg-gray-100"
        onclick="mostrarTab('historial')">
        Historial
    </button>

</div>

<!-- ========================= -->
<!-- TAB ASISTENCIA -->
<!-- ========================= -->
<div id="contenido-asistencia" class="tab-content">

<!-- SELECTORES -->
<div class="card">
  <h3 class="font-semibold mb-4">Seleccionar Clase</h3>

  <div class="grid">
    <div>
      <label>Fecha</label>
      <input type="date" id="fecha" class="border px-3 py-2 rounded w-full" value="<?= date('Y-m-d') ?>">
    </div>

    <div>
      <label>Curso</label>
      <select id="curso" class="border px-3 py-2 rounded w-full" onchange="cargarGrupos()">
        <option value="">-- Seleccione Curso --</option>
        <?php foreach($cursos as $c): ?>
          <option value="<?= $c['id_curso'] ?>">
            <?= htmlspecialchars($c['nombre_curso']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label>Grupo</label>
      <select id="grupo" class="border px-3 py-2 rounded w-full" onchange="cargarAlumnos()">
        <option value="">-- Primero seleccione curso --</option>
      </select>
    </div>
  </div>

  <div id="infoGrupo" class="mt-4 text-sm text-gray-600"></div>
</div>

<!-- STATS -->
<div class="grid">
  <div class="card stat">Presentes<div id="presentes">0</div></div>
  <div class="card stat">Ausentes<div id="ausentes">0</div></div>
  <div class="card stat">Porcentaje<div id="porcentaje">0%</div></div>
</div>

<!-- TABLA -->
<div class="card">
  <h3 class="font-semibold mb-4">Alumnos del Grupo</h3>

  <label>
    <input type="checkbox" id="marcarTodos" onchange="marcarTodos()"> Marcar todos como presentes
  </label>

  <table class="shadow-sm">

    <thead>
      <tr>
        <th>#</th>
        <th>Alumno</th>
        <th>DNI</th>
        <th>Teléfono</th>
        <th class="center">Presente</th>
        <th class="center">Salida</th>
      </tr>
    </thead>

    <tbody id="tabla"></tbody>

  </table>

  <div class="text-right mt-4">
    <button class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700" onclick="guardarAsistencia()">
      Guardar Asistencia
    </button>
  </div>
</div>

</div>

<!-- ========================= -->
<!-- TAB HISTORIAL -->
<!-- ========================= -->
<div id="contenido-historial" class="tab-content hidden">

<div class="card">
  <h3 class="font-semibold mb-4">Historial de Asistencia</h3>

  <table class="shadow-sm">

    <thead>
      <tr>
        <th>Alumno</th>
        <th>Fecha</th>
        <th>Entrada</th>
        <th>Salida</th>
      </tr>
    </thead>

    <tbody>

    <?php if($historial): ?>
      <?php foreach($historial as $h): ?>
      <tr>
        <td>
          <strong style="color:#1f2937;">
            <?= htmlspecialchars($h['nombre']) ?>
          </strong>
        </td>

        <td><?= $h['fecha'] ?></td>

        <td>
          <span style="color:#16a34a; font-weight:600;">
            <?= substr($h['hora_entrada'], 0, 5) ?>
          </span>
        </td>

        <td>
          <?php if($h['hora_salida']): ?>
            <span style="color:#2563eb;">
              <?= substr($h['hora_salida'], 0, 5) ?>
            </span>
          <?php else: ?>
            <span style="color:#9ca3af;">-</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>

    <?php else: ?>
      <tr>
        <td colspan="4" class="center">No hay registros</td>
      </tr>
    <?php endif; ?>

    </tbody>

  </table>

</div>

</div>

<script>

// =========================
// TABS
// =========================
function mostrarTab(tab) {
  document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
  document.querySelectorAll('.tab-btn').forEach(b => {
    b.classList.remove('bg-teal-600', 'text-white');
    b.classList.add('text-gray-600');
  });

  document.getElementById(`contenido-${tab}`).classList.remove('hidden');
  document.getElementById(`tab-${tab}`).classList.add('bg-teal-600', 'text-white');
  document.getElementById(`tab-${tab}`).classList.remove('text-gray-600');
}

// =========================
// VARIABLES
// =========================
let alumnosData = [];
let gruposData = [];

// =========================
// CARGAR GRUPOS
// =========================
function cargarGrupos() {
  let cursoId = document.getElementById('curso').value;
  let selectGrupo = document.getElementById('grupo');

  selectGrupo.innerHTML = '<option value="">-- Cargando... --</option>';
  alumnosData = [];
  document.getElementById('tabla').innerHTML = '';

  if (!cursoId) {
    selectGrupo.innerHTML = '<option value="">-- Primero seleccione curso --</option>';
    return;
  }

  fetch(`../process/get_grupos.php?id_curso=${cursoId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        gruposData = data.grupos;
        selectGrupo.innerHTML = '<option value="">-- Seleccione Grupo --</option>';

        data.grupos.forEach(g => {
          let op = document.createElement('option');
          op.value = g.id_grupo;
          op.textContent = g.nombre_grupo;
          op.dataset.dias = g.dias;
          op.dataset.hora = `${g.hora_inicio.substring(0,5)} - ${g.hora_fin.substring(0,5)}`;
          selectGrupo.appendChild(op);
        });
      }
    });
}

// =========================
// CARGAR ALUMNOS
// =========================
function cargarAlumnos() {
  let grupoId = document.getElementById('grupo').value;

  if (!grupoId) return;

  let grupo = gruposData.find(g => g.id_grupo == grupoId);
  if (grupo) {
    document.getElementById('infoGrupo').innerHTML =
      `<span class="badge">${grupo.nombre_grupo}</span> | ${grupo.dias} | ${grupo.hora_inicio.substring(0,5)} - ${grupo.hora_fin.substring(0,5)}`;
  }

  fetch(`../process/get_alumnos.php?id_grupo=${grupoId}`)
    .then(res => res.json())
    .then(data => {
      alumnosData = data.alumnos;
      renderTabla();
    });
}

// =========================
// RENDER TABLA
// =========================
function renderTabla() {
  let tabla = document.getElementById('tabla');
    tabla.innerHTML = '';

    alumnosData.forEach((a, i) => {
      tabla.innerHTML += `
      <tr>
        <td>${i+1}</td>
        
        <td>
          <strong style="color:#1f2937;">${a.nombre}</strong><br>
          <span class="text-gray-600 text-sm">
            Contacto: ${a.contacto_pago}
          </span>
        </td>

        <td>${a.dni}</td>

        <td>${a.telefono || '-'}</td>

        <td class="center">
          <input type="checkbox"
            class="presente"
            data-id="${a.id_alumno}"
            style="accent-color:#16a34a; transform:scale(1.2);"
            onchange="toggle(${a.id_alumno}); actualizarStats()">
        </td>

        <td class="center">
          <input type="checkbox"
            class="ausente"
            data-id="${a.id_alumno}"
            style="accent-color:#dc2626; transform:scale(1.2);"
            onchange="toggle(${a.id_alumno}); actualizarStats()">
        </td>

        <!-- ✅ ESTADO (YA TENÍAS) -->
        <td>
          ${a.estado_asistencia === 'completo'
            ? '<span style="color:green;font-weight:bold;">✔ Completo</span>'
            : a.estado_asistencia === 'entrada'
            ? '<span style="color:orange;">⏳ En clase</span>'
            : '<span style="color:red;">✖ Sin marcar</span>'
          }
        </td>

        <!-- 🔥 NUEVO: BOTÓN SALIDA -->
        <td class="center">
          <button 
            onclick="registrarSalidaAlumno(${a.id_alumno})"
            style="background:#2563eb;color:white;padding:5px 10px;border-radius:6px;font-size:12px;">
            Salida
          </button>
        </td>

      </tr>`;
    });

    actualizarStats();
}

// =========================
// TOGGLE
// =========================
function toggle(id) {
  let p = document.querySelector(`.presente[data-id="${id}"]`);
  let a = document.querySelector(`.ausente[data-id="${id}"]`);

  if (p.checked) a.checked = false;
  if (a.checked) p.checked = false;
}

// =========================
// STATS
// =========================
function actualizarStats() {
  let p = document.querySelectorAll('.presente:checked').length;
  let t = alumnosData.length;
  let a = t - p;
  let por = t ? Math.round((p/t)*100) : 0;

  document.getElementById('presentes').textContent = p;
  document.getElementById('ausentes').textContent = a;
  document.getElementById('porcentaje').textContent = por+'%';
}

// =========================
// MARCAR TODOS
// =========================
function marcarTodos() {
  let c = document.getElementById('marcarTodos').checked;
  document.querySelectorAll('.presente').forEach(cb => {
    cb.checked = c;
    cb.closest('tr').querySelector('.ausente').checked = false;
  });
  actualizarStats();
}

// =========================
// GUARDAR
// =========================

function guardarAsistencia() {

  if(!validarHorarioSeleccionado()) return;

  let fecha = document.getElementById('fecha').value;
  let grupo = document.getElementById('grupo').value;

  if (!fecha || !grupo) {
    alert('Seleccione fecha y grupo');
    return;
  }

  let asistencias = [];

  alumnosData.forEach(a => {

    let presente = document.querySelector(`.presente[data-id="${a.id_alumno}"]`).checked;

    if (presente) {

      let horaActual = new Date().toTimeString().slice(0,5);

      asistencias.push({
        id_alumno: a.id_alumno,
        fecha: fecha,
        hora_entrada: horaActual
      });

    }

  });

  if (asistencias.length === 0) {
    alert('Marque al menos un alumno como presente');
    return;
  }

  fetch('../process/process_asistencia.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ asistencias })
  })
  .then(res => res.json())
  .then(data => {

    if (data.success) {
      alert('✓ Asistencia guardada correctamente');

      // ✅ AQUÍ va la limpieza
      document.getElementById('marcarTodos').checked = false;
      cargarAlumnos();

    } else {
      alert('Error: ' + data.error);
    }

  })
  .catch(err => {
    console.error(err);
    alert('Error al guardar asistencia');
  });

}

// =========================
// REGISTRAR SALIDA
// =========================


function registrarSalida() {

  if(!validarHorarioSeleccionado()) return;

  let fecha = document.getElementById('fecha').value;
  let grupo = document.getElementById('grupo').value;

  if (!fecha || !grupo) {
    alert('Seleccione fecha y grupo');
    return;
  }

  let asistencias = [];

  alumnosData.forEach(a => {

    let presente = document.querySelector(`.presente[data-id="${a.id_alumno}"]`).checked;

    if (presente) {

      let horaActual = new Date().toTimeString().slice(0,5);

      asistencias.push({
        id_alumno: a.id_alumno,
        fecha: fecha,
        hora_salida: horaActual
      });

    }

  });

  if (asistencias.length === 0) {
    alert('Seleccione al menos un alumno');
    return;
  }

  fetch('../process/process_asistencia.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ asistencias })
  })
  .then(res => res.json())
  .then(data => {

    if (data.success) {
      alert('✓ Salida registrada correctamente');
    } else {
      alert('Error: ' + data.error);
    }

  })
  .catch(err => {
    console.error(err);
    alert('Error al registrar salida');
  });

}

// =========================
// VALIDAR HORARIO SELECCIONADO
// =========================
function validarHorarioSeleccionado(){

  let grupoId = document.getElementById('grupo').value;

  if(!grupoId) return true;

  let grupo = gruposData.find(g => g.id_grupo == grupoId);

  if(!grupo) return true;

  let ahora = new Date();
  let horaActual = ahora.getHours().toString().padStart(2,'0') + ':' +
                   ahora.getMinutes().toString().padStart(2,'0');

  let inicio = grupo.hora_inicio ? grupo.hora_inicio.substring(0,5) : '00:00';
  let fin = grupo.hora_fin.substring(0,5);

  if(horaActual < inicio){
    alert("⏳ La clase aún no empieza");
    return false;
  }

  if(horaActual > fin){
    alert("⚠ La clase ya terminó");
    return false;
  }

  return true;
}

function registrarSalidaAlumno(id){

  let fecha = document.getElementById('fecha').value;

  let horaActual = new Date().toTimeString().slice(0,5);

  fetch('../process/process_asistencia.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({
      asistencias: [{
        id_alumno:id,
        fecha:fecha,
        hora_salida:horaActual
      }]
    })
  })
  .then(res => res.json())
  .then(data=>{
    alert(data.success ? "Salida registrada" : data.error);
    cargarAlumnos();
  });
}

function cargarHorariosHoy(){

  fetch('../process/get_horarios.php')
  .then(res => res.json())
  .then(data => {

    let html = '';

    if(data.length === 0){
      html = '<span class="text-gray-400">No hay horarios registrados</span>';
    } else {

      data.forEach(h => {

        html += `
        <div style="padding:8px;border-bottom:1px solid #eee;">
          <strong style="color:#0f766e;">${h.curso}</strong> - ${h.grupo}<br>
          <span class="text-gray-500">
            ${h.dias || 'Sin días'} | ${h.hora_inicio} - ${h.hora_fin}
          </span>
        </div>`;

      });

    }

    document.getElementById('horariosHoy').innerHTML = html;

  })
  .catch(err => {
    console.error(err);
  });

}

cargarHorariosHoy();

</script>

<?php
$content = ob_get_clean();
require __DIR__. '/layout.php';