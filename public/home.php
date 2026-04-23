<?php
require_once __DIR__ . '/../config/auth.php';

$title  = 'Inicio';
$active = 'home';
$ROLE   = strtoupper($_SESSION['role'] ?? '');

$pdo = require __DIR__ . '/../config/db.php';

// =================
// DATOS DINÁMICOS
// =================

// Alumnos registrados esta semana
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM alumnos
    WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
");
$stmt->execute();
$alumnos_semana = $stmt->fetch()['total'];

// Prácticantes registrados esta semana
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM practicantes
    WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
");
$stmt->execute();
$practicantes_semana = $stmt->fetch()['total'];

// Estadísticas de asistencias esta semana
$stmt = $pdo->prepare("
    SELECT
        COUNT(CASE WHEN hora_entrada IS NOT NULL THEN 1 END) as presentes,
        COUNT(CASE WHEN hora_entrada IS NULL THEN 1 END) as ausentes
    FROM asistencias
    WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
");
$stmt->execute();
$asistencias = $stmt->fetch();
$presentes_semana = $asistencias['presentes'];
$ausentes_semana = $asistencias['ausentes'];
$total_asistencias = $presentes_semana + $ausentes_semana;
$porcentaje_asistencia = $total_asistencias > 0 ? round(($presentes_semana / $total_asistencias) * 100) : 0;

// Últimos alumnos registrados esta semana
$stmt = $pdo->prepare("
    SELECT nombre, fecha_registro
    FROM alumnos
    WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
    ORDER BY fecha_registro DESC
    LIMIT 5
");
$stmt->execute();
$ultimos_alumnos = $stmt->fetchAll();

// Últimos prácticantes registrados esta semana
$stmt = $pdo->prepare("
    SELECT nombre, fecha_registro
    FROM practicantes
    WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
    ORDER BY fecha_registro DESC
    LIMIT 5
");
$stmt->execute();
$ultimos_practicantes = $stmt->fetchAll();

// Totales generales
$stmt = $pdo->query("SELECT COUNT(*) as total FROM alumnos WHERE fecha_baja IS NULL");
$total_alumnos = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM practicantes WHERE fecha_baja IS NULL");
$total_practicantes = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cuotas WHERE estado = 'Pendiente'");
$stmt->execute();
$cuotas_pendientes = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cuotas WHERE estado = 'Pagado'");
$stmt->execute();
$pagos_realizados = $stmt->fetch()['total'];

ob_start();
?>

<!-- ENCABEZADO -->
<div class="mb-6">
  <h2 class="text-3xl font-bold text-gray-800 mb-2">
    Bienvenido <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?>
  </h2>
  <p class="text-sm text-gray-500">
    Panel principal del sistema Beatcell
  </p>
</div>

<!-- ================= KPI BASICOS ================= -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

  <div class="bg-white rounded-2xl shadow p-5 border">
    <div class="text-xs text-gray-500">Alumnos Registrados</div>
    <div class="text-3xl font-bold text-gray-800"><?= $total_alumnos ?></div>
    <small class="text-green-600">+<?= $alumnos_semana ?> esta semana</small>
  </div>

  <div class="bg-white rounded-2xl shadow p-5 border">
    <div class="text-xs text-gray-500">Cuotas Pendientes</div>
    <div class="text-3xl font-bold text-yellow-500"><?= $cuotas_pendientes ?></div>
    <small class="text-gray-400">Requieren atención</small>
  </div>

  <div class="bg-white rounded-2xl shadow p-5 border">
    <div class="text-xs text-gray-500">Pagos Realizados</div>
    <div class="text-3xl font-bold text-green-600"><?= $pagos_realizados ?></div>
    <small class="text-gray-400">Esta semana</small>
  </div>

  <div class="bg-white rounded-2xl shadow p-5 border">
    <div class="text-xs text-gray-500">Practicantes</div>
    <div class="text-3xl font-bold text-blue-500"><?= $total_practicantes ?></div>
    <small class="text-green-600">+<?= $practicantes_semana ?> esta semana</small>
  </div>

</div>

<!-- ================= NUEVAS SECCIONES ================= -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

  <!-- ÚLTIMOS ALUMNOS REGISTRADOS -->
  <div class="bg-white rounded-2xl shadow p-6 border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">Últimos Alumnos Registrados</h3>
    <div class="space-y-3">
      <?php if(empty($ultimos_alumnos)): ?>
        <p class="text-gray-500 text-sm">No hay alumnos registrados esta semana</p>
      <?php else: ?>
        <?php foreach($ultimos_alumnos as $alumno): ?>
          <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
            <div>
              <div class="font-medium text-gray-800"><?= htmlspecialchars($alumno['nombre']) ?></div>
              <div class="text-xs text-gray-500">Registrado: <?= date('d/m/Y', strtotime($alumno['fecha_registro'])) ?></div>
            </div>
            <div class="text-green-600 text-sm font-medium">Nuevo</div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- ÚLTIMOS PRÁCTICANTES REGISTRADOS -->
  <div class="bg-white rounded-2xl shadow p-6 border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">Últimos Prácticantes Registrados</h3>
    <div class="space-y-3">
      <?php if(empty($ultimos_practicantes)): ?>
        <p class="text-gray-500 text-sm">No hay prácticantes registrados esta semana</p>
      <?php else: ?>
        <?php foreach($ultimos_practicantes as $practicante): ?>
          <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
            <div>
              <div class="font-medium text-gray-800"><?= htmlspecialchars($practicante['nombre']) ?></div>
              <div class="text-xs text-gray-500">Registrado: <?= date('d/m/Y', strtotime($practicante['fecha_registro'])) ?></div>
            </div>
            <div class="text-blue-600 text-sm font-medium">Nuevo</div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- ================= GRAFICA DE ASISTENCIAS ================= -->
<div class="bg-white rounded-2xl shadow p-6 border mb-6">
  <h3 class="text-lg font-semibold mb-4 text-gray-800">Asistencias de la Semana</h3>
  <canvas id="graficoAsistencias" height="120"></canvas>
</div>

<!-- ================= ESTADÍSTICAS DE ASISTENCIAS ================= -->
<div class="bg-white rounded-2xl shadow p-6 border">
  <h3 class="text-lg font-semibold mb-4 text-gray-800">Estadísticas de Asistencia (Esta Semana)</h3>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="text-center p-4 bg-green-50 rounded-lg border border-green-200">
      <div class="text-2xl font-bold text-green-600"><?= $presentes_semana ?></div>
      <div class="text-sm text-green-800">Presentes</div>
    </div>
    <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200">
      <div class="text-2xl font-bold text-red-600"><?= $ausentes_semana ?></div>
      <div class="text-sm text-red-800">Ausentes</div>
    </div>
    <div class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200">
      <div class="text-2xl font-bold text-blue-600"><?= $porcentaje_asistencia ?>%</div>
      <div class="text-sm text-blue-800">Porcentaje de Asistencia</div>
    </div>
  </div>
</div>

<!-- ================= CHART ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Cargar gráfica de asistencias
async function cargarGraficaAsistencias() {
  try {
    const response = await fetch('../process/get_estadisticas_asistencia.php');
    const data = await response.json();

    if (!data.success) {
      console.error('Error cargando datos:', data.error);
      return;
    }

    const ctx = document.getElementById('graficoAsistencias');

    // Preparar datos para todos los días de la semana
    const diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    const asistenciasData = diasSemana.map(dia => {
      const encontrado = data.data.find(item => item.dia === dia);
      return encontrado ? encontrado.total : 0;
    });

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: diasSemana,
        datasets: [{
          label: 'Asistencias',
          data: asistenciasData,
          backgroundColor: 'rgba(15, 118, 110, 0.8)',
          borderColor: 'rgba(15, 118, 110, 1)',
          borderWidth: 1
        }]
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
              color: 'rgba(15, 118, 110, 0.1)'
            }
          },
          x: {
            grid: {
              color: 'rgba(15, 118, 110, 0.05)'
            }
          }
        }
      }
    });
  } catch (error) {
    console.error('Error:', error);
  }
}

// Cargar gráfica al cargar la página
document.addEventListener('DOMContentLoaded', cargarGraficaAsistencias);
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';