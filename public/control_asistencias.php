<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'SECRETARIO']);

$title  = "Asistencia de Estudiantes";
$active = "asistencia";

$pdo = require __DIR__ . '/../config/db.php';

// Obtener cursos
$stmt = $pdo->query("SELECT id_curso, nombre_curso FROM cursos ORDER BY nombre_curso ASC");
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener carreras
$stmt = $pdo->query("SELECT id_carrera, nombre_carrera FROM carreras ORDER BY nombre_carrera ASC");
$carreras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =========================
// HISTORIAL
// =========================
$stmt = $pdo->prepare("
  SELECT 
      a.nombre,
      c.nombre_curso,
      g.nombre_grupo,
      asl.fecha,
      asl.hora_entrada,
      asl.hora_salida
  FROM asistencias asl
  INNER JOIN alumnos a ON a.id_alumno = asl.id_alumno
  INNER JOIN matriculas m ON m.id_alumno = a.id_alumno
  INNER JOIN grupos g ON g.id_grupo = m.id_grupo
  INNER JOIN cursos c ON c.id_curso = g.id_curso
  WHERE asl.fecha = CURDATE()
  ORDER BY asl.hora_entrada ASC
  ");
$stmt->execute();
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

  .pagination-controls{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:0.75rem;
    flex-wrap:wrap;
  }

  .page-button{
    background:#0f766e;
    color:white;
    border:none;
    padding:0.55rem 0.95rem;
    border-radius:9999px;
    cursor:pointer;
  }

  .page-button:disabled{
    opacity:0.45;
    cursor:not-allowed;
  }

  .text-gray-600{ color:#666; }
  .text-sm{ font-size:0.85rem; }

  .hidden{display:none;}
</style>

<h2 class="text-3xl font-bold mb-2">Control de Asistencia</h2>
<p class="text-gray-600 mb-6">Vista basada en cursos → grupos → alumnos</p>

<div id="horaActual" style="font-weight:bold;"></div>

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

    <button id="tab-practicantes"
        class="tab-btn px-4 py-2 font-semibold text-gray-600 hover:bg-gray-100"
        onclick="mostrarTab('practicantes')">
        Practicantes
    </button>

    <button id="tab-historial"
        class="tab-btn px-4 py-2 font-semibold text-gray-600 hover:bg-gray-100"
        onclick="mostrarTab('historial')">
        Historial
    </button>

</div>

<!-- ========================= -->
<!-- TAB ASISTENCIA - Solo ADMIN -->
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

  <div class="card">

    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold">Alumnos del Grupo</h3>

        <div class="flex gap-2">
            <button type="button" onclick="exportarAsistenciaAlumnosPDF()" class="bg-red-600 text-white px-3 py-2 rounded">
    PDF
</button>

<button type="button" onclick="exportarAsistenciaAlumnosExcel()" class="bg-green-600 text-white px-3 py-2 rounded">
    EXCEL
</button>

<button type="button" onclick="exportarAsistenciaAlumnosXML()" class="bg-blue-600 text-white px-3 py-2 rounded">
    XML
</button>
        </div>
    </div>

    <table class="shadow-sm">

      <thead>
        <tr>
          <th>#</th>
          <th>Alumno</th>
          <th>DNI</th>
          <th>Teléfono</th>
          <th class="center">Acción</th>
          <th class="center">Estado</th>
          <th class="center">Salida</th>
        </tr>
      </thead>

      <tbody id="tabla"></tbody>

    </table>

  </div>

</div>


<!-- ========================= -->
<!-- TAB PRACTICANTES -->
<!-- ========================= -->
<div id="contenido-practicantes" class="tab-content hidden">

  <div class="card">

    <div class="flex items-center justify-between mb-4">
      <h3 class="font-semibold">Seleccionar Practicante</h3>
      <button id="btnTogglePracticanteFiltro" type="button" class="bg-teal-600 text-white px-4 py-2 rounded">
        Mostrar filtro
      </button>
    </div>

    <div class="grid">
      <div>
        <label>Fecha</label>
        
        <input type="date" id="fechaPracticantes" class="border px-3 py-2 rounded w-full" value="<?= date('Y-m-d') ?>">
      </div>
      <div>
        <label>Carrera</label>
        <select id="filtroCarreraPracticantes" class="border px-3 py-2 rounded w-full">
          <option value="">-- Todas las carreras --</option>
          <?php foreach($carreras as $c): ?>
            <option value="<?= $c['id_carrera'] ?>">
              <?= htmlspecialchars($c['nombre_carrera']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div id="panelFiltrosPracticantes" class="hidden mt-4 p-4 bg-gray-50 border border-gray-200 rounded">
      <div class="grid">
        <div>
          <label>Nombre</label>
          <input type="text" id="filtroNombrePracticantes" class="border px-3 py-2 rounded w-full" placeholder="Buscar por nombre">
        </div>
        <div>
          <label>DNI</label>
          <input type="text" id="filtroDniPracticantes" class="border px-3 py-2 rounded w-full" placeholder="Buscar por DNI">
        </div>
      </div>
    </div>

    <div id="infoPracticantes" class="mt-4 text-sm text-gray-600">Todos los practicantes activos</div>
  </div>

  <div class="grid">
    <div class="card stat">Presentes<div id="presentesPracticantes">0</div></div>
    <div class="card stat">Ausentes<div id="ausentesPracticantes">0</div></div>
    <div class="card stat">Porcentaje<div id="porcentajePracticantes">0%</div></div>
  </div>

  <div class="card">

  <div class="flex items-center justify-between mb-4">
    <h3 class="font-semibold">Practicantes</h3>

    <div class="flex gap-2">
      <button 
        type="button"
        onclick="exportarPracticantesPDF()"
        class="bg-red-600 text-white px-3 py-2 rounded">
        PDF
      </button>

      <button 
        type="button"
        onclick="exportarPracticantesExcel()"
        class="bg-green-600 text-white px-3 py-2 rounded">
        EXCEL
      </button>

      <button 
        type="button"
        onclick="exportarPracticantesXML()"
        class="bg-blue-600 text-white px-3 py-2 rounded">
        XML
      </button>
    </div>
  </div>

    <div style="overflow-x:auto;">
      <table class="shadow-sm" style="min-width:1100px;">
        <thead>
          <tr>
            <th>#</th>
            <th>Alumno</th>
            <th>DNI</th>
            <th>Teléfono</th>
            <th>Carrera</th>
            <th>Horario de hoy</th>     
            <th class="center">Acción</th>
            <th class="center">Estado</th>
            <th class="center">Salida</th>
          </tr>
        </thead>
        <tbody id="tablaPracticantes"></tbody>
      </table>
    </div>
  </div>

</div>


<!-- ========================= -->
<!-- TAB HISTORIAL -->
<!-- ========================= -->
<div id="contenido-historial" class="tab-content hidden">

<div class="card">
  <h3 class="font-semibold mb-4">Historial de Asistencia</h3>

  <?php
      $total = count($historial);
      $presentes = 0;
      $ausentes = 0;

      foreach($historial as $h){

          if(isset($h['estado']) && $h['estado'] === 'Asistió'){
              $presentes++;
          }

          if(isset($h['estado']) && $h['estado'] === 'Ausente'){
              $ausentes++;
          }

      }

      $evaluados = $presentes + $ausentes;

      $porcentaje =
          $evaluados > 0
          ? round(($presentes / $evaluados) * 100)
          : 0;

  ?>


  <div class="card mb-4">
    <div class="grid">

      <div>
        <label>Fecha</label>
        <input type="date" id="filtroFecha"
          class="border px-3 py-2 rounded w-full"
          value="<?= date('Y-m-d') ?>">
      </div>

      <div>
        <label>Curso</label>
        <select id="filtroCurso" class="border px-3 py-2 rounded w-full">
          <option value="">Todos</option>
          <?php foreach($cursos as $c): ?>
            <option value="<?= $c['id_curso'] ?>">
              <?= htmlspecialchars($c['nombre_curso']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label>Grupo</label>
        <select id="filtroGrupo" class="border px-3 py-2 rounded w-full">
          <option value="">Todos</option>
        </select>
      </div>

      <div style="display:flex;align-items:end;">
        <button
          type="button"
          onclick="filtrarHistorial()"
          class="bg-teal-600 text-white px-4 py-2 rounded w-full">
          Filtrar
        </button>

      </div>

    </div>
  </div>

  <div class="grid mb-4">
    <div class="card stat">
      Presentes
      <div id="presentesHistorial"><?= $presentes ?></div>
    </div>

    <div class="card stat">
      Ausentes
      <div id="ausentesHistorial"><?= $ausentes ?></div>
    </div>

    <div class="card stat">
      Asistencia
      <div id="porcentajeHistorial"><?= $porcentaje ?>%</div>
    </div>
  </div>

  <table class="shadow-sm">

    <thead>
      <tr>
        <th>Alumno</th>
        <th>Curso</th>
        <th>Grupo</th>
        <th>Entrada</th>
        <th>Salida</th>
        <th>Estado</th>
      </tr>
    </thead>

    <tbody>

      <?php if($historial): ?>
        <?php foreach($historial as $h): ?>

        <tr>
          <td><?= htmlspecialchars($h['nombre']) ?></td>
          <td><?= $h['nombre_curso'] ?></td>
          <td><?= $h['nombre_grupo'] ?></td>

          <td>
            <?= $h['hora_entrada'] ? substr($h['hora_entrada'],0,5) : '-' ?>
          </td>

          <td>
            <?= $h['hora_salida'] ? substr($h['hora_salida'],0,5) : '-' ?>
          </td>

            <td>

                <?php

                  // 🔥 Definir estado de forma segura
                  $estado =
                      $h['estado']
                      ?? 'Pendiente';

                ?>

                <?php if($estado === 'Asistió'): ?>

                  <span style="color:green;">✔ Asistió</span>

                <?php elseif($estado === 'Ausente'): ?>

                  <span style="color:red;">✖ Ausente</span>

                <?php else: ?>

                  <span style="color:gray;">⏳ Pendiente</span>

                <?php endif; ?>

            </td>



        </tr>

        <?php endforeach; ?>
      <?php else: ?>

      <tr>
        <td colspan="6" class="center">No hay registros hoy</td>
      </tr>

      <?php endif; ?>

    </tbody>

  </table>

  <div class="pagination-controls mb-4">
    <button id="prevPage" class="page-button" type="button">Anterior</button>
    <span id="pageInfo">Página 1 de 1</span>
    <button id="nextPage" class="page-button" type="button">Siguiente</button>
  </div>

  <div class="card mt-4">
    <h3>📊 Asistencia semanal</h3>
    <canvas id="graficaAsistencia"></canvas>
  </div>


  <select id="selectorSemana" class="form-select w-auto mb-2">
    <option value="actual">Semana actual</option>
    <option value="anterior">Semana anterior</option>
  </select>


  <div class="card mt-4">
    <h3 style="font-weight:bold;color:#0f766e;">
      📊 Presentes vs Ausentes (Semana)
    </h3>
    <canvas id="graficaResumenSemana"></canvas>
  </div>


  <h5 class="mt-4">Tendencia mensual de asistencia</h5>

  <canvas id="graficaMensual" height="100"></canvas>

  <h5 class="mt-4">Resumen mensual</h5>

  <canvas id="graficaResumenMensual" height="100"></canvas>

</div>

</div>

<!-- ========================= -->
<!-- MODAL EDITAR ASISTENCIA PRACTICANTE -->
<!-- ========================= -->
<div id="modalEditarAsistenciaPracticante" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded shadow-lg p-6 w-full max-w-md">
    <h3 class="font-semibold mb-4">Editar asistencia del practicante</h3>

    <input type="hidden" id="editIdAsistenciaPracticante">

    <div class="mb-3">
      <label class="block mb-1">Estado</label>
      <select id="editEstadoPracticante" class="border px-3 py-2 rounded w-full">
        <option value="Pendiente">Pendiente</option>
        <option value="Asistió">Asistió</option>
        <option value="Tarde">Tarde</option>
        <option value="Ausente">Ausente</option>
        <option value="Justificado">Justificado</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="block mb-1">Hora entrada</label>
      <input type="time" id="editHoraEntradaPracticante" class="border px-3 py-2 rounded w-full">
    </div>

    <div class="mb-3">
      <label class="block mb-1">Hora salida</label>
      <input type="time" id="editHoraSalidaPracticante" class="border px-3 py-2 rounded w-full">
    </div>

    <div class="mb-4">
      <label class="block mb-1">Observación / motivo</label>
      <textarea id="editObservacionPracticante" class="border px-3 py-2 rounded w-full" rows="3" placeholder="Ej: Se corrigió porque se marcó tarde por error"></textarea>
    </div>

    <div class="flex justify-end gap-2">
      <button type="button" onclick="cerrarModalEditarAsistenciaPracticante()" class="bg-gray-500 text-white px-4 py-2 rounded">
        Cancelar
      </button>

      <button type="button" onclick="guardarEdicionAsistenciaPracticante()" class="bg-teal-600 text-white px-4 py-2 rounded">
        Guardar
      </button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

/* ================================
   EXPORTAR ASISTENCIA DE ALUMNOS
   POR FECHA / CURSO / GRUPO
================================ */

function obtenerParametrosExportacionAlumnos() {
    const fecha = document.getElementById('fecha')?.value;
    const curso = document.getElementById('curso')?.value;
    const grupo = document.getElementById('grupo')?.value;

    if (!fecha) {
        alert("Seleccione una fecha.");
        return null;
    }

    if (!curso) {
        alert("Seleccione un curso.");
        return null;
    }

    if (!grupo) {
        alert("Seleccione un grupo.");
        return null;
    }

    return new URLSearchParams({
        fecha: fecha,
        id_curso: curso,
        id_grupo: grupo
    }).toString();
}

function exportarAsistenciaAlumnosPDF() {
    const params = obtenerParametrosExportacionAlumnos();

    if (!params) return;

    window.open("exportar_asistencia_alumnos_pdf.php?" + params, "_blank");
}

function exportarAsistenciaAlumnosExcel() {
    const params = obtenerParametrosExportacionAlumnos();

    if (!params) return;

    window.location.href = "exportar_asistencia_alumnos_excel.php?" + params;
}

function exportarAsistenciaAlumnosXML() {
    const params = obtenerParametrosExportacionAlumnos();

    if (!params) return;

    window.location.href = "exportar_asistencia_alumnos_xml.php?" + params;
}

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
let practicantesData = [];
let gruposData = [];
let historialData = [];
let historialPage = 1;
const historialPageSize = 10;

// =========================
// CARGAR GRUPOS
// =========================
function cargarGrupos() {

    let cursoId =
        document.getElementById('curso').value;

    let selectGrupo =
        document.getElementById('grupo');

    selectGrupo.innerHTML =
        '<option value="">-- Cargando... --</option>';

    alumnosData = [];
    document.getElementById('tabla').innerHTML = '';

    if (!cursoId) {

      selectGrupo.innerHTML =
        '<option value="">-- Primero seleccione curso --</option>';

      return;

    }

    fetch(
      `../process/get_grupos.php?id_curso=${cursoId}`
    )
    .then(res => res.json())
    .then(data => {

      if (data.success) {

        gruposData = data.grupos;

        selectGrupo.innerHTML =
          '<option value="">-- Seleccione Grupo --</option>';

        data.grupos.forEach(g => {

          let op =
            document.createElement('option');

          op.value =
            g.id_grupo;

          op.textContent =
            g.nombre_grupo;

     // convertir horarios a texto
          let horarios = g.horarios || [];

        let horariosTexto = horarios
            .map(h => `${h.hora_inicio} - ${h.hora_fin}`)
            .join(' | ');

          let diasTexto = horarios
            .map(h => h.dia)
            .join(' / ');

          op.dataset.dias = diasTexto;
          op.dataset.hora = horariosTexto;

      // 🔥 guardar horarios completos (IMPORTANTE)
          op.dataset.horarios = JSON.stringify(horarios);

          selectGrupo.appendChild(op);

        });

      }

    });

}

// =========================
// CARGAR GRUPOS FILTRO HISTORIAL
// =========================

function cargarGruposFiltro(){

  let idCurso =
      document.getElementById('filtroCurso').value;

  let selectGrupo =
      document.getElementById('filtroGrupo');

  selectGrupo.innerHTML =
      '<option value="">Cargando...</option>';

  if(!idCurso){

    selectGrupo.innerHTML =
      '<option value="">Todos</option>';

    return;

  }

  fetch(
    `../process/get_grupos.php?id_curso=${idCurso}`
  )
  .then(res => res.json())
  .then(data => {

    selectGrupo.innerHTML =
      '<option value="">Todos</option>';

    data.grupos.forEach(g => {

      let op =
        document.createElement('option');

      op.value =
        g.id_grupo;

      op.textContent =
        g.nombre_grupo;

      selectGrupo.appendChild(op);

    });

    filtrarHistorial();


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
    let horarios = grupo.horarios || [];
    let horariosTexto = horarios
      .map(h => `${h.dia} ${h.hora_inicio}-${h.hora_fin}`)
      .join('<br>');

    document.getElementById('infoGrupo').innerHTML =
      `<span class="badge">${grupo.nombre_grupo}</span><br>${horariosTexto}`;
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

    let tabla = document.getElementById("tabla");

    tabla.innerHTML = "";

    alumnosData.forEach((alumno, index) => {

        tabla.innerHTML += crearFilaAlumno(
            alumno,
            index
        );

    });

    actualizarEstadisticas();

}

function cargarPracticantes() {

  let fecha = document.getElementById('fechaPracticantes').value;
  let carrera = document.getElementById('filtroCarreraPracticantes')?.value || '';

  if (!fecha) {
    fecha = new Date().toISOString().slice(0, 10);
  }

  const hoy = new Date().toISOString().slice(0, 10);

  localStorage.setItem('fechaPracticantesSeleccionada', fecha);
  localStorage.setItem('fechaPracticantesGuardadaEn', hoy);

  let url = `../process/get_practicantes_por_dia.php?fecha=${encodeURIComponent(fecha)}`;

  if (carrera !== '') {
    url += `&id_carrera=${encodeURIComponent(carrera)}`;
  }

  fetch(url)
    .then(async res => {
      const texto = await res.text();

      try {
        return JSON.parse(texto);
      } catch (e) {
        console.error("Respuesta no JSON:", texto);
        throw new Error("El servidor no devolvió JSON válido");
      }
    })
    .then(data => {
      if (!data.success) {
        alert(data.error || 'Error al cargar practicantes');
        return;
      }

      practicantesData = data.practicantes;

      const info = document.getElementById('infoPracticantes');
      if (info) {
        info.innerText = `Mostrando practicantes con horario para ${data.dia_semana} (${data.total})`;
      }

      renderTablaPracticantes();
    })
    .catch(error => {
      console.error("Error cargarPracticantes:", error);
      alert("Error al cargar practicantes: " + error.message);
    });
}

function renderTablaPracticantes() {

    let tabla = document.getElementById("tablaPracticantes");
    let filtroCarrera = document.getElementById('filtroCarreraPracticantes')?.value;
    let filtroNombre = document.getElementById('filtroNombrePracticantes')?.value.trim().toLowerCase();
    let filtroDni = document.getElementById('filtroDniPracticantes')?.value.trim().toLowerCase();

    let listaFiltrada = practicantesData.filter(practicante => {
      if (filtroCarrera && String(practicante.id_carrera) !== filtroCarrera) {
        return false;
      }

      if (filtroNombre && !practicante.nombre.toLowerCase().includes(filtroNombre)) {
        return false;
      }

      if (filtroDni && !String(practicante.dni).toLowerCase().includes(filtroDni)) {
        return false;
      }

      return true;
    });

    tabla.innerHTML = "";

    if (listaFiltrada.length === 0) {
      tabla.innerHTML = '<tr><td colspan="9" class="center">No hay practicantes registrados</td></tr>';
      actualizarEstadisticasPracticantes();
      return;
    }

    listaFiltrada.forEach((practicante, index) => {

        tabla.innerHTML += crearFilaPracticante(
            practicante,
            index
        );

    });

    actualizarEstadisticasPracticantes();

}

// =========================
// ACTUALIZAR ESTADÍSTICAS PRACTICANTES
// =========================

function actualizarEstadisticasPracticantes() {

    let estados = document.querySelectorAll("[id^='estado-practicante-']");

    let presentes = 0;
    let ausentes = 0;
    let tardes = 0;
    let justificados = 0;

    estados.forEach(e => {
        const estado = e.innerText.trim();

        if (estado === "Asistió") presentes++;
        if (estado === "Ausente") ausentes++;
        if (estado === "Tarde") tardes++;
        if (estado === "Justificado") justificados++;
    });

    let total = estados.length;

    // Para el porcentaje, Tarde y Justificado también cuentan como asistencia válida
    let asistenciaValida = presentes + tardes + justificados;

    let porcentaje = total > 0
        ? Math.round((asistenciaValida / total) * 100)
        : 0;

    document.getElementById("presentesPracticantes").innerText = presentes;
    document.getElementById("ausentesPracticantes").innerText = ausentes;
    document.getElementById("porcentajePracticantes").innerText = porcentaje + "%";
}

function crearFilaPracticante(practicante, index) {

    let estado = practicante.estado ?? "Pendiente";

    let color = "gray";
    if (estado === "Asistió") color = "green";
    if (estado === "Ausente") color = "red";
    if (estado === "Tarde") color = "yellow";
    if (estado === "Justificado") color = "blue";

    let horaEntrada = practicante.hora_entrada ?? null;
    let horaSalida = practicante.hora_salida ?? null;

    let bloqueEntrada = "";

    if (horaEntrada) {
        if (estado === "Tarde") {
            bloqueEntrada = `
                <div class="text-yellow-700 font-semibold mb-1">
                    Tarde: ${horaEntrada}
                </div>
            `;
        } else {
            bloqueEntrada = `
                <div class="text-green-700 font-semibold mb-1">
                    Entrada: ${horaEntrada}
                </div>
            `;
        }
    } else if (estado === "Ausente") {
        bloqueEntrada = `
            <div class="text-red-600 font-semibold mb-1">
                Marcado ausente
            </div>
        `;
    } else if (estado === "Justificado") {
        bloqueEntrada = `
            <div class="text-blue-600 font-semibold mb-1">
                Justificado
            </div>
        `;
    } else {
        bloqueEntrada = `
            <button 
                class="bg-green-500 text-white px-3 py-1 rounded mb-1"
                onclick="marcarAsistencia(${practicante.id_asistencia}, 'Asistió', 'practicante')">
                Entrada
            </button>
        `;
    }

    let bloqueSalida = "";

    if (horaSalida) {
        bloqueSalida = `
            <span class="text-gray-700 font-semibold">
                ${horaSalida}
            </span>
        `;
    } else if (estado === "Asistió" || estado === "Tarde") {
        bloqueSalida = `
            <button 
                class="bg-gray-500 text-white px-3 py-1 rounded"
                onclick="registrarSalidaPracticante(${practicante.id_asistencia})">
                Salida
            </button>
        `;
    } else {
        bloqueSalida = "--";
    }

    return `
        <tr>
            <td>${index + 1}</td>

            <td>
                <strong>${practicante.nombre}</strong>
            </td>

            <td>${practicante.dni ?? '-'}</td>
            <td>${practicante.telefono ?? '-'}</td>
            <td>${practicante.carrera ?? '-'}</td>

            <td>
                <strong>${practicante.horario_hoy ?? '-'}</strong>
                <br>
                <small class="text-gray-500">${practicante.modalidad_horario ?? ''}</small>
            </td>

            <td class="center">
                ${bloqueEntrada}

                <select 
                    class="border px-2 py-1 rounded bg-gray-700 text-white mt-1"
                    onchange="ejecutarOpcionPracticante(this, ${practicante.id_asistencia})">
                    <option value="">Más opciones</option>
                    <option value="Asistió">Marcar entrada</option>
                    <option value="Tarde">Marcar tarde</option>
                    <option value="Ausente">Marcar ausente</option>
                    <option value="Justificado">Justificar</option>
                    <option value="Editar">Editar asistencia</option>
                </select>
            </td>

            <td class="center">
                <span 
                    id="estado-practicante-${practicante.id_asistencia}"
                    class="px-2 py-1 rounded text-white bg-${color}-500">
                    ${estado}
                </span>
            </td>

            <td class="center">
                ${bloqueSalida}
            </td>
        </tr>
    `;
}


// =========================
// EJECUTAR OPCIÓN PRACTICANTE
// =========================

function ejecutarOpcionPracticante(select, id_asistencia) {

    const accion = select.value;

    if (!accion) {
        return;
    }

    const practicante = practicantesData.find(p => String(p.id_asistencia) === String(id_asistencia));

    if (accion === "Editar") {

        if (!practicante) {
            alert("No se encontró la información del practicante.");
            select.value = "";
            return;
        }

        abrirModalEditarAsistenciaPracticante(
            practicante.id_asistencia,
            practicante.estado,
            practicante.hora_entrada,
            practicante.hora_salida,
            practicante.observacion ?? ''
        );

        select.value = "";
        return;
    }

    marcarAsistencia(id_asistencia, accion, 'practicante');

    select.value = "";
}

function convertirHoraParaInput(hora) {

    if (!hora) return '';

    hora = String(hora).trim();

    if (/^\d{2}:\d{2}:\d{2}$/.test(hora)) {
        return hora.substring(0, 5);
    }

    if (/^\d{2}:\d{2}$/.test(hora)) {
        return hora;
    }

    const match = hora.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);

    if (match) {
        let h = parseInt(match[1], 10);
        const m = match[2];
        const ampm = match[3].toUpperCase();

        if (ampm === 'PM' && h < 12) h += 12;
        if (ampm === 'AM' && h === 12) h = 0;

        return String(h).padStart(2, '0') + ':' + m;
    }

    return '';
}

function convertirHoraParaInput(hora) {

    if (!hora) return '';

    hora = String(hora).trim();

    if (/^\d{2}:\d{2}:\d{2}$/.test(hora)) {
        return hora.substring(0, 5);
    }

    if (/^\d{2}:\d{2}$/.test(hora)) {
        return hora;
    }

    const match = hora.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);

    if (match) {
        let h = parseInt(match[1], 10);
        const m = match[2];
        const ampm = match[3].toUpperCase();

        if (ampm === 'PM' && h < 12) h += 12;
        if (ampm === 'AM' && h === 12) h = 0;

        return String(h).padStart(2, '0') + ':' + m;
    }

    return '';
}

function abrirModalEditarAsistenciaPracticante(id_asistencia, estado, horaEntrada = '', horaSalida = '', observacion = '') {

    document.getElementById('editIdAsistenciaPracticante').value = id_asistencia;
    document.getElementById('editEstadoPracticante').value = estado || 'Pendiente';

    document.getElementById('editHoraEntradaPracticante').value = convertirHoraParaInput(horaEntrada);
    document.getElementById('editHoraSalidaPracticante').value = convertirHoraParaInput(horaSalida);
    document.getElementById('editObservacionPracticante').value = observacion || '';

    document.getElementById('modalEditarAsistenciaPracticante').classList.remove('hidden');
}

function cerrarModalEditarAsistenciaPracticante() {
    document.getElementById('modalEditarAsistenciaPracticante').classList.add('hidden');
}

function abrirModalEditarAsistenciaPracticante(id_asistencia, estado, horaEntrada = '', horaSalida = '', observacion = '') {

    document.getElementById('editIdAsistenciaPracticante').value = id_asistencia;
    document.getElementById('editEstadoPracticante').value = estado || 'Pendiente';

    document.getElementById('editHoraEntradaPracticante').value = horaEntrada ? horaEntrada.substring(0, 5) : '';
    document.getElementById('editHoraSalidaPracticante').value = horaSalida ? horaSalida.substring(0, 5) : '';
    document.getElementById('editObservacionPracticante').value = observacion || '';

    document.getElementById('modalEditarAsistenciaPracticante').classList.remove('hidden');
}

function cerrarModalEditarAsistenciaPracticante() {
    document.getElementById('modalEditarAsistenciaPracticante').classList.add('hidden');
}

function guardarEdicionAsistenciaPracticante() {

    const id_asistencia = document.getElementById('editIdAsistenciaPracticante').value;
    const estado = document.getElementById('editEstadoPracticante').value;
    const hora_entrada = document.getElementById('editHoraEntradaPracticante').value;
    const hora_salida = document.getElementById('editHoraSalidaPracticante').value;
    const observacion = document.getElementById('editObservacionPracticante').value.trim();

    if (!id_asistencia) {
        alert('No se encontró la asistencia.');
        return;
    }

    if ((estado === 'Justificado' || observacion !== '') && observacion.length < 3) {
        alert('Ingresa una observación válida.');
        return;
    }

    const body =
        "id_asistencia=" + encodeURIComponent(id_asistencia) +
        "&estado=" + encodeURIComponent(estado) +
        "&hora_entrada=" + encodeURIComponent(hora_entrada) +
        "&hora_salida=" + encodeURIComponent(hora_salida) +
        "&observacion=" + encodeURIComponent(observacion);

    fetch("../process/editar_asistencia_practicante.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: body
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            cerrarModalEditarAsistenciaPracticante();
            cargarPracticantes();
        } else {
            alert(data.message || 'Error al editar asistencia');
        }
    })
    .catch(error => {
        console.error(error);
        alert('Error de conexión al editar asistencia');
    });
}

function obtenerPracticantesFiltrados() {

    let filtroCarrera = document.getElementById('filtroCarreraPracticantes')?.value;
    let filtroNombre = document.getElementById('filtroNombrePracticantes')?.value.trim().toLowerCase();
    let filtroDni = document.getElementById('filtroDniPracticantes')?.value.trim().toLowerCase();

    return practicantesData.filter(practicante => {
        if (filtroCarrera && String(practicante.id_carrera) !== filtroCarrera) {
            return false;
        }

        if (filtroNombre && !String(practicante.nombre).toLowerCase().includes(filtroNombre)) {
            return false;
        }

        if (filtroDni && !String(practicante.dni).toLowerCase().includes(filtroDni)) {
            return false;
        }

        return true;
    });
}

function exportarPracticantesPDF() {

    const fecha = document.getElementById('fechaPracticantes')?.value || '';
    const info = document.getElementById('infoPracticantes')?.innerText || '';

    let filas = '';

    obtenerPracticantesFiltrados().forEach((p, index) => {
        filas += `
            <tr>
                <td>${index + 1}</td>
                <td>${p.nombre ?? '-'}</td>
                <td>${p.dni ?? '-'}</td>
                <td>${p.telefono ?? '-'}</td>
                <td>${p.carrera ?? '-'}</td>
                <td>${p.horario_hoy ?? '-'}</td>
                <td>${p.hora_entrada ?? '-'}</td>
                <td>${p.hora_salida ?? '-'}</td>
                <td>${p.estado ?? 'Pendiente'}</td>
                <td>${p.observacion ?? '-'}</td>
            </tr>
        `;
    });

    const ventana = window.open('', '_blank');

    ventana.document.write(`
        <html>
        <head>
            <title>Reporte de Asistencia de Practicantes</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    padding: 20px;
                    color: #111827;
                }

                h2 {
                    color: #0f766e;
                    margin-bottom: 5px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    font-size: 12px;
                }

                th {
                    background: #0f766e;
                    color: white;
                    padding: 8px;
                    border: 1px solid #ddd;
                    text-align: left;
                }

                td {
                    padding: 8px;
                    border: 1px solid #ddd;
                }

                p {
                    font-size: 13px;
                }
            </style>
        </head>
        <body>
            <h2>SISTEMA BEATCELL</h2>
            <h3>Reporte de Asistencia de Practicantes</h3>

            <p><strong>Fecha:</strong> ${fecha}</p>
            <p><strong>Detalle:</strong> ${info}</p>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Practicante</th>
                        <th>DNI</th>
                        <th>Teléfono</th>
                        <th>Carrera</th>
                        <th>Horario de hoy</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Estado</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                    ${filas}
                </tbody>
            </table>

            <script>
                window.print();
            <\/script>
        </body>
        </html>
    `);

    ventana.document.close();
}

function exportarPracticantesExcel() {

    const fecha = document.getElementById('fechaPracticantes')?.value || 'sin_fecha';

    let tabla = `
        <table border="1">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Practicante</th>
                    <th>DNI</th>
                    <th>Teléfono</th>
                    <th>Carrera</th>
                    <th>Horario de hoy</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Estado</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
    `;

    obtenerPracticantesFiltrados().forEach((p, index) => {
        tabla += `
            <tr>
                <td>${index + 1}</td>
                <td>${p.nombre ?? '-'}</td>
                <td>${p.dni ?? '-'}</td>
                <td>${p.telefono ?? '-'}</td>
                <td>${p.carrera ?? '-'}</td>
                <td>${p.horario_hoy ?? '-'}</td>
                <td>${p.hora_entrada ?? '-'}</td>
                <td>${p.hora_salida ?? '-'}</td>
                <td>${p.estado ?? 'Pendiente'}</td>
                <td>${p.observacion ?? '-'}</td>
            </tr>
        `;
    });

    tabla += `
            </tbody>
        </table>
    `;

    const blob = new Blob([tabla], {
        type: 'application/vnd.ms-excel;charset=utf-8;'
    });

    descargarArchivo(blob, `asistencia_practicantes_${fecha}.xls`);
}

function limpiarXML(valor) {
    return String(valor ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&apos;');
}

function exportarPracticantesXML() {

    const fecha = document.getElementById('fechaPracticantes')?.value || 'sin_fecha';

    let xml = '<' + '?xml version="1.0" encoding="UTF-8"?>\n';
    xml += '<reporte_asistencia_practicantes>\n';
    xml += '  <fecha>' + limpiarXML(fecha) + '</fecha>\n';
    xml += '  <total>' + obtenerPracticantesFiltrados().length + '</total>\n';
      xml += '  <practicantes>\n';

      obtenerPracticantesFiltrados().forEach((p, index) => {        xml += '    <practicante>\n';
        xml += '      <numero>' + (index + 1) + '</numero>\n';
        xml += '      <id_practicante>' + limpiarXML(p.id_practicante) + '</id_practicante>\n';
        xml += '      <nombre>' + limpiarXML(p.nombre) + '</nombre>\n';
        xml += '      <dni>' + limpiarXML(p.dni) + '</dni>\n';
        xml += '      <telefono>' + limpiarXML(p.telefono) + '</telefono>\n';
        xml += '      <carrera>' + limpiarXML(p.carrera) + '</carrera>\n';
        xml += '      <horario_hoy>' + limpiarXML(p.horario_hoy) + '</horario_hoy>\n';
        xml += '      <modalidad>' + limpiarXML(p.modalidad_horario) + '</modalidad>\n';
        xml += '      <hora_entrada>' + limpiarXML(p.hora_entrada) + '</hora_entrada>\n';
        xml += '      <hora_salida>' + limpiarXML(p.hora_salida) + '</hora_salida>\n';
        xml += '      <estado>' + limpiarXML(p.estado ?? 'Pendiente') + '</estado>\n';
        xml += '      <observacion>' + limpiarXML(p.observacion) + '</observacion>\n';
        xml += '    </practicante>\n';
    });

    xml += '  </practicantes>\n';
    xml += '</reporte_asistencia_practicantes>';

    const blob = new Blob([xml], {
        type: 'application/xml;charset=utf-8;'
    });

    descargarArchivo(blob, `asistencia_practicantes_${fecha}.xml`);
}

function descargarArchivo(blob, nombreArchivo) {

    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.download = nombreArchivo;

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    URL.revokeObjectURL(url);
}

// =========================
// marcar asistencia
// =========================

function crearFilaAlumno(alumno, index) {

    let estado = alumno.estado ?? "Pendiente";

    let color = "gray";

    if (estado === "Asistió")
        color = "green";

    if (estado === "Ausente")
        color = "red";

    return `
        <tr>

            <td>${index + 1}</td>

            <td>${alumno.nombre}</td>

            <td>${alumno.dni}</td>

            <td>${alumno.telefono ?? ''}</td>

            <td class="center">

                <button 
                    class="bg-green-500 text-white px-2 py-1 rounded"
                    onclick="marcarAsistencia(${alumno.id_asistencia}, 'Asistió')">

                    Asistió

                </button>

                <button 
                    class="bg-red-500 text-white px-2 py-1 rounded"
                    onclick="marcarAsistencia(${alumno.id_asistencia}, 'Ausente')">

                    Ausente

                </button>

            </td>

            <td class="center">

                <span 
                    id="estado-${alumno.id_asistencia}"
                    class="px-2 py-1 rounded text-white bg-${color}-500">

                    ${estado}

                </span>

            </td>

            <td class="center">

                ${alumno.hora_salida ?? '--'}

            </td>

        </tr>
    `;
}

// =========================
// actualizar estadísticas
// =========================

function actualizarEstadisticas() {

    let estados =
        document.querySelectorAll("[id^='estado-']");

    let presentes = 0;
    let ausentes = 0;

    estados.forEach(e => {

        if (e.innerText === "Asistió")
            presentes++;

        if (e.innerText === "Ausente")
            ausentes++;

    });

    let total = estados.length;

    let porcentaje =
        total > 0
        ? Math.round((presentes / total) * 100)
        : 0;

    document.getElementById("presentes")
        .innerText = presentes;

    document.getElementById("ausentes")
        .innerText = ausentes;

    document.getElementById("porcentaje")
        .innerText = porcentaje + "%";

}


// =========================
// MARCAR ASISTENCIA
// =========================

function marcarAsistencia(id_asistencia, estado, tipo = 'alumno') {

    console.log("ID:", id_asistencia);
    console.log("Estado:", estado);
    console.log("Tipo:", tipo);

    if (!id_asistencia) {
        alert("No se encontró el ID de asistencia.");
        return;
    }

    let body =
        "id_asistencia=" + encodeURIComponent(id_asistencia) +
        "&estado=" + encodeURIComponent(estado);

    if (tipo === 'practicante') {
        body += "&tipo=practicante";
    }

    fetch("../process/actualizar_estado.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: body
    })
    .then(res => res.json())
    .then(data => {

        if (data.success) {

            let badge = document.getElementById(
                tipo === 'practicante'
                    ? "estado-practicante-" + id_asistencia
                    : "estado-" + id_asistencia
            );

            if (!badge) {
                console.warn("No se encontró el badge del estado.");
                return;
            }

            badge.innerText = estado;

            // Color por defecto
            badge.className = "px-2 py-1 rounded text-white bg-gray-500";

            if (estado === "Asistió") {
                badge.className = "px-2 py-1 rounded text-white bg-green-500";
            }

            if (estado === "Ausente") {
                badge.className = "px-2 py-1 rounded text-white bg-red-500";
            }

            if (estado === "Tarde") {
                badge.className = "px-2 py-1 rounded text-white bg-yellow-500";
            }

            if (estado === "Justificado") {
                badge.className = "px-2 py-1 rounded text-white bg-blue-500";
            }

            if (estado === "Pendiente") {
                badge.className = "px-2 py-1 rounded text-white bg-gray-500";
            }

            if (tipo === 'practicante') {
                actualizarEstadisticasPracticantes();
            } else {
                actualizarEstadisticas();
            }

        } else {
            alert(data.message || "Error al actualizar");
        }

    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error de conexión al actualizar asistencia");
    });

}

// =========================
// REGISTRAR SALIDA PRACTICANTE
// =========================

function registrarSalidaPracticante(id_asistencia) {

    console.log("Registrar salida practicante ID:", id_asistencia);

    if (!id_asistencia) {
        alert("No se encontró el ID de asistencia.");
        return;
    }

    let body = "id_asistencia=" + encodeURIComponent(id_asistencia);

    fetch("../process/registrar_salida.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: body
    })
    .then(res => res.json())
    .then(data => {

        if (data.success) {
            cargarPracticantes();
        } else {
            alert(data.message || "Error al registrar salida");
        }

    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error de conexión al registrar salida");
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

  let horarios = grupo.horarios || [];

  let ahora = new Date();
  let horaActual =
    ahora.getHours().toString().padStart(2,'0') + ':' +
    ahora.getMinutes().toString().padStart(2,'0');

  let diaActual = ahora.toLocaleDateString('es-ES', { weekday: 'long' });
  diaActual = diaActual.charAt(0).toUpperCase() + diaActual.slice(1);

  let valido = horarios.some(h => {
    if(h.dia !== diaActual) return false;
    return horaActual >= h.hora_inicio && horaActual <= h.hora_fin;
  });

  if(!valido){
    alert("⚠ No estás dentro del horario de clase");
    return false;
  }

  return true;
}


function cargarHorariosHoy(){

  let fecha = document.getElementById('fecha')?.value;

  fetch(`../process/get_horarios.php?fecha=${fecha}`)
  .then(res => res.json())
  .then(data => {

    let contenedor = {};

    // AGRUPAR
    data.forEach(h => {

      let key = `${h.curso} - ${h.grupo}`;

      if(!contenedor[key]){
        contenedor[key] = {
          normal: null,
          especiales: []
        };
      }

      if(h.tipo === "NORMAL"){
        contenedor[key].normal = h;
      } else {
        contenedor[key].especiales.push(h);
      }

    });

    // RENDER
    let html = '';

    Object.keys(contenedor).forEach(key => {

      let grupo = contenedor[key];

      html += `
      <div style="padding:10px;border-bottom:1px solid #eee;">
        <strong style="color:#0f766e;">${key}</strong><br>
      `;

      if(grupo.normal){
        html += `
          <div>• Normal: ${grupo.normal.hora_inicio} - ${grupo.normal.hora_fin}</div>
        `;
      }

      grupo.especiales.forEach(e => {
        html += `
          <div>• Especial (${e.dias}): ${e.hora_inicio} - ${e.hora_fin}</div>
        `;
      });

      html += `</div>`;
    });

    if(html === ''){
      html = '<span class="text-gray-400">No hay horarios hoy</span>';
    }

    document.getElementById('horariosHoy').innerHTML = html;

  })
  .catch(err => console.error(err));

}


setInterval(() => {
  let ahora = new Date();
  let hora = ahora.toLocaleTimeString();
  document.getElementById('horaActual').textContent = "Hora actual: " + hora;
}, 1000);


// =========================
// EVENTOS FILTROS HISTORIAL
// =========================

// 🔥 Cuando cambia la fecha → filtra automáticamente
document.getElementById('filtroFecha').addEventListener('change', filtrarHistorial);


// =========================
// FILTRAR HISTORIAL
// =========================

function filtrarHistorial(){

  let fecha =
    document.getElementById('filtroFecha').value;

  let curso =
    document.getElementById('filtroCurso').value;

  let grupo =
    document.getElementById('filtroGrupo').value;

  fetch(
    `get_historial_asistencias.php?fecha=${fecha}&id_curso=${curso}&id_grupo=${grupo}`
  )
  .then(res => res.json())
  .then(data => {

    if(!data.success){

      alert(data.error);

      return;

    }

    historialData = data.data;
    historialPage = 1;

    renderHistorial();
    actualizarStatsHistorial(historialData);
    cargarGraficaAsistencia();
    cargarResumenSemana();
    cargarGraficaMensual();
    cargarResumenMensual();

  })
  .catch(error => {

    console.error(error);

    alert("Error al cargar historial");

  });

}


function renderHistorial(){

  let tbody = document.querySelector('#contenido-historial tbody');

  let html = '';
  let total = historialData.length;
  let totalPages = Math.max(1, Math.ceil(total / historialPageSize));

  if (historialPage > totalPages) {
    historialPage = totalPages;
  }

  let start = (historialPage - 1) * historialPageSize;
  let pageData = historialData.slice(start, start + historialPageSize);

  if(pageData.length === 0){
    html = `<tr><td colspan="6">No hay registros</td></tr>`;
  } else {

    pageData.forEach(h => {

      let estado = '';

      if(h.estado === "Asistió"){

          estado =
            '<span style="color:green;">✔ Asistió</span>';

        }
        else if(h.estado === "Ausente"){

          estado =
            '<span style="color:red;">✖ Ausente</span>';

        }
        else{

          estado =
            '<span style="color:gray;">⏳ Pendiente</span>';

      }

      html += `
      <tr>
        <td>${h.nombre}</td>
        <td>${h.nombre_curso}</td>
        <td>${h.nombre_grupo}</td>
        <td>${h.hora_entrada ? h.hora_entrada.substring(0,5) : '-'}</td>
        <td>${h.hora_salida ? h.hora_salida.substring(0,5) : '-'}</td>
        <td>${estado}</td>
      </tr>`;
    });

  }

  tbody.innerHTML = html;

  document.getElementById('pageInfo').innerText =
    `Página ${historialPage} de ${totalPages}`;

  document.getElementById('prevPage').disabled = historialPage <= 1;
  document.getElementById('nextPage').disabled = historialPage >= totalPages;

}

function cambiarPaginaHistorial(delta){
  historialPage += delta;
  renderHistorial();
}

// =========================
// grafica asistencia semanal
// =========================

let grafica;

function cargarGraficaAsistencia(){

  fetch('../process/get_estadisticas_asistencia.php')
  .then(res => res.json())
  .then(data => {

    if(!data.success){
      console.error(data.error);
      return;
    }

    let dias = [];
    let totales = [];

    data.data.forEach(d => {
      dias.push(d.dia);
      totales.push(d.total);
    });

    let ctx = document.getElementById('graficaAsistencia').getContext('2d');

    if(grafica){
      grafica.destroy();
    }

    grafica = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: dias,
        datasets: [{
          label: 'Asistencias',
          data: totales
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: true
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(15, 118, 110, 0.15)'
            }
          },
          x: {
            grid: {
              color: 'rgba(15, 118, 110, 0.08)'
            }
          }
        }
      }
    });

  });

}

