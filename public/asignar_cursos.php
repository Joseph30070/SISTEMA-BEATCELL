<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'SECRETARIO', 'ASISTENTE']);

$ROLE = $_SESSION['rol'] ?? '';

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

    <button class="tab-btn active" onclick="showTab('tab1', this)">
        Normal
    </button>

    <!-- SOLO ADMIN PUEDE ENTRAR A TAB 2 -->
    <button class="tab-btn <?php echo ($ROLE!='ADMINISTRADOR')?'opacity-50 cursor-not-allowed':''; ?>"
            <?php echo ($ROLE!='ADMINISTRADOR')?'disabled':''; ?>
            onclick="showTab('tab2', this)">
        Horarios especiales
    </button>

</div>

<!-- ================= TAB 1 (VISIBLE PARA TODOS) ================= -->
<div id="tab1">

    <!-- CREAR CURSO -->
    <div class="card">

        <h3 class="font-semibold mb-2">Nuevo Curso</h3>

        <!-- SOLO ADMIN Y SECRETARIO PUEDEN CREAR CURSOS -->
        <?php if(in_array($ROLE, ['ADMINISTRADOR','SECRETARIO'])): ?>
        <form action="../process/process_cursos.php" method="POST">
            <input type="text" name="nombre" placeholder="Ej: Programación Web"
                class="border px-3 py-2 rounded w-full mb-2">

            <button class="bg-teal-600 text-white px-4 py-2 rounded">
                Agregar Curso
            </button>
        </form>
        <?php else: ?>
            <p class="text-gray-500 text-sm">Solo lectura</p>
        <?php endif; ?>

    </div>

    <!-- LISTA CURSOS (TODOS PUEDEN VER) -->
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
            <?php foreach($cursos as $c): ?>
                <tr class="border-t hover:bg-gray-50">
                    <td class="p-2"><?= $c['id_curso'] ?></td>
                    <td class="p-2"><?= htmlspecialchars($c['nombre_curso']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>

    </div>

    <!-- CREAR GRUPO -->
    <?php if(in_array($ROLE, ['ADMINISTRADOR','SECRETARIO'])): ?>
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
    <?php else: ?>
        <p class="text-gray-500 text-sm px-4">No tienes permisos para crear grupos</p>
    <?php endif; ?>

    <!-- TABLA GRUPOS (TODOS VEN) -->
    <div class="card">

        <h3 class="font-semibold mb-3">Grupos Registrados</h3>

        <table class="w-full border rounded overflow-hidden">

            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="p-3 text-left">Curso</th>
                    <th class="p-3 text-left">Grupo</th>
                    <th class="p-3 text-left">Días</th>
                    <th class="p-3 text-left">Horario</th>
                    <th class="p-3 text-center">Acciones</th>
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

                    <td class="p-2 text-center">

                        <?php if($ROLE === 'ADMINISTRADOR'): ?>

                            <button onclick="abrirEditarGrupo(<?= $g['id_grupo'] ?>)"
                                    class="bg-yellow-500 text-white px-2 py-1 rounded">
                                Editar
                            </button>

                            <button onclick="eliminarGrupo(<?= $g['id_grupo'] ?>)"
                                    class="bg-red-600 text-white px-3 py-1 rounded text-sm">
                                Eliminar
                            </button>

                        <?php else: ?>
                            <span class="text-gray-400 text-xs">Solo lectura</span>
                        <?php endif; ?>

                    </td>

                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>

    </div>

</div>

<!-- ================= HORARIOS ESPECIALES ================= -->
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

    $horariosEspeciales = [];

    foreach($data as $d){

        $key = $d['nombre_curso'].'-'.$d['nombre_grupo'];

        if(!isset($horariosEspeciales[$key])){
            $horariosEspeciales[$key] = [
                'id_grupo' => $d['id_grupo'],
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
                <th class="p-2">Acciones</th>
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

                <td class="p-2 text-center">

                    <?php if($ROLE === 'ADMINISTRADOR'): ?>

                        <button onclick="editarHorarioEspecial(<?= $g['id_grupo'] ?>)"
                                class="bg-yellow-500 text-white px-2 py-1 rounded">
                            Editar
                        </button>

                        <button onclick="eliminarHorarioEspecial(<?= $g['id_grupo'] ?>)"
                                class="bg-red-600 text-white px-3 py-1 rounded text-sm">
                            Eliminar
                        </button>

                    <?php else: ?>
                        <span class="text-gray-400 text-xs">Solo lectura</span>
                    <?php endif; ?>

                </td>

            </tr>
        <?php endforeach; ?>
        </tbody>

    </table>

</div>



<!-- ================= TAB 2 (SOLO ADMIN) ================= -->
<?php if($ROLE === 'ADMINISTRADOR'): ?>
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

        <!-- DÍAS -->
        <div class="grid gap-3">

        <?php
        $dias = ["Lunes","Martes","Miércoles","Jueves","Viernes","Sábado","Domingo"];

        foreach($dias as $d):
        ?>

        <div class="day-card bg-white border rounded-lg p-4">

            <div class="flex justify-between items-center">

                <label>
                    <input type="checkbox"
                           onchange="toggleDayEspecial('<?= $d ?>')">
                    <?= $d ?>
                </label>

                <span id="preview-<?= $d ?>" class="text-xs text-gray-400">
                    Sin horario
                </span>

            </div>

            <div id="inputs-<?= $d ?>" class="hidden mt-2">

                <input type="time" id="inicio-<?= $d ?>" class="border p-2 w-full">
                <input type="time" id="fin-<?= $d ?>" class="border p-2 w-full mt-1">

            </div>

        </div>

        <?php endforeach; ?>

        </div>

        <button onclick="guardarHorarioEspecial()"
                class="mt-5 bg-blue-600 text-white px-4 py-2 rounded w-full">

            Guardar Horario Especial

        </button>

    </div>

</div>
<?php endif; ?>

<script>
    const USER_ROLE = "<?= $ROLE ?>";
</script>


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

// =========================
// FUNCIONES DE ELIMINACIÓN DE GRUPO
// =========================
function eliminarGrupo(id){

    if(!confirm("¿Seguro que deseas eliminar este grupo?")){
        return;
    }

    fetch("../process/eliminar_grupo.php", {
        method: "POST",
        headers:{
            "Content-Type":"application/x-www-form-urlencoded"
        },
        body: "id_grupo=" + id
    })
    .then(res => res.text())
    .then(resp => {

        alert("Grupo eliminado correctamente");
        location.reload();

    })
    .catch(() => {

        alert("Error al eliminar");

    });

}
// =========================
// FUNCIONES DE ELIMINACIÓN DE HORARIO ESPECIAL
// =========================

function eliminarHorarioEspecial(grupo){

    if(!confirm("¿Eliminar todos los horarios especiales de este grupo?")){
        return;
    }

    fetch("../process/eliminar_horario_especial.php", {
        method: "POST",
        headers:{
            "Content-Type":"application/x-www-form-urlencoded"
        },
        body: "grupo=" + encodeURIComponent(grupo)
    })
    .then(res => res.text())
    .then(resp => {

        alert("Horario especial eliminado");
        location.reload();

    })
    .catch(() => {

        alert("Error al eliminar");

    });

}

// =========================
// FUNCIONES DE EDICIÓN DE GRUPO
// =========================

function abrirEditarGrupo(id){

    fetch("../process/get_grupo_by_id.php?id_grupo=" + id)
    .then(res => res.json())
    .then(data => {

        if(!data.success){
            alert("Error al cargar grupo");
            return;
        }

        let g = data.grupo;

        document.getElementById("edit_id_grupo").value = g.id_grupo;
        document.getElementById("edit_nombre").value = g.nombre_grupo;
        document.getElementById("edit_inicio").value = g.hora_inicio;
        document.getElementById("edit_fin").value = g.hora_fin;

        // limpiar checkboxes
        document.querySelectorAll(".edit_dia").forEach(c => c.checked = false);

        // marcar días
        if(g.dias){
            let dias = g.dias.split(", ");
            dias.forEach(d => {
                document.querySelectorAll(".edit_dia").forEach(c => {
                    if(c.value === d) c.checked = true;
                });
            });
        }

        document.getElementById("modalEditar").classList.remove("hidden");
    });
}

function cerrarModal(){
    document.getElementById("modalEditar").classList.add("hidden");
}

function guardarEdicionGrupo(){

    let id = document.getElementById("edit_id_grupo").value;
    let nombre = document.getElementById("edit_nombre").value;
    let inicio = document.getElementById("edit_inicio").value;
    let fin = document.getElementById("edit_fin").value;

    let dias = [];
    document.querySelectorAll(".edit_dia:checked").forEach(c => {
        dias.push(c.value);
    });

    if(nombre === ""){
        alert("Nombre requerido");
        return;
    }

    let formData = new FormData();
    formData.append("id_grupo", id);
    formData.append("nombre_grupo", nombre);
    formData.append("hora_inicio", inicio);
    formData.append("hora_fin", fin);

    dias.forEach(d => formData.append("dias[]", d));

    fetch("../process/update_grupo.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if(data.success){
            alert("Grupo actualizado");
            location.reload();
        } else {
            alert("Error al actualizar");
        }

    });
}

// =========================
// FUNCIONES DE EDICIÓN DE HORARIO ESPECIAL
// =========================

function editarHorarioEspecial(id){

    fetch("../process/get_horario_especial.php?id_grupo=" + id)
    .then(res => res.json())
    .then(data => {

        if(!data.success){
            alert("Error");
            return;
        }

        document.getElementById("edit_especial_id").value = id;

        let container = document.getElementById("edit_dias_container");
        container.innerHTML = "";

        let dias = ["Lunes","Martes","Miércoles","Jueves","Viernes","Sábado","Domingo"];

        dias.forEach(dia => {

            let registro = data.data.find(d => d.dia_semana === dia);

            let inicio = registro ? registro.hora_inicio.slice(0,5) : "";
            let fin = registro ? registro.hora_fin.slice(0,5) : "";

            container.innerHTML += `
                <div class="border p-2 rounded">
                    <label class="font-bold">
                        <input type="checkbox" class="esp-dia" value="${dia}"
                        ${registro ? "checked" : ""}>
                        ${dia}
                    </label>

                    <div class="flex gap-2 mt-2">
                        <input type="time" class="esp-inicio border p-1" value="${inicio}">
                        <input type="time" class="esp-fin border p-1" value="${fin}">
                    </div>
                </div>
            `;
        });

        document.getElementById("modalEspecial").classList.remove("hidden");
    });
}

// =========================
// GUARDAR HORARIO ESPECIAL EDITADO
// =========================

function guardarEspecial(){

    let id = document.getElementById("edit_especial_id").value;

    let dias = [];
    let formData = new FormData();

    formData.append("id_grupo", id);

    document.querySelectorAll("#edit_dias_container > div").forEach(card => {

        let check = card.querySelector(".esp-dia");
        let inicio = card.querySelector(".esp-inicio");
        let fin = card.querySelector(".esp-fin");

        if(check.checked){

            dias.push(check.value);

            formData.append(`hora_inicio[${check.value}]`, inicio.value);
            formData.append(`hora_fin[${check.value}]`, fin.value);
        }
    });

    dias.forEach(d => formData.append("dias[]", d));

    fetch("../process/update_horario_especial.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if(data.success){
            alert("Actualizado");
            location.reload();
        }else{
            alert("Error");
        }
    });
}

