<?php
require_once __DIR__ . '/../config/auth.php';

$title  = 'Inicio';
$active = 'home';
$ROLE   = strtoupper($_SESSION['role'] ?? '');

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
    <div class="text-3xl font-bold text-gray-800">120</div>
    <small class="text-gray-400">Dato temporal</small>
  </div>

  <div class="bg-white rounded-2xl shadow p-5 border">
    <div class="text-xs text-gray-500">Cuotas Pendientes</div>
    <div class="text-3xl font-bold text-yellow-500">35</div>
    <small class="text-gray-400">Dato temporal</small>
  </div>

  <div class="bg-white rounded-2xl shadow p-5 border">
    <div class="text-xs text-gray-500">Pagos Realizados</div>
    <div class="text-3xl font-bold text-green-600">85</div>
    <small class="text-gray-400">Dato temporal</small>
  </div>

  <div class="bg-white rounded-2xl shadow p-5 border">
    <div class="text-xs text-gray-500">Practicantes</div>
    <div class="text-3xl font-bold text-blue-500">10</div>
    <small class="text-gray-400">Dato temporal</small>
  </div>

</div>

<!-- ================= GRAFICA SIMPLE ================= -->
<div class="bg-white rounded-2xl shadow p-6 border mb-6">
  <h3 class="text-lg font-semibold mb-4">Vista General (Demo)</h3>
  <canvas id="graficoDemo" height="120"></canvas>
</div>

<!-- ================= ACTIVIDAD ================= -->
<div class="bg-white rounded-2xl shadow p-6 border">
  <h3 class="text-lg font-semibold mb-2">Actividad Reciente</h3>
  <ul class="text-gray-600 list-disc list-inside">
    <li>Sistema en fase de desarrollo</li>
    <li>Próximamente se mostrarán datos reales</li>
  </ul>
</div>

<!-- ================= CHART ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('graficoDemo');

new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Ene','Feb','Mar','Abr','May'],
    datasets: [{
      label: 'Pagos',
      data: [5, 10, 8, 15, 12],
      borderWidth: 1
    }]
  },
  options: {
    responsive: true
  }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';