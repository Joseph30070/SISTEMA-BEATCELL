<?php
// public/layout.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($title))  $title  = 'CiipGestión';
if (!isset($active)) $active = 'home';

$config = require __DIR__ . '/../config/config.php';
$base   = rtrim($config['base_url'], '/');

// ============================
// NORMALIZA ROL
// ============================

$ROLE = strtoupper($_SESSION['rol'] ?? '');

// Roles válidos del sistema
$ROLES_VALIDOS = [
    'ADMINISTRADOR',
    'SECRETARIO',
    'ASISTENTE'
];

// Si el rol no es válido, se limpia por seguridad
if (!in_array($ROLE, $ROLES_VALIDOS, true)) {
    $ROLE = '';
}

?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($title) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>

.sidebar{width:240px}

@media (max-width:768px){
  .sidebar{
    position:fixed;
    z-index:50;
    transform:translateX(-100%);
  }
  .sidebar.active{
    transform:translateX(0);
  }
}

@media (min-width:768px){
  .sidebar{
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    transform: none !important;
    background: #ffffff;
  }

  .main-shift{
    margin-left: 240px;
  }
}

</style>

</head>

<body class="bg-gray-50">

<button class="md:hidden fixed top-4 left-4 z-50 bg-white p-2 rounded shadow sidebar-toggle">
<i class="fas fa-bars"></i>
</button>

<div class="flex min-h-screen">

<!-- SIDEBAR -->
<aside class="sidebar bg-white border-r shadow-md flex flex-col">

<!-- LOGO -->
<div class="p-4 border-b flex items-center space-x-3">

<img src="../img/logo-beatcell.png"
     class="w-8 h-8 object-contain"
     alt="Logo">

<div>
<h1 class="text-xl font-bold tracking-wide">

<span class="text-gray-800">
SISTEMA
</span>

<span class="bg-gradient-to-r from-[#9b00ff] to-[#ff8c00] bg-clip-text text-transparent">
BEATCELL
</span>

</h1>

<p class="text-xs text-gray-500">
Sistema Académico
</p>

</div>

</div>

<!-- MENÚ -->
<nav class="p-3 space-y-1 flex-1 overflow-y-auto">

<!-- PANEL ESTADÍSTICO -->
<?php if (in_array($ROLE, ['ADMINISTRADOR','SECRETARIO','ASISTENTE'], true)): ?>

<a href="<?= $base ?>/home.php"
class="flex items-center gap-3 px-3 py-2 rounded
<?= $active==='home'
? 'bg-gray-200 text-gray-900'
: 'text-gray-600 hover:bg-gray-100' ?>">

<i class="fas fa-gauge"></i>
<span>Panel Estadístico</span>

</a>

<?php endif; ?>


<!-- CENTRO DE CONTROL -->
<a href="<?= $base ?>/ventana.php"
class="flex items-center gap-3 px-3 py-2 rounded
<?= $active==='ventana'
? 'bg-gray-200 text-gray-900'
: 'text-gray-600 hover:bg-gray-100' ?>">

<i class="fas fa-window-restore"></i>
<span>Centro de Control</span>

</a>


<!-- REGISTRO ALUMNOS -->
<?php if (in_array($ROLE, ['ADMINISTRADOR','ASISTENTE','SECRETARIO'], true)): ?>

<a href="<?= $base ?>/registro_alumnos.php"
class="flex items-center gap-3 px-3 py-2 rounded
<?= $active==='registro'
? 'bg-gray-200 text-gray-900'
: 'text-gray-600 hover:bg-gray-100' ?>">

<i class="fas fa-user-plus"></i>
<span>Registro Alumnos</span>

</a>

<?php endif; ?>


<!-- PRACTICANTES -->
<?php if ($ROLE === 'ADMINISTRADOR'): ?>


<a href="<?= $base ?>/registrar_usuario.php"
class="flex items-center gap-3 px-3 py-2 rounded
<?= $active==='registrar_usuario'
? 'bg-gray-200 text-gray-900'
: 'text-gray-600 hover:bg-gray-100' ?>">

<i class="fas fa-user-cog"></i>
<span>Practicantes</span>

</a>

<?php endif; ?>


<!-- CONTROL CUOTAS -->
<?php if (in_array($ROLE, ['ADMINISTRADOR','SECRETARIO'], true)): ?>

<a href="<?= $base ?>/control_cuotas.php"
class="flex items-center gap-3 px-3 py-2 rounded
<?= $active==='consulta'
? 'bg-gray-200 text-gray-900'
: 'text-gray-600 hover:bg-gray-100' ?>">

<i class="fas fa-money-bill-wave"></i>
<span>Control de cuotas</span>

</a>

<?php endif; ?>


<!-- CURSOS -->
<?php if (in_array($ROLE, ['ADMINISTRADOR','ASISTENTE','SECRETARIO'], true)): ?>

<a href="<?= $base ?>/asignar_cursos.php"
class="flex items-center gap-3 px-3 py-2 rounded
<?= $active==='cursos'
? 'bg-gray-200 text-gray-900'
: 'text-gray-600 hover:bg-gray-100' ?>">

<i class="fas fa-book"></i>
<span>Cursos</span>

</a>

<?php endif; ?>


<!-- ASISTENCIA -->
<?php if (in_array($ROLE, ['ADMINISTRADOR','SECRETARIO'], true)): ?>

<a href="<?= $base ?>/control_asistencias.php"
class="flex items-center gap-3 px-3 py-2 rounded
<?= $active==='editar_registros'
? 'bg-gray-200 text-gray-900'
: 'text-gray-600 hover:bg-gray-100' ?>">

<i class="fas fa-calendar-check"></i>
<span>Asistencia</span>

</a>

<?php endif; ?>


<!-- PERFIL -->
<a href="<?= $base ?>/perfil.php"
class="flex items-center gap-3 px-3 py-2 rounded
<?= $active==='perfil'
? 'bg-gray-200 text-gray-900'
: 'text-gray-600 hover:bg-gray-100' ?>">

<i class="fas fa-user-circle"></i>
<span>Perfil</span>

</a>

</nav>


<!-- LOGOUT -->
<div class="p-3 mt-auto">

<a href="<?= $base ?>/logout.php"
class="inline-flex items-center gap-2 px-4 py-2 rounded-md
text-red-600 hover:bg-red-100 transition-all
cursor-pointer shadow-sm w-full justify-start">

<i class="fas fa-right-from-bracket"></i>
<span>Cerrar sesión</span>

</a>

</div>

</aside>


<!-- CONTENIDO -->
<main class="main-shift flex-1 bg-gray-50 min-h-screen overflow-x-hidden overflow-y-auto p-6">

<div class="w-[95%] max-w-[1600px] mx-auto">

<?= $content ?? '' ?>

</div>

</main>

</div>

<script>

document.querySelector('.sidebar-toggle')
?.addEventListener('click', () => {
document.querySelector('.sidebar')
?.classList.toggle('active');
});

</script>

</body>
</html>


