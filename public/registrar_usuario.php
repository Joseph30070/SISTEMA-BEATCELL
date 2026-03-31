<?php
require_once __DIR__ . '/../config/auth.php';
require __DIR__ . '/../config/db.php';

checkRole(['ADMINISTRADOR']);

$title = 'Gestión de Practicantes';
$active = 'practicantes';

ob_start();
?>

<h2 class="text-3xl font-bold text-gray-800 mb-2">
  Gestión de Practicantes
</h2>

<p class="text-gray-600 mb-6">
  Administra los practicantes del sistema: registro, actualización y eliminación.
</p>

<!-- MENSAJES -->
<?php if(isset($_GET['msg'])): ?>
  <div class="bg-green-50 text-green-700 p-3 rounded-lg border border-green-200 mb-4 text-sm text-center">
    <?= htmlspecialchars($_GET['msg']) ?>
  </div>
<?php elseif(isset($_GET['error'])): ?>
  <div class="bg-red-50 text-red-700 p-3 rounded-lg border border-red-200 mb-4 text-sm text-center">
    <?= htmlspecialchars($_GET['error']) ?>
  </div>
<?php endif; ?>

<!-- TABS -->
<ul class="flex gap-2 mb-6">
  <li>
    <button class="tab-btn px-5 py-2 rounded-t-lg font-semibold bg-white text-green-600 shadow border border-b-0"
            data-tab="registrar">
      Registrar Practicante
    </button>
  </li>
  <li>
    <button class="tab-btn px-5 py-2 rounded-t-lg font-semibold bg-gray-100 text-gray-600 hover:bg-gray-200 transition"
            data-tab="gestionar">
      Gestionar Practicantes
    </button>
  </li>
</ul>

<!-- CONTENIDO -->
<div id="tab-registrar" class="tab-content">
  <?php include __DIR__ . '/registrar_nuevo_usuario.php'; ?>
</div>

<div id="tab-gestionar" class="tab-content hidden">
  <?php include __DIR__ . '/gestionar_usuarios.php'; ?>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(button => {
  button.addEventListener('click', function() {

    const tab = this.getAttribute('data-tab');

    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.getElementById(`tab-${tab}`).classList.remove('hidden');

    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.classList.remove('bg-white','text-green-600','shadow','border','border-b-0');
      btn.classList.add('bg-gray-100','text-gray-600');
    });

    this.classList.add('bg-white','text-green-600','shadow','border','border-b-0');
  });
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
