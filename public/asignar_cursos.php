<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR']);

$pdo = require __DIR__ . '/../config/db.php';

// Obtener cursos
$stmt = $pdo->query("SELECT * FROM cursos ORDER BY id_curso DESC");
$cursos = $stmt->fetchAll();

// Obtener grupos con su curso
$stmt = $pdo->query("
SELECT g.*, c.nombre_curso
FROM grupos g
INNER JOIN cursos c ON c.id_curso = g.id_curso
WHERE g.id_grupo NOT IN (
    SELECT id_grupo FROM horarios_especiales
)
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
.tab-btn{
    padding:10px 15px;
    border-radius:8px;
    cursor:pointer;
    background:#e5e7eb;
    margin-right:5px;
}
.tab-btn.active{
    background:#0f766e;
    color:white;
}
.hidden{display:none;}
.day-card{
    border:1px solid #ddd;
    padding:10px;
    border-radius:10px;
    transition:0.3s;
}
.day-card.active{
    border-color:#0f766e;
    background:#ecfeff;
}
</style>

<h2 class="text-3xl font-bold mb-4">Cursos y Grupos</h2>

<!-- TABS -->
<div class="mb-4">
    <button class="tab-btn active" onclick="showTab('tab1', this)">Normal</button>
    <button class="tab-btn" onclick="showTab('tab2', this)">Horarios especiales</button>
</div>

<!-- ================= TAB 1 (TU CÓDIGO ORIGINAL SIN CAMBIOS) ================= -->
<div id="tab1">

    <!-- CREAR CURSO -->
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

    <!-- LISTA CURSOS (RESTAURADO) -->
    <div class="card">
        <h3 class="font-semibold mb-3">Cursos Registrados</h3>

        <table class="w-full border rounded overflow-hidden">
            <thead class="bg-teal-600 text-white">
            <tr>
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
                <tr><td colspan="2" class="p-2 text-center">No hay cursos</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

<!-- CREAR GRUPO -->
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

<!-- TABLA GRUPOS (RESTAURADA) -->
<div class="card">
  <h3 class="font-semibold mb-3">Grupos Registrados</h3>

  <table class="w-full border rounded overflow-hidden">
    <thead class="bg-blue-600 text-white">
      <tr>
        <th class="p-3 text-left">Curso</th>
        <th class="p-3 text-left">Grupo</th>
        <th class="p-3 text-left">Días</th>
        <th class="p-3 text-left">Horario</th>
      </tr>
    </thead>

    <tbody class="bg-white">
    <?php foreach($grupos as $g): ?>
        <tr class="border-t">
            <td class="p-2"><?= $g['nombre_curso'] ?></td>
            <td class="p-2"><?= $g['nombre_grupo'] ?></td>
            <td class="p-2"><?= $g['dias'] ?></td>
            <td class="p-2">
                <?= substr($g['hora_inicio'],0,5) ?> - <?= substr($g['hora_fin'],0,5) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ========================= -->
<!-- HORARIOS ESPECIALES (TABLA) -->
<!-- ========================= -->
<div class="card mt-6">

<h3 class="font-semibold mb-3 text-blue-600">
Horarios Especiales
</h3>

<?php
$stmt = $pdo->query("
    SELECT 
        he.*,
        g.nombre_grupo,
        c.nombre_curso
    FROM horarios_especiales he
    INNER JOIN grupos g ON g.id_grupo = he.id_grupo
    INNER JOIN cursos c ON c.id_curso = g.id_curso
    ORDER BY g.id_grupo, he.dia_semana
");

$data = $stmt->fetchAll();


// =========================
// AGRUPAR POR GRUPO (FIX VARIABLE)
// =========================
$horariosEspeciales = [];

foreach($data as $d){

    $key = $d['nombre_curso'].'-'.$d['nombre_grupo'];

    if(!isset($horariosEspeciales[$key])){

        $horariosEspeciales[$key] = [
            'curso' => $d['nombre_curso'],
            'grupo' => $d['nombre_grupo'],
            'horarios' => []
        ];
    }

    $horariosEspeciales[$key]['horarios'][] =
        $d['dia_semana'].' '.
        substr($d['hora_inicio'],0,5).' - '.
        substr($d['hora_fin'],0,5);
}
?>


<table class="w-full border rounded overflow-hidden">

<thead class="bg-blue-700 text-white">
<tr>
    <th class="p-2">Curso</th>
    <th class="p-2">Grupo</th>
    <th class="p-2">Horarios</th>
</tr>
</thead>

<tbody>

<?php foreach($horariosEspeciales as $g): ?>

<tr class="border-t">

    <td class="p-2">
        <?= htmlspecialchars($g['curso']) ?>
    </td>

    <td class="p-2">
        <?= htmlspecialchars($g['grupo']) ?>
    </td>

    <td class="p-2 text-sm text-gray-700">

        <?php foreach($g['horarios'] as $h): ?>
            <div><?= $h ?></div>
        <?php endforeach; ?>

    </td>

</tr>

<?php endforeach; ?>

</tbody>
</table>

</div>
</div> <!-- CIERRE DE TAB 1 -->


<!-- ========================= -->
<!-- TAB 2 (HORARIOS ESPECIALES PRO) -->
<!-- ========================= -->
<div id="tab2" class="hidden">

<div class="card">

<h3 class="font-semibold mb-4 text-lg text-blue-600">
Crear Horario Especial
</h3>

<!-- CURSO -->
<select id="curso_especial"
        class="border p-2 w-full mb-3"
        onchange="loadGruposEspecial(this.value)">

<option value="">Seleccione curso</option>

<?php foreach($cursos as $c): ?>
<option value="<?= $c['id_curso'] ?>">
    <?= htmlspecialchars($c['nombre_curso']) ?>
</option>
<?php endforeach; ?>

</select>

<!-- NOMBRE GRUPO -->
<input type="text"
       id="nombre_grupo_especial"
       class="border p-2 w-full mb-4"
       placeholder="Nombre del nuevo grupo">

<!-- ================= DÍAS ================= -->
<div class="grid gap-3">

<?php
$dias = ["Lunes","Martes","Miércoles","Jueves","Viernes","Sábado","Domingo"];

foreach($dias as $d):
?>

<div class="day-card bg-white border rounded-lg p-4 shadow-sm transition-all duration-300 hover:shadow-md">

    <!-- HEADER -->
    <div class="flex justify-between items-center">

        <label class="font-semibold text-gray-700">
            <input type="checkbox"
                   class="mr-2"
                   onchange="toggleDayEspecial('<?= $d ?>')">
            <?= $d ?>
        </label>

        <span id="preview-<?= $d ?>"
              class="text-xs text-gray-400">
            Sin horario
        </span>

    </div>

    <!-- INPUTS -->
    <div id="inputs-<?= $d ?>"
         class="hidden mt-3 space-y-2">

        <input type="time"
               id="inicio-<?= $d ?>"
               class="border p-2 w-full rounded">

        <input type="time"
               id="fin-<?= $d ?>"
               class="border p-2 w-full rounded">

    </div>

</div>

<?php endforeach; ?>

</div>

<!-- BOTÓN -->
<button onclick="guardarHorarioEspecial()"
        class="mt-5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full transition">

    Guardar Horario Especial

</button>

</div>

</div>




<script>

function showTab(tab, btn){
    document.getElementById('tab1').classList.add('hidden');
    document.getElementById('tab2').classList.add('hidden');

    document.getElementById(tab).classList.remove('hidden');

    document.querySelectorAll('.tab-btn').forEach(b=>{
        b.classList.remove('active');
    });

    btn.classList.add('active');
}

function loadGrupos(idCurso){
    let select = document.getElementById('grupo_especial');
    select.innerHTML = '<option>Cargando...</option>';

    fetch(`../process/get_grupos.php?id_curso=${idCurso}`)
    .then(r=>r.json())
    .then(data=>{
        select.innerHTML = '<option value="">Seleccione grupo</option>';
        data.grupos.forEach(g=>{
            let opt = document.createElement('option');
            opt.value = g.id_grupo;
            opt.textContent = g.nombre_grupo;
            select.appendChild(opt);
        });
    });
}

function toggleDayEspecial(dia){

    let box = document.getElementById("inputs-" + dia);
    let preview = document.getElementById("preview-" + dia);

    let inicio = document.getElementById("inicio-" + dia);
    let fin = document.getElementById("fin-" + dia);

    if(box.classList.contains("hidden")){
        box.classList.remove("hidden");

        // animación suave
        box.style.opacity = 0;
        box.style.transform = "translateY(-5px)";

        setTimeout(() => {
            box.style.transition = "0.3s";
            box.style.opacity = 1;
            box.style.transform = "translateY(0)";
        }, 10);

    } else {
        box.classList.add("hidden");

        // reset preview
        preview.textContent = "Sin horario";

        inicio.value = "";
        fin.value = "";
        return;
    }

    // actualizar preview en tiempo real
    inicio.onchange = fin.onchange = function(){

        if(inicio.value && fin.value){
            preview.textContent = inicio.value + " - " + fin.value;
            preview.classList.add("text-blue-600");
            preview.classList.remove("text-gray-400");
        }
    };
}


function loadGruposEspecial(idCurso){

    let select = document.getElementById("grupo_especial");

    select.innerHTML = "<option>Cargando...</option>";

    if(!idCurso){
        select.innerHTML = "<option value=''>Seleccione grupo</option>";
        return;
    }

    fetch(`../process/get_grupos.php?id_curso=${idCurso}`)
        .then(res => res.json())
        .then(data => {

            if(data.success){

                select.innerHTML = "<option value=''>Seleccione grupo</option>";

                data.grupos.forEach(g => {

                    let option = document.createElement("option");
                    option.value = g.id_grupo;
                    option.textContent = g.nombre_grupo;

                    select.appendChild(option);

                });

            } else {
                select.innerHTML = "<option>Error al cargar</option>";
            }

        })
        .catch(() => {
            select.innerHTML = "<option>Error de conexión</option>";
        });
}


function guardarHorarioEspecial(){

    let idCurso = document.getElementById("curso_especial").value;
    let nombreGrupo = document.getElementById("nombre_grupo_especial").value;

    if(!idCurso){
        alert("Selecciona un curso");
        return;
    }

    if(nombreGrupo.trim() === ""){
        alert("Escribe un nombre de grupo");
        return;
    }

    let diasSeleccionados = [];

    let formData = new FormData();

    formData.append("id_curso", idCurso);
    formData.append("nombre_grupo", nombreGrupo);

    // =========================
    // RECORRER DÍAS
    // =========================
    const dias = ["Lunes","Martes","Miércoles","Jueves","Viernes","Sábado","Domingo"];

    dias.forEach(dia => {

        let checkbox = document.querySelector(`input[onchange*="${dia}"]`);
        let inicio = document.getElementById("inicio-" + dia);
        let fin = document.getElementById("fin-" + dia);

        if(checkbox && checkbox.checked){

            if(!inicio.value || !fin.value){
                alert(`Completa horario en ${dia}`);
                return;
            }

            formData.append("dias[]", dia);
            formData.append(`hora_inicio[${dia}]`, inicio.value);
            formData.append(`hora_fin[${dia}]`, fin.value);

            diasSeleccionados.push(dia);
        }
    });

    if(diasSeleccionados.length === 0){
        alert("Selecciona al menos un día");
        return;
    }

    // =========================
    // ENVIAR A PHP
    // =========================
    fetch("../process/process_horarios_especiales.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(resp => {

        // redirigir o recargar
        window.location.href = "../public/asignar_cursos.php?success=Horario especial creado correctamente";

    })
    .catch(err => {

        alert("Error al guardar horario");

    });

}



</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>