// =========================
// GRAFICA COMPARATIVA PRESENTES vs AUSENTES (SEMANA)
// =========================

let graficaResumen;

function cargarResumenSemana(){

  let semana = document
    .getElementById('selectorSemana')
    .value;

  fetch(`../process/get_resumen_semanal.php?semana=${semana}`)
  .then(res => res.json())
  .then(data => {

    if(!data.success){
      console.error(data.error);
      return;
    }

    let dias = [];
    let presentes = [];
    let ausentes = [];

    data.data.forEach(d => {

      dias.push(d.dia);
      presentes.push(d.presentes);
      ausentes.push(d.ausentes);

    });

    let ctx = document
      .getElementById('graficaResumenSemana')
      .getContext('2d');

    if(graficaResumen){
      graficaResumen.destroy();
    }

    graficaResumen = new Chart(ctx, {

      type: 'bar',

      data: {
        labels: dias,

        datasets: [
          {
            label: 'Presentes',
            data: presentes
          },
          {
            label: 'Ausentes',
            data: ausentes
          }
        ]
      },

      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top'
          },
          tooltip: {
            mode: 'index',
            intersect: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(51, 65, 85, 0.08)'
            }
          },
          x: {
            grid: {
              color: 'rgba(51, 65, 85, 0.06)'
            }
          }
        }
      }

    });

  });

}