// =========================
// CERRAR MODAL HORARIO ESPECIAL
// =========================

function cerrarEspecial(){
    document.getElementById("modalEspecial").classList.add("hidden");
}


// =========================
// RESTRICCIONES DE ROL EN FRONTEND
// =========================
function initRoleRestrictions() {

    // ===== SOLO ADMIN =====
    if (USER_ROLE !== "ADMINISTRADOR") {

        // Bloquear botones críticos
        document.querySelectorAll("button[onclick*='eliminarGrupo']").forEach(b => {
            b.disabled = true;
            b.classList.add("opacity-50", "cursor-not-allowed");
        });

        document.querySelectorAll("button[onclick*='abrirEditarGrupo']").forEach(b => {
            b.disabled = true;
            b.classList.add("opacity-50", "cursor-not-allowed");
        });

        document.querySelectorAll("button[onclick*='eliminarHorarioEspecial']").forEach(b => {
            b.disabled = true;
            b.classList.add("opacity-50", "cursor-not-allowed");
        });

        document.querySelectorAll("button[onclick*='editarHorarioEspecial']").forEach(b => {
            b.disabled = true;
            b.classList.add("opacity-50", "cursor-not-allowed");
        });
    }

    // ===== SOLO ADMIN Y SECRETARIO =====
    if (!["ADMINISTRADOR", "SECRETARIO"].includes(USER_ROLE)) {

        // bloquear forms de creación (por si inspeccionan DOM)
        document.querySelectorAll("form").forEach(f => {
            if (f.action.includes("process_cursos") || f.action.includes("process_grupos")) {
                f.style.opacity = "0.5";
                f.style.pointerEvents = "none";
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", initRoleRestrictions);



</script>

<!-- MODAL EDITAR HORARIO NORMAL -->
<div id="modalEditar" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
    <div class="bg-white p-6 rounded w-[400px]">

        <h3 class="text-lg font-bold mb-3">Editar Grupo</h3>

        <input type="hidden" id="edit_id_grupo">

        <input id="edit_nombre" class="border p-2 w-full mb-2" placeholder="Nombre">

        <input id="edit_inicio" type="time" class="border p-2 w-full mb-2">
        <input id="edit_fin" type="time" class="border p-2 w-full mb-2">

        <div class="mb-2">
            <?php
            $dias = ["Lunes","Martes","Miércoles","Jueves","Viernes","Sábado","Domingo"];
            foreach ($dias as $d): ?>
                <label>
                    <input type="checkbox" class="edit_dia" value="<?= $d ?>"> <?= $d ?>
                </label><br>
            <?php endforeach; ?>
        </div>

        <div class="flex justify-end gap-2">
            <button onclick="cerrarModal()" class="px-3 py-1 bg-gray-400">Cerrar</button>
            <button onclick="guardarEdicionGrupo()" class="px-3 py-1 bg-green-600 text-white">Guardar</button>
        </div>

    </div>
</div>



<!-- MODAL HORARIO ESPECIAL EDITAR -->
<div id="modalEspecial" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
    <div class="bg-white p-6 rounded w-[500px]">

        <h3 class="text-lg font-bold mb-3">Editar Horario Especial</h3>

        <input type="hidden" id="edit_especial_id">

        <div id="edit_dias_container"></div>

        <div class="flex justify-end gap-2 mt-3">
            <button onclick="cerrarEspecial()" class="px-3 py-1 bg-gray-400">Cerrar</button>
            <button onclick="guardarEspecial()" class="px-3 py-1 bg-blue-600 text-white">Guardar</button>
        </div>

    </div>
</div>



<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>

