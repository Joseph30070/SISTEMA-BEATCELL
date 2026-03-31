<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

$config = require __DIR__ . '/../config/config.php';
$base = rtrim($config['base_url'], '/');

$pdo = require __DIR__ . '/../config/db.php';

// =========================
// OBTENER CURSOS
// =========================
$stmt = $pdo->query("SELECT * FROM cursos ORDER BY nombre_curso ASC");
$cursos = $stmt->fetchAll();

// =========================
// OBTENER ALUMNOS
// =========================
$stmt = $pdo->query("
SELECT 
    a.*, 
    g.nombre_grupo,
    c.nombre_curso,
    CASE 
        WHEN a.fecha_baja IS NULL THEN 'Activo'
        ELSE 'Baja'
    END as estado
FROM alumnos a
LEFT JOIN matriculas m ON m.id_alumno = a.id_alumno
LEFT JOIN grupos g ON g.id_grupo = m.id_grupo
LEFT JOIN cursos c ON c.id_curso = g.id_curso
ORDER BY a.id_alumno DESC
");

$alumnos = $stmt->fetchAll();

// =========================
// OBTENER ASISTENCIAS
// =========================
$stmt = $pdo->query("
SELECT 
    asl.id_asistencia,
    asl.id_alumno,
    a.nombre,
    asl.fecha,
    asl.hora_entrada,
    asl.hora_salida
FROM asistencias asl
INNER JOIN alumnos a ON a.id_alumno = asl.id_alumno
ORDER BY asl.fecha DESC
LIMIT 50
");

$asistencias = $stmt->fetchAll();

$title  = "Registro de Alumno";
$active = "registro";

ob_start();
?>

<!-- MENSAJES -->
<?php if(isset($_GET['success'])): ?>
<div id="alerta" class="fixed top-5 right-5 bg-green-500 text-white px-5 py-3 rounded shadow-lg z-50">
    ✓ <?= htmlspecialchars($_GET['success']) ?>
</div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
<div id="alerta" class="fixed top-5 right-5 bg-red-500 text-white px-5 py-3 rounded shadow-lg z-50">
    ✗ <?= htmlspecialchars($_GET['error']) ?>
</div>
<?php endif; ?>

<h2 class="text-3xl font-bold text-gray-800 mb-6">
    Gestión de Alumnos
</h2>

<!-- ========================= -->
<!-- TABS -->
<!-- ========================= -->
<div class="flex border-b mb-6">

    <button id="tab-registro"
        class="tab-btn px-4 py-2 font-semibold bg-teal-600 text-white"
        onclick="mostrarTab('registro')">
        Registrar Alumno
    </button>

    <button id="tab-info"
        class="tab-btn px-4 py-2 font-semibold text-gray-600 hover:bg-gray-100"
        onclick="mostrarTab('info')">
        Información de Alumnos
    </button>

</div>

<!-- ========================= -->
<!-- TAB REGISTRO -->
<!-- ========================= -->
<div id="contenido-registro" class="tab-content">

<form action="../process/process_alumno.php" method="POST" class="space-y-8" id="formAlumno">

<!-- DATOS -->
<section class="bg-white rounded-2xl shadow-md p-8 border border-gray-100">

    <h3 class="text-xl font-semibold mb-6 text-gray-800 flex items-center gap-2">
        <i class="fas fa-user-graduate text-teal-600"></i>
        Datos del Alumno
    </h3>

    <div class="grid md:grid-cols-3 gap-5">

        <div class="md:col-span-2">
            <label class="block font-semibold mb-1">Nombre Completo *</label>
            <input name="nombre" required  placeholder="Ej: Pedro Chavez" class="w-full border rounded-lg px-3 py-2">
        </div>

        <div>
            <label class="block font-semibold mb-1">DNI *</label>
            <input name="dni" type="text" maxlength="8" pattern="[0-9]{8}" inputmode="numeric" required placeholder="Ej: 12345678" class="w-full border rounded-lg px-3 py-2" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            <small class="text-gray-500 text-xs">
                Debe contener exactamente 8 números
            </small>
        </div>

        <div>
            <label class="block font-semibold mb-1">Teléfono</label>
            <input name="telefono" type="text" maxlength="9" pattern="[0-9]{9}" inputmode="numeric" placeholder="Ej: 987654321" class="w-full border rounded-lg px-3 py-2" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            <small class="text-gray-500 text-xs">
                Debe contener exactamente 9 números
            </small>
        </div>

        <div>
            <label class="block font-semibold mb-1">Teléfono Padres</label>
            <input name="telefonopadres" type="text" maxlength="9" pattern="[0-9]{9}" inputmode="numeric" placeholder="Ej: 987654321" class="w-full border rounded-lg px-3 py-2" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            <small class="text-gray-500 text-xs">
                Debe contener exactamente 9 números
            </small>
        </div>

        <div>
            <label class="block font-semibold mb-1">Teléfono Apoderado</label>
            <input name="telefonoapoderado" type="text" maxlength="9" pattern="[0-9]{9}" inputmode="numeric" placeholder="Ej: 987654321" class="w-full border rounded-lg px-3 py-2" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            <small class="text-gray-500 text-xs">
                Debe contener exactamente 9 números
            </small>
        </div>

        <div>
            <label class="block font-semibold mb-1">Contacto de Pago</label>
            <select name="contacto_pago" class="w-full border rounded-lg px-3 py-2">
                <option value="Alumno">Alumno</option>
                <option value="Padre">Padre</option>
                <option value="Apoderado">Apoderado</option>
            </select>
        </div>

    </div>

</section>

<!-- ASIGNACIÓN -->
<section class="bg-purple-50 rounded-2xl shadow-md p-8 border border-purple-100">

    <h3 class="text-xl font-semibold mb-6 flex items-center gap-2">
        <i class="fas fa-book text-purple-600"></i>
        Asignación Académica
    </h3>

    <div class="grid md:grid-cols-3 gap-5">

        <div>
            <label>Curso *</label>
            <select id="id_curso" name="id_curso" required onchange="cargarGrupos(this.value)"
                class="w-full border rounded-lg px-3 py-2">

                <option value="">-- Seleccione Curso --</option>
                <?php foreach($cursos as $c): ?>
                    <option value="<?= $c['id_curso'] ?>">
                        <?= htmlspecialchars($c['nombre_curso']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Grupo *</label>
            <select id="id_grupo" name="id_grupo" required onchange="cargarHorario(this.value)"
                class="w-full border rounded-lg px-3 py-2">
                <option value="">-- Primero seleccione curso --</option>
            </select>
        </div>

        <div>
            <label>Horario</label>
            <input id="horario" readonly class="w-full border rounded-lg px-3 py-2 bg-gray-100">
        </div>

    </div>

</section>

<div class="flex justify-end gap-3">
    <button type="reset" class="px-4 py-2 border rounded">Cancelar</button>
    <button type="submit" class="px-5 py-2 bg-teal-600 text-white rounded">Guardar</button>
</div>

</form>

</div>
<!-- ========================= -->
<!-- TAB INFO -->
<!-- ========================= -->
<div id="contenido-info" class="tab-content hidden">

<div class="flex justify-between items-center mb-4">

    <h3 class="text-xl font-semibold">Listado de Alumnos</h3>

    <div class="flex gap-2">

        <!-- EXPORTAR EXCEL -->
        <button onclick="exportarExcel()"
            class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 flex items-center gap-2">
            <i class="fas fa-file-excel"></i>
            Excel
        </button>

        <!-- EXPORTAR PDF -->
        <button onclick="exportarPDF()"
            class="bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700 flex items-center gap-2">
            <i class="fas fa-file-pdf"></i>
            PDF
        </button>

        <!-- FILTRO -->
        <div class="relative">

            <button onclick="toggleFiltro()"
                class="bg-gray-200 px-3 py-2 rounded hover:bg-gray-300 flex items-center gap-2">
                <i class="fas fa-filter"></i>
                Filtros
            </button>

            <!-- PANEL -->
            <div id="panelFiltro"
                class="hidden absolute right-0 mt-2 bg-white border rounded shadow-lg p-4 w-72 z-50 space-y-3">

                <h4 class="font-semibold text-gray-700">Filtros</h4>

                <!-- BUSCAR -->
                <div>
                    <label class="text-sm">Buscar</label>
                    <input type="text" id="buscar"
                        placeholder="Nombre o DNI"
                        class="w-full border px-2 py-1 rounded">
                </div>

                <!-- ESTADO -->
                <div>
                    <label class="text-sm">Estado</label>
                    <select id="filtroEstado" class="w-full border px-2 py-1 rounded">
                        <option value="">Todos</option>
                        <option value="Activo">Activo</option>
                        <option value="Baja">Baja</option>
                    </select>
                </div>

                <!-- CURSO -->
                <div>
                    <label class="text-sm">Curso</label>
                    <select id="filtroCurso"
                        class="w-full border px-2 py-1 rounded"
                        onchange="cargarGruposFiltro(this.value)">

                        <option value="">Todos</option>

                        <?php foreach($cursos as $c): ?>
                            <option value="<?= htmlspecialchars($c['nombre_curso']) ?>">
                                <?= htmlspecialchars($c['nombre_curso']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <!-- GRUPO -->
                <div>
                    <label class="text-sm">Grupo</label>
                    <select id="filtroGrupo" class="w-full border px-2 py-1 rounded">
                        <option value="">Todos</option>
                    </select>
                </div>

                <!-- BOTONES -->
                <div class="flex gap-2">
                    <button onclick="aplicarFiltro()"
                        class="flex-1 bg-teal-600 text-white py-1 rounded">
                        Aplicar
                    </button>

                    <button onclick="limpiarFiltro()"
                        class="flex-1 bg-gray-300 py-1 rounded">
                        Limpiar
                    </button>
                </div>

            </div>

        </div>

    </div>

</div>

<table class="w-full border border-gray-200">

    <thead class="bg-teal-600 text-white">
    <tr>
        <th class="p-3">ID</th>
        <th class="p-3">Nombre</th>
        <th class="p-3">DNI</th>
        <th class="p-3">Teléfono</th>
        <th class="p-3">Padres</th>
        <th class="p-3">Apoderado</th>
        <th class="p-3">Contacto Pago</th>
        <th class="p-3">Curso</th>
        <th class="p-3">Grupo</th>
        <th class="p-3">Registro</th>
        <th class="p-3">Fecha Baja</th>
        <th class="p-3">Estado</th>
        <th class="p-3">Acciones</th>
    </tr>
    </thead>

    <tbody>

    <?php foreach($alumnos as $a): ?>
    <tr class="border-t hover:bg-gray-50">

        <td class="p-2"><?= $a['id_alumno'] ?></td>

        <td class="p-2 font-semibold text-gray-800">
            <?= htmlspecialchars($a['nombre']) ?>
        </td>

        <td class="p-2"><?= $a['dni'] ?></td>

        <!-- TELÉFONO PRINCIPAL -->
        <td class="p-2"><?= $a['telefono'] ?? '-' ?></td>

        <!-- PADRES -->
        <td class="p-2"><?= $a['telefonopadres'] ?? '-' ?></td>

        <!-- APODERADO -->
        <td class="p-2"><?= $a['telefonoapoderado'] ?? '-' ?></td>

        <!-- CONTACTO PAGO -->
        <td class="p-2">
            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-semibold">
                <?= $a['contacto_pago'] ?>
            </span>
        </td>

        <!-- CURSO -->
        <td class="p-2"><?= $a['nombre_curso'] ?? '-' ?></td>

        <!-- GRUPO -->
        <td class="p-2"><?= $a['nombre_grupo'] ?? '-' ?></td>

        <!-- FECHA REGISTRO -->
        <td class="p-2"><?= $a['fecha_registro'] ?></td>

        <!-- FECHA BAJA -->
        <td class="p-2">
            <?php if($a['fecha_baja']): ?>
                <span class="text-red-600 font-semibold">
                    <?= $a['fecha_baja'] ?>
                </span>
            <?php else: ?>
                <span class="text-gray-400">
                    —
                </span>
            <?php endif; ?>
        </td>

        <!-- ESTADO -->
        <td class="p-2">
            <?php if($a['estado'] == 'Activo'): ?>
                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-semibold">
                    Activo
                </span>
            <?php else: ?>
                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-semibold">
                    Baja
                </span>
            <?php endif; ?>
        </td>

        <!-- ACCIONES -->
        <td class="p-2 flex gap-2">

            <!-- EDITAR -->
            <a href="editar_alumno.php?id=<?= $a['id_alumno'] ?>"
            class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
            Editar
            </a>

            <?php if($a['estado'] == 'Activo'): ?>
                <a href="../process/process_baja_alumno.php?id=<?= $a['id_alumno'] ?>"
                onclick="return confirm('¿Dar de baja a este alumno?')"
                class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                Baja
                </a>
            <?php else: ?>
                <a href="../process/process_reactivar_alumno.php?id=<?= $a['id_alumno'] ?>"
                onclick="return confirm('¿Reactivar alumno?')"
                class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                Reactivar
                </a>
            <?php endif; ?>

        </td>

    </tr>
    <?php endforeach; ?>

    </tbody>

</table>

</div>

</div>

</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>

// Función para cargar grupos según el curso seleccionado
function cargarGrupos(idCurso) {
    const selectGrupo = document.getElementById('id_grupo');
    const inputHorario = document.getElementById('horario');

    // Limpiar selects
    selectGrupo.innerHTML = '<option value="">-- Cargando... --</option>';
    inputHorario.value = '';

    if (!idCurso) {
        selectGrupo.innerHTML = '<option value="">-- Primero seleccione curso --</option>';
        return;
    }

    // Hacer petición AJAX
    fetch(`../process/get_grupos.php?id_curso=${idCurso}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.grupos.length > 0) {
                selectGrupo.innerHTML = '<option value="">-- Seleccione Grupo --</option>';
                
                data.grupos.forEach(grupo => {
                    const option = document.createElement('option');
                    option.value = grupo.id_grupo;
                    option.textContent = grupo.nombre_grupo;
                    option.dataset.horario = `${grupo.hora_inicio.substring(0, 5)} - ${grupo.hora_fin.substring(0, 5)}`;
                    selectGrupo.appendChild(option);
                });
            } else {
                selectGrupo.innerHTML = '<option value="">No hay grupos en este curso</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            selectGrupo.innerHTML = '<option value="">Error al cargar grupos</option>';
        });
}

// Función para cargar horario del grupo seleccionado
function cargarHorario(idGrupo) {
    const selectGrupo = document.getElementById('id_grupo');
    const inputHorario = document.getElementById('horario');
    
    const selectedOption = selectGrupo.options[selectGrupo.selectedIndex];
    
    if (selectedOption.dataset.horario) {
        inputHorario.value = selectedOption.dataset.horario;
    } else {
        inputHorario.value = '';
    }
}

// Función para mostrar/ocultar tabs
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

// Auto ocultar alertas
setTimeout(() => {
    let alerta = document.getElementById("alerta");
    if (alerta) {
        alerta.style.transition = "opacity 0.5s";
        alerta.style.opacity = "0";
        setTimeout(() => alerta.remove(), 500);
    }
}, 3000);

//funcion para filtros
function toggleFiltro(){
    document.getElementById('panelFiltro').classList.toggle('hidden');
}

function aplicarFiltro(){

    let texto = document.getElementById('buscar').value.toLowerCase();
    let estado = document.getElementById('filtroEstado').value;
    let curso = document.getElementById('filtroCurso').value.toLowerCase();
    let grupo = document.getElementById('filtroGrupo').value.toLowerCase();

    let filas = document.querySelectorAll("#contenido-info tbody tr");

    filas.forEach(fila => {

        let nombre = fila.children[1].innerText.toLowerCase();
        let dni = fila.children[2].innerText.toLowerCase();
        let cursoFila = fila.children[7].innerText.toLowerCase();
        let grupoFila = fila.children[8].innerText.toLowerCase();
        let estadoFila = fila.children[11].innerText.trim();

        let matchTexto = nombre.includes(texto) || dni.includes(texto);
        let matchEstado = estado === "" || estadoFila === estado;
        let matchCurso = curso === "" || cursoFila.includes(curso);
        let matchGrupo = grupo === "" || grupoFila.includes(grupo);

        if(matchTexto && matchEstado && matchCurso && matchGrupo){
            fila.style.display = "";
        } else {
            fila.style.display = "none";
        }

    });

}

function limpiarFiltro(){

    document.getElementById('buscar').value = "";
    document.getElementById('filtroEstado').value = "";
    document.getElementById('filtroCurso').value = "";
    document.getElementById('filtroGrupo').value = "";

    aplicarFiltro();
}

document.addEventListener('click', function(e){

    let panel = document.getElementById('panelFiltro');
    let botonFiltro = e.target.closest('[onclick="toggleFiltro()"]');

    if (!panel.contains(e.target) && !botonFiltro) {
        panel.classList.add('hidden');
    }

});

function cargarGruposFiltro(nombreCurso){

    let selectGrupo = document.getElementById('filtroGrupo');

    selectGrupo.innerHTML = '<option value="">Cargando...</option>';

    if(!nombreCurso){
        selectGrupo.innerHTML = '<option value="">Todos</option>';
        return;
    }

    // buscamos el ID del curso desde el select original
    let selectCurso = document.getElementById('id_curso');

    let idCurso = null;

    for(let opt of selectCurso.options){
        if(opt.text === nombreCurso){
            idCurso = opt.value;
            break;
        }
    }

    if(!idCurso){
        selectGrupo.innerHTML = '<option value="">Error</option>';
        return;
    }

    fetch(`../process/get_grupos.php?id_curso=${idCurso}`)
    .then(res => res.json())
    .then(data => {

        if(data.success){
            selectGrupo.innerHTML = '<option value="">Todos</option>';

            data.grupos.forEach(g => {
                let op = document.createElement('option');
                op.value = g.nombre_grupo;
                op.textContent = g.nombre_grupo;
                selectGrupo.appendChild(op);
            });
        }

    })
    .catch(() => {
        selectGrupo.innerHTML = '<option value="">Error</option>';
    });
}

function exportarExcel(){

    let filas = document.querySelectorAll("#contenido-info tbody tr");

    let contenido = `
    <meta charset="UTF-8">
    <table border="1">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>DNI</th>
        <th>Teléfono</th>
        <th>Padres</th>
        <th>Apoderado</th>
        <th>Contacto Pago</th>
        <th>Curso</th>
        <th>Grupo</th>
        <th>Registro</th>
        <th>Fecha Baja</th>
        <th>Estado</th>
    </tr>
    `;

    filas.forEach(fila => {

        if(fila.style.display === "none") return;

        contenido += "<tr>";

        for(let i = 0; i <= 11; i++){
            contenido += `<td>${fila.children[i].innerText}</td>`;
        }

        contenido += "</tr>";
    });

    contenido += "</table>";

    let blob = new Blob(["\ufeff", contenido], {
        type: "application/vnd.ms-excel;charset=utf-8;"
    });

    let a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = "reporte_alumnos.xls";
    a.click();
}

function exportarPDF(){

    let filas = document.querySelectorAll("#contenido-info tbody tr");

    let fecha = new Date().toLocaleDateString();

    let html = `
    <div style="font-family: Arial;">

        <div style="display:flex; align-items:center; gap:10px;">
            <img src="../img/logo-beatcell.png" width="60">
            <div>
                <h2 style="margin:0;">BEATCELL</h2>
                <small>Reporte de Alumnos</small>
            </div>
        </div>

        <hr>

        <p><strong>Fecha:</strong> ${fecha}</p>

        <table border="1" style="width:100%; border-collapse: collapse; font-size:11px;">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>DNI</th>
                <th>Teléfono</th>
                <th>Padres</th>
                <th>Apoderado</th>
                <th>Contacto</th>
                <th>Curso</th>
                <th>Grupo</th>
                <th>Registro</th>
                <th>Fecha Baja</th>
                <th>Estado</th>
            </tr>
    `;

    filas.forEach(fila => {

        if(fila.style.display === "none") return;

        html += "<tr>";

        for(let i = 0; i <= 11; i++){
            html += `<td>${fila.children[i].innerText}</td>`;
        }

        html += "</tr>";
    });

    html += "</table></div>";

    let opciones = {
        margin: 0.3,
        filename: 'reporte_alumnos.pdf',
        html2canvas: { scale: 2 },
        jsPDF: { orientation: 'landscape' }
    };

    html2pdf().set(opciones).from(html).save();
}

</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>