// =========================
// GRAFICA TENDENCIA MENSUAL
// =========================

let graficaMensual;

function cargarGraficaMensual(){

  fetch('../process/get_resumen_mensual.php')
  .then(res => res.json())
  .then(data => {

    if(!data.success){
      console.error(data.error);
      return;
    }

    let dias = [];
    let asistencias = [];

    data.data.forEach(d => {

      dias.push(d.dia);
      asistencias.push(d.asistencias);

    });

    let ctx = document
      .getElementById('graficaMensual')
      .getContext('2d');

    if(graficaMensual){
      graficaMensual.destroy();
    }

    graficaMensual = new Chart(ctx, {

      type: 'line',

      data: {

        labels: dias,

        datasets: [
          {
            label: 'Asistencias por día',
            data: asistencias,
            tension: 0.3,
            borderColor: '#0f766e',
            backgroundColor: 'rgba(15, 118, 110, 0.15)',
            fill: true,
            pointBackgroundColor: '#0f766e',
            pointRadius: 4
          }
        ]

      },

      options: {

        responsive: true,

        plugins: {
          legend: {
            position: 'top'
          }
        },

        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(15, 118, 110, 0.12)'
            }
          },
          x: {
            grid: {
              color: 'rgba(15, 118, 110, 0.08)'
            }
          }
        }

      }

    });

  });

}

