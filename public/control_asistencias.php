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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


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

function marcarAsistencia(id_asistencia, estado) {

    console.log("ID:", id_asistencia);
    console.log("Estado:", estado);

    fetch("../process/actualizar_estado.php", {

        method: "POST",

        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },

        body:
            "id_asistencia=" + id_asistencia +
            "&estado=" + estado

    })
    .then(res => res.json())

    .then(data => {

        if (data.success) {

            let badge =
                document.getElementById(
                    "estado-" + id_asistencia
                );

            badge.innerText = estado;

            if (estado === "Asistió") {

                badge.className =
                    "px-2 py-1 rounded text-white bg-green-500";

            }

            if (estado === "Ausente") {

                badge.className =
                    "px-2 py-1 rounded text-white bg-red-500";

            }

            actualizarEstadisticas();

        } else {

            alert("Error al actualizar");

        }

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

  // 🔥 validar contra TODOS los horarios
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

  // 🔥 Fecha (horarios dinámicos)
  const fechaInput = document.getElementById('fecha');
  if (fechaInput) {
    fechaInput.addEventListener('change', cargarHorariosHoy);
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
require __DIR__. '/layout.php';