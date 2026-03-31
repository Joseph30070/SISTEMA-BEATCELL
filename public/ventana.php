<?php
require_once __DIR__ . '/../config/auth.php';

$title  = 'Panel Principal';
$active = 'ventana';

// Normaliza el rol
$ROLE = strtoupper($_SESSION['role'] ?? '');

ob_start(); 
?>



<h2 class="text-3xl font-bold text-gray-800 mb-6">
    Bienvenido al Sistema de Gestión Académica
</h2>

<p class="text-gray-600 mb-6">
    Administra alumnos, asistencia, cursos y pagos desde este panel principal.
</p>

<div class="grid md:grid-cols-3 gap-4">

    <!-- REGISTRAR ALUMNOS -->
    <?php if (in_array($ROLE, ['ADMINISTRADOR','ASESOR'], true)): ?>
        <a href="registro_alumnos.php" class="block bg-white rounded shadow p-6 hover:shadow-md transition">
            <div class="text-teal-600 text-3xl mb-3">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h3 class="font-semibold">Registrar Alumno</h3>
            <p class="text-sm text-gray-600">
                Registrar nuevos alumnos en el sistema.
            </p>
        </a>   
    <?php endif; ?>

    <!-- INFORMACIÓN ALUMNOS -->
    <?php if (in_array($ROLE, ['ADMINISTRADOR','ADMISION'], true)): ?>
        <a href="registro.php?tab=info" class="block bg-white rounded shadow p-6 hover:shadow-md transition">
            <div class="text-blue-600 text-3xl mb-3">
                <i class="fas fa-id-card"></i>
            </div>
            <h3 class="font-semibold">Información de Alumnos</h3>
            <p class="text-sm text-gray-600">
                Consulta datos y estado de alumnos.
            </p>
        </a>
    <?php endif; ?>

    <!-- PRACTICANTES -->
    <?php if ($ROLE === 'ADMINISTRADOR'): ?>
    <a href="registrar_usuario.php" class="block bg-white rounded shadow p-6 hover:shadow-md transition">
        <div class="text-gray-700 text-3xl mb-3">
            <i class="fas fa-user-tie"></i>
        </div>
        <h3 class="font-semibold">Practicantes</h3>
        <p class="text-sm text-gray-600">
            Gestión y consulta de practicantes.
        </p>
    </a>
    <?php endif; ?>
    
    <!-- CUOTAS -->
    <?php if ($ROLE === 'ADMINISTRADOR'): ?>
        <a href="registrar_usuario.php" class="block bg-white rounded shadow p-6 hover:shadow-md transition">
            <div class="text-green-600 text-3xl mb-3">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <h3 class="font-semibold">Control de Cuotas</h3>
            <p class="text-sm text-gray-600">
                Gestión de pagos y control de deudas.
            </p>
        </a>    

    <!-- CURSOS / GRUPOS -->
        <a href="asignar_cursos.php" class="block bg-white rounded shadow p-6 hover:shadow-md transition">
            <div class="text-purple-600 text-3xl mb-3">
                <i class="fas fa-book-open"></i>
            </div>
            <h3 class="font-semibold">Asignar Cursos</h3>
            <p class="text-sm text-gray-600">
                Gestión de cursos, grupos y horarios.
            </p>
        </a>

    <!-- ASISTENCIA -->
        <a href="editar_registros.php" class="block bg-white rounded shadow p-6 hover:shadow-md transition">
            <div class="text-orange-600 text-3xl mb-3">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h3 class="font-semibold">Control de Asistencia</h3>
            <p class="text-sm text-gray-600">
                Registro y control de asistencia diaria.
            </p>
        </a>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>