// =========================
// GRAFICA RESUMEN MENSUAL PRESENTES vs AUSENTES
// =========================

let graficaResumenMensual;

function cargarResumenMensual(){

  fetch('../process/get_resumen_mensual.php')
  .then(res => res.json())
  .then(data => {

    if(!data.success){
      console.error(data.error);
      return;
    }

    let asistencias = data.total_asistencias;
    let ausentes = data.total_ausentes;

    let ctx = document
      .getElementById('graficaResumenMensual')
      .getContext('2d');

    if(graficaResumenMensual){
      graficaResumenMensual.destroy();
    }

    graficaResumenMensual = new Chart(ctx, {

      type: 'bar',

      data: {

        labels: [
          'Asistencias',
          'Ausentes'
        ],

        datasets: [
          {
            label: 'Resumen mensual',
            data: [
              asistencias,
              ausentes
            ],
            backgroundColor: [
              'rgba(15, 118, 110, 0.8)',
              'rgba(220, 38, 38, 0.8)'
            ],
            borderColor: [
              'rgba(15, 118, 110, 1)',
              'rgba(220, 38, 38, 1)'
            ],
            borderWidth: 1
          }
        ]

      },

      options: {

        responsive: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(15, 118, 110, 0.12)'
            }
          },
          x: {
            grid: {
              color: 'rgba(15, 118, 110, 0.08)'
            }
          }
        }

      }

    });

  });

}

