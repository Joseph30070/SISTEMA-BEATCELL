<?php
require_once __DIR__ . '/../config/auth.php';

$pdo = require __DIR__ . '/../config/db.php';

// 🔐 Permitir todos los roles del sistema
checkRole(['ADMINISTRADOR', 'SECRETARIO', 'ASISTENTE']);

$title  = "Perfil del Usuario";
$active = "perfil";

// =========================
// OBTENER DATOS USUARIO
// =========================
$stmt = $pdo->prepare("
    SELECT id_usuario, nombre, usuario, rol
    FROM usuarios
    WHERE id_usuario = ?
");

$stmt->execute([$_SESSION['id_usuario']]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Avatar (letra)
$letraAvatar = strtoupper(substr($usuario['nombre'], 0, 1));

// =========================
// CONFIGURACIÓN POR ROL
// =========================
$roleLabel = strtoupper($usuario['rol'] ?? '');

$roleColor = 'bg-gray-500';
$roleDescripcion = '';
$headerColor = 'from-gray-500 to-gray-400';
$rutaImagen = null;

switch ($roleLabel) {

    case 'ADMINISTRADOR':
        $roleColor = 'bg-red-600';
        $headerColor = 'from-red-700 via-red-500 to-orange-500';
        $roleDescripcion = 'Acceso completo al sistema. Control total de usuarios, cursos y reportes.';
        $rutaImagen = '../img/admin.jpg';
    break;

    case 'SECRETARIO':
        $roleColor = 'bg-blue-600';
        $headerColor = 'from-blue-700 via-blue-500 to-cyan-400';
        $roleDescripcion = 'Gestiona alumnos, asistencia y mantiene el orden del sistema.';
        $rutaImagen = '../img/secretario.jpg';
    break;

    case 'ASISTENTE':
        $roleColor = 'bg-green-600';
        $headerColor = 'from-green-600 via-emerald-400 to-lime-300';
        $roleDescripcion = 'Apoya en el registro de asistencia y consulta de información.';
        $rutaImagen = '../img/asistente.jpg';
    break;
}

ob_start();
?>

<div class="max-w-5xl mx-auto mt-10 animate-fade-in">

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100 transition hover:shadow-2xl duration-300">

        <!-- ENCABEZADO -->
        <div class="bg-gradient-to-r <?= $headerColor ?> px-10 py-8 text-white relative">

            <!-- LOGOUT -->
            <div class="absolute top-4 right-4">
                <a href="logout.php"
                   class="bg-white/20 hover:bg-white/30 text-white text-sm px-4 py-1.5 rounded-full transition">
                    Cerrar Sesión
                </a>
            </div>

            <div class="flex items-center gap-6">

                <!-- AVATAR -->
                <div class="w-32 h-32 rounded-full bg-white/10 flex items-center justify-center overflow-hidden border-4 border-white/40 shadow-xl hover:scale-105 transition duration-300">

                    <?php if ($rutaImagen): ?>

                        <img src="<?= htmlspecialchars($rutaImagen) ?>"
                             class="w-full h-full object-cover scale-110">

                    <?php else: ?>

                        <span class="text-4xl font-bold">
                            <?= $letraAvatar ?>
                        </span>

                    <?php endif; ?>

                </div>

                <div>

                    <h1 class="text-3xl font-bold">
                        <?= htmlspecialchars($usuario['nombre']) ?>
                    </h1>

                    <p class="text-sm opacity-90">
                        Usuario:
                        <?= htmlspecialchars($usuario['usuario']) ?>
                    </p>

                    <div class="mt-2">
                        <span class="px-3 py-1 rounded-full text-xs text-white <?= $roleColor ?>">
                            <?= $roleLabel ?>
                        </span>
                    </div>

                    <!-- DESCRIPCIÓN -->
                    <p class="text-xs opacity-80 italic mt-2">
                        <?= $roleDescripcion ?>
                    </p>

                </div>

            </div>

        </div>

        <!-- CUERPO -->
        <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- INFO PERSONAL -->
            <div class="md:col-span-2 bg-gray-50 p-6 rounded-xl border hover:shadow-lg transition duration-300">

                <h2 class="text-lg font-semibold mb-4">
                    Información Personal
                </h2>

                <div class="space-y-4 text-sm">

                    <div class="hover:translate-x-1 transition">

                        <span class="text-gray-500">
                            Nombre:
                        </span><br>

                        <strong>
                            <?= htmlspecialchars($usuario['nombre']) ?>
                        </strong>

                    </div>

                    <div class="hover:translate-x-1 transition">

                        <span class="text-gray-500">
                            Usuario:
                        </span><br>

                        <strong>
                            <?= htmlspecialchars($usuario['usuario']) ?>
                        </strong>

                    </div>

                </div>

                <!-- PERMISOS -->
                <div class="mt-6 bg-white border rounded-xl p-5 hover:shadow-md transition duration-300">

                    <h3 class="font-semibold mb-2">
                        Permisos del Rol
                    </h3>

                    <p class="text-sm text-gray-600 mb-3">
                        <?= $roleDescripcion ?>
                    </p>

                    <ul class="text-sm list-disc pl-5 space-y-1">

                        <?php if($roleLabel === 'ADMINISTRADOR'): ?>

                            <li>Gestionar usuarios</li>
                            <li>Crear cursos y grupos</li>
                            <li>Editar horarios</li>
                            <li>Ver reportes completos</li>

                        <?php elseif($roleLabel === 'SECRETARIO'): ?>

                            <li>Registrar alumnos</li>
                            <li>Tomar asistencia</li>
                            <li>Ver historial</li>

                        <?php elseif($roleLabel === 'ASISTENTE'): ?>

                            <li>Registrar asistencia</li>
                            <li>Consultar información</li>

                        <?php endif; ?>

                    </ul>

                </div>

            </div>

            <!-- PANEL DERECHO -->
            <div class="bg-white p-6 rounded-xl border flex flex-col justify-between hover:shadow-lg transition duration-300">

                <div>

                    <h2 class="text-lg font-semibold mb-4">
                        Estado de Cuenta
                    </h2>

                    <div class="mb-4">

                        <span class="text-gray-500 text-sm">
                            Estado:
                        </span><br>

                        <span class="text-green-600 font-semibold">
                            Activo
                        </span>

                    </div>

                    <div>

                        <span class="text-gray-500 text-sm">
                            Rol:
                        </span><br>

                        <strong>
                            <?= $roleLabel ?>
                        </strong>

                    </div>

                </div>

                <?php if($roleLabel === 'ADMINISTRADOR'): ?>

                    <div class="mt-6">

                        <!-- BOTÓN BACKUP -->
                        <button
                            onclick="abrirModalBackup()"
                            class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 hover:scale-105 transition duration-200">

                            Hacer Backup DB

                        </button>

                        <!-- RECORDATORIO -->
                        <div class="mt-3 bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs p-3 rounded-lg">

                            ⚠️ Recuerda realizar un backup de la base de datos mínimo una vez por semana para evitar pérdida de información.

                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

<!-- MODAL BACKUP -->

<div id="modalBackup"
     class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[9999]">

    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md animate-fade-in">

        <h2 class="text-xl font-bold mb-4 text-gray-800">
            Confirmar Credenciales
        </h2>

        <p class="text-sm text-gray-600 mb-4">
            Debes ingresar tus credenciales de administrador para continuar con el backup.
        </p>

        <form action="../process/process_backup_db.php" method="POST">

            <!-- USUARIO -->
            <div class="mb-3">

                <label class="block text-sm font-semibold mb-1">
                    Usuario
                </label>

                <input type="text"
                       name="usuario"
                       required
                       autocomplete="off"
                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">

            </div>

            <!-- PASSWORD -->
            <div class="mb-4">

                <label class="block text-sm font-semibold mb-1">
                    Contraseña
                </label>

                <input type="password"
                       name="password"
                       required
                       autocomplete="off"
                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">

            </div>

            <!-- BOTONES -->
            <div class="flex justify-end gap-2">

                <button type="button"
                        onclick="cerrarModalBackup()"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">

                    Cancelar

                </button>

                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">

                    Confirmar Backup

                </button>

            </div>

        </form>

    </div>

</div>

<!-- SCRIPT -->

<script>

function abrirModalBackup(){

    let modal =
        document.getElementById('modalBackup');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function cerrarModalBackup(){

    let modal =
        document.getElementById('modalBackup');

    modal.classList.add('hidden');
    modal.classList.remove('flex');

    // LIMPIAR FORMULARIO
    document
        .querySelector('#modalBackup form')
        .reset();
}

// CERRAR SI HACEN CLICK FUERA
window.addEventListener('click', function(e){

    let modal =
        document.getElementById('modalBackup');

    if(e.target === modal){

        cerrarModalBackup();
    }
});

</script>

<?php
$content = ob_get_clean();

require __DIR__ . '/layout.php';
?>
