<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR']);

$pdo = require __DIR__ . '/../config/db.php';

// Obtener cursos
$stmt = $pdo->query("SELECT * FROM cursos ORDER BY id_curso DESC");
$cursos = $stmt->fetchAll();

// Obtener grupos con su curso
$stmt = $pdo->query("
SELECT 
    g.*, 
    c.nombre_curso
FROM grupos g
INNER JOIN cursos c ON c.id_curso = g.id_curso
ORDER BY g.id_grupo DESC
");

$grupos = $stmt->fetchAll();

$title = "Cursos y Grupos";
$active = "cursos";

ob_start();
?>

<style>
.card{
    background:white;
    padding:20px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    margin-bottom:20px;
}
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:15px;
}
.badge{
    background:#2563eb;
    color:white;
    padding:3px 8px;
    border-radius:10px;
}
</style>

<h2 class="text-3xl font-bold mb-4">Cursos y Grupos</h2>
<p class="text-gray-600 mb-6">Gestión completa de cursos y asignación de grupos</p>

<!-- MENSAJES -->
<?php if(isset($_GET['success'])): ?>
<div id="alerta" class="fixed top-5 right-5 bg-green-500 text-white px-5 py-3 rounded shadow-lg z-50">
    <?= htmlspecialchars($_GET['success']) ?>
</div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
<div id="alerta" class="fixed top-5 right-5 bg-red-500 text-white px-5 py-3 rounded shadow-lg z-50">
    <?= htmlspecialchars($_GET['error']) ?>
</div>
<?php endif; ?>

<!-- ========================= -->
<!-- CREAR CURSO -->
<!-- ========================= -->
<div class="card">
  <h3 class="font-semibold mb-2">Nuevo Curso</h3>

  <form action="../process/process_cursos.php" method="POST">
    <input type="text" name="nombre" placeholder="Ej: Programación Web"
           class="border px-3 py-2 rounded w-full mb-2">

    <button class="bg-teal-600 text-white px-4 py-2 rounded">
      Agregar Curso
    </button>
  </form>
</div>

<!-- ========================= -->
<!-- LISTA DE CURSOS -->
<!-- ========================= -->
<div class="card">
  <h3 class="font-semibold mb-3">Cursos Registrados</h3>

  <table class="w-full border rounded overflow-hidden">
    <thead class="bg-teal-600 text-white">
      <tr class="border-t hover:bg-gray-50">
        <th class="p-3 text-left">ID</th>
        <th class="p-3 text-left">Nombre Curso</th>
      </tr>
    </thead>

    <tbody class="bg-white">
      <?php if($cursos): ?>
          <?php foreach($cursos as $c): ?>
              <tr class="border-t hover:bg-gray-50">
                  <td class="p-2"><?= $c['id_curso'] ?></td>
                  <td class="p-2"><?= htmlspecialchars($c['nombre_curso']) ?></td>
              </tr>
          <?php endforeach; ?>
      <?php else: ?>
          <tr class="border-t hover:bg-gray-50">
              <td colspan="2" class="p-2 text-center text-gray-500">
                  No hay cursos registrados
              </td>
          </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- ========================= -->
<!-- CREAR GRUPO -->
<!-- ========================= -->
<form action="../process/process_grupos.php" method="POST" class="card">

  <h3 class="font-semibold mb-3">Crear Grupo</h3>

  <div class="grid">

    <select name="id_curso" class="border px-3 py-2 rounded" required>
        <option value="">Seleccione curso</option>

        <?php foreach($cursos as $c): ?>
            <option value="<?= $c['id_curso'] ?>">
                <?= htmlspecialchars($c['nombre_curso']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="nombre_grupo" placeholder="Grupo A"
           class="border px-3 py-2 rounded" required>

    <input type="time" name="hora_inicio" class="border px-3 py-2 rounded" required>
    <input type="time" name="hora_fin" class="border px-3 py-2 rounded" required>
  </div>

  <div class="mt-3 flex flex-wrap gap-3">
    <label><input type="checkbox" name="dias[]" value="Lunes"> Lunes</label>
    <label><input type="checkbox" name="dias[]" value="Martes"> Martes</label>
    <label><input type="checkbox" name="dias[]" value="Miércoles"> Miércoles</label>
    <label><input type="checkbox" name="dias[]" value="Jueves"> Jueves</label>
    <label><input type="checkbox" name="dias[]" value="Viernes"> Viernes</label>
    <label><input type="checkbox" name="dias[]" value="Sábado"> Sábado</label>
    <label><input type="checkbox" name="dias[]" value="Domingo"> Domingo</label>
  </div>

  <button class="mt-4 bg-teal-600 text-white px-4 py-2 rounded">
    Crear Grupo
  </button>

</form>
 

<!-- ========================= -->
<!-- TABLA GRUPOS -->
<!-- ========================= -->
<div class="card">
  <h3 class="font-semibold mb-3">Grupos Registrados</h3>

  <table class="w-full border rounded overflow-hidden">
    <thead class="bg-blue-600 text-white">
      <tr>
        <th class="p-3 text-left">Curso</th>
        <th class="p-3 text-left">Grupo</th>
        <th class="p-3 text-left">Días</th>
        <th class="p-3 text-left">Horario</th>
        <th class="p-3 text-left">Acciones</th>
      </tr>
    </thead>

    <tbody class="bg-white">
    <?php if($grupos): ?>
        <?php foreach($grupos as $g): ?>
            <tr class="border-t hover:bg-gray-50">
                <td class="p-2"><?= htmlspecialchars($g['nombre_curso']) ?></td>

                <td class="p-2">
                    <span class="badge"><?= htmlspecialchars($g['nombre_grupo']) ?></span>
                </td>

                <td class="p-2"><?= htmlspecialchars($g['dias']) ?></td>

                <td class="p-2">
                    <?= substr($g['hora_inicio'], 0, 5) ?> - <?= substr($g['hora_fin'], 0, 5) ?>
                </td>

                <td class="p-2 flex gap-2">

                    <!-- EDITAR -->
                    <button class="bg-yellow-400 px-2 py-1 rounded text-white text-sm">
                        Editar
                    </button>

                    <!-- ELIMINAR -->
                    <form action="../process/process_grupos.php" method="POST"
                          onsubmit="return confirm('¿Eliminar grupo?')">

                        <input type="hidden" name="id_grupo" value="<?= $g['id_grupo'] ?>">
                        <input type="hidden" name="action" value="delete">

                        <button class="bg-red-500 px-2 py-1 rounded text-white text-sm">
                            Eliminar
                        </button>
                    </form>

                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="5" class="p-3 text-center text-gray-500">
                Aún no hay grupos registrados
            </td>
        </tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>



<script>
setTimeout(()=>{
    let alerta = document.getElementById("alerta");
    if(alerta){
        alerta.style.transition = "opacity 0.5s";
        alerta.style.opacity = "0";
        setTimeout(()=>alerta.remove(),500);
    }
},3000);
</script>


<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>