// =========================
// ACTUALIZAR STATS HISTORIAL
// =========================

function actualizarStatsHistorial(data){

  let presentes = 0;
  let ausentes = 0;

  data.forEach(h => {

    if(h.estado === "Asistió")
        presentes++;

    if(h.estado === "Ausente")
        ausentes++;

  });

  let evaluados =
      presentes + ausentes;

  let porcentaje =
      evaluados > 0
      ? Math.round(
          (presentes / evaluados) * 100
        )
      : 0;

  document.getElementById('presentesHistorial').innerText = presentes;
  document.getElementById('ausentesHistorial').innerText = ausentes;
  document.getElementById('porcentajeHistorial').innerText = porcentaje + "%";

}

// =========================
// EVENTOS DOM CONTENT LOADED
// =========================

document.addEventListener('DOMContentLoaded', () => {

  // =========================
  // CARGAS INICIALES
  // =========================
  filtrarHistorial();
  cargarHorariosHoy();
  cargarGraficaAsistencia();
  cargarResumenSemana();
  cargarGraficaMensual();
  cargarResumenMensual();

  // =========================
  // EVENTOS
  // =========================

  // Fecha alumnos / horarios dinámicos
  const fechaInput = document.getElementById('fecha');
  if (fechaInput) {
    fechaInput.addEventListener('change', cargarHorariosHoy);
  }

  // Fecha practicantes: recordar solo durante el día actual
  const fechaPracticantes = document.getElementById('fechaPracticantes');

  if (fechaPracticantes) {

    const hoySistema = new Date().toISOString().slice(0, 10);

    const fechaGuardada = localStorage.getItem('fechaPracticantesSeleccionada');
    const diaGuardado = localStorage.getItem('fechaPracticantesGuardadaEn');

    if (fechaGuardada && diaGuardado === hoySistema) {
      fechaPracticantes.value = fechaGuardada;
    } else {
      localStorage.removeItem('fechaPracticantesSeleccionada');
      localStorage.removeItem('fechaPracticantesGuardadaEn');
    }

    fechaPracticantes.addEventListener('change', function() {
      const hoy = new Date().toISOString().slice(0, 10);

      localStorage.setItem('fechaPracticantesSeleccionada', this.value);
      localStorage.setItem('fechaPracticantesGuardadaEn', hoy);

      cargarPracticantes();
    });
  }

  // Ahora sí carga practicantes usando la fecha restaurada o la fecha actual
  cargarPracticantes();

  const filtroCarreraPracticantes = document.getElementById('filtroCarreraPracticantes');
  if (filtroCarreraPracticantes) {
    filtroCarreraPracticantes.addEventListener('change', cargarPracticantes);
  }

  const filtroNombrePracticantes = document.getElementById('filtroNombrePracticantes');
  if (filtroNombrePracticantes) {
    filtroNombrePracticantes.addEventListener('input', renderTablaPracticantes);
  }

  const filtroDniPracticantes = document.getElementById('filtroDniPracticantes');
  if (filtroDniPracticantes) {
    filtroDniPracticantes.addEventListener('input', renderTablaPracticantes);
  }

  const btnTogglePracticanteFiltro = document.getElementById('btnTogglePracticanteFiltro');
  if (btnTogglePracticanteFiltro) {
    btnTogglePracticanteFiltro.addEventListener('click', function() {
      const panel = document.getElementById('panelFiltrosPracticantes');
      if (!panel) return;

      panel.classList.toggle('hidden');
      this.innerText = panel.classList.contains('hidden') ? 'Mostrar filtro' : 'Ocultar filtro';
    });
  }

  // Filtro historial por fecha
  const filtroFecha = document.getElementById('filtroFecha');
  if (filtroFecha) {
    filtroFecha.addEventListener('change', filtrarHistorial);
  }
  
  // Filtro por curso
  const filtroCurso = document.getElementById('filtroCurso');
  if (filtroCurso) {
    filtroCurso.addEventListener('change', cargarGruposFiltro);
  }

  // Filtro por grupo
  const filtroGrupo = document.getElementById('filtroGrupo');
  if (filtroGrupo) {
    filtroGrupo.addEventListener('change', filtrarHistorial);
  }

  // Selector de semana
  const selectorSemana = document.getElementById('selectorSemana');
  if (selectorSemana) {
    selectorSemana.addEventListener('change', cargarResumenSemana);
  }

  const prevPage = document.getElementById('prevPage');
  const nextPage = document.getElementById('nextPage');

  if (prevPage) {
    prevPage.addEventListener('click', () => cambiarPaginaHistorial(-1));
  }

  if (nextPage) {
    nextPage.addEventListener('click', () => cambiarPaginaHistorial(1));
  }

});

</script>


<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>

