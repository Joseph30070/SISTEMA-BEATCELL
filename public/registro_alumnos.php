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

<!-- ===================================================== -->
<!-- 1. DATOS DEL ALUMNO -->
<!-- ===================================================== -->
<section class="bg-white rounded-2xl shadow-md p-8 border border-gray-100">

    <h3 class="text-xl font-semibold mb-6 text-gray-800 flex items-center gap-2">
        <i class="fas fa-user-graduate text-teal-600"></i>
        Datos del Alumno
    </h3>

    <div class="grid md:grid-cols-3 gap-5">

        <div class="md:col-span-2">
            <label class="block font-semibold mb-1">Nombre Completo *</label>
            <input name="nombre" required placeholder="Ej: Juan Pérez Gómez" class="w-full border rounded-lg px-3 py-2">
        </div>

        <div>
            <label class="block font-semibold mb-1">DNI *</label>
            <input name="dni" type="text" maxlength="8" pattern="[0-9]{8}" required
                placeholder="Ej: 12345678"
                class="w-full border rounded-lg px-3 py-2"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>

        <div>
            <label class="block font-semibold mb-1">Edad</label>
            <input name="edad" type="number" min="1" max="100"
                placeholder="Ej: 15"
                class="w-full border rounded-lg px-3 py-2">
        </div>

        <div>
            <label class="block font-semibold mb-1">Correo Electrónico</label>
            <input name="email" type="email"
                placeholder="Ej: alumno@gmail.com"
                class="w-full border rounded-lg px-3 py-2">
        </div>

        <div class="md:col-span-2">
            <label class="block font-semibold mb-1">Dirección</label>
            <input name="direccion"
                placeholder="Ej: Av. Perú 123 - Comas"
                class="w-full border rounded-lg px-3 py-2">
        </div>

        <div>
            <label class="block font-semibold mb-1">Teléfono del Alumno</label>
            <input name="telefono" maxlength="9"
                placeholder="Ej: 987654321"
                class="w-full border rounded-lg px-3 py-2"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>

        <div>
            <label class="block font-semibold mb-1">Teléfono del Padre / Madre</label>
            <input name="telefonopadres" maxlength="9"
                placeholder="Ej: 912345678"
                class="w-full border rounded-lg px-3 py-2"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>

        

        <div>
            <label class="block font-semibold mb-1">Contacto de Pago</label>
            <select name="contacto_pago" class="w-full border rounded-lg px-3 py-2" required>
                <option value="Alumno">Alumno</option>
                <option value="Padre">Padre</option>
                <option value="Apoderado">Apoderado</option>
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Tipo de Ciclo</label>
            <select name="tipo_ciclo" class="w-full border rounded-lg px-3 py-2" required>
                <option value="">Seleccione tipo de ciclo</option>

                <option value="Normal">
                    Normal (4 meses)
                </option>

                <option value="Acelerado">
                    Acelerado (2 meses)
                </option>

                <option value="Especialización">
                    Especialización (1 semana / 1 mes)
                </option>

            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Medio de Captación</label>
            <select name="medio_captacion" class="w-full border rounded-lg px-3 py-2">
                <option value="">¿Cómo conoció la academia?</option>
                <option value="Facebook">Facebook</option>
                <option value="Instagram">Instagram</option>
                <option value="TikTok">TikTok</option>
                <option value="Recomendación">Recomendación</option>
                <option value="Otro">Otro</option>
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Notificar en Emergencia</label>
            <select name="notificar_emergencia" class="w-full border rounded-lg px-3 py-2">
                <option value="">Seleccione responsable</option>
                <option value="Padre">Padre</option>
                <option value="Madre">Madre</option>
                <option value="Apoderado">Apoderado</option>
            </select>
        </div>

    </div>

</section>

<!-- ===================================================== -->
<!-- 2. DATOS DEL REPRESENTANTE / APODERADO -->
<!-- ===================================================== -->
<section class="bg-blue-50 rounded-2xl shadow-md p-8 border border-blue-100">

    <h3 class="text-xl font-semibold mb-6 flex items-center gap-2">
        <i class="fas fa-user-shield text-blue-600"></i>
        Datos del Representante / Apoderado
    </h3>

    <div class="grid md:grid-cols-3 gap-5">

        <div>
            <label class="block font-semibold mb-1">Nombre del Apoderado</label>
            <input name="nombre_apoderado"
                placeholder="Ej: María López Torres"
                class="w-full border rounded-lg px-3 py-2">
        </div>

        <div>
            <label class="block font-semibold mb-1">DNI del Apoderado</label>
            <input name="dni_apoderado" maxlength="8"
                placeholder="Ej: 87654321"
                class="w-full border rounded-lg px-3 py-2"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>

        <div>
            <label class="block font-semibold mb-1">Correo del Apoderado</label>
            <input name="correo_apoderado" type="email"
                placeholder="Ej: apoderado@gmail.com"
                class="w-full border rounded-lg px-3 py-2">
        </div>

        <!-- IMPORTANTE: usamos telefonoapoderado existente -->
        <div>
            <label class="block font-semibold mb-1">Teléfono del Apoderado</label>
            <input name="telefonoapoderado" maxlength="9"
                placeholder="Ej: 998877665"
                class="w-full border rounded-lg px-3 py-2"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>

    </div>

</section>

<!-- ===================================================== -->
<!-- 3. ASIGNACIÓN ACADÉMICA -->
<!-- ===================================================== -->
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

    <p class="text-sm text-gray-500 mt-4">
        Nota: Un alumno puede matricularse en 2 o más cursos. Este formulario registra la primera matrícula.
    </p>

</section>

<div class="flex justify-end gap-3">
    <button type="reset" class="px-4 py-2 border rounded">Cancelar</button>
    <button type="submit" class="px-5 py-2 bg-teal-600 text-white rounded">Guardar</button>
</div>

</form>

</div>



<!-- ========================= -->
<!-- TAB INFO ACTUALIZADO -->
<!-- ========================= -->
<div id="contenido-info" class="tab-content hidden">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-4">

        <h3 class="text-xl font-semibold">
            Listado de Alumnos
        </h3>

        <div class="flex gap-2">

            <!-- BOTÓN FILTRO -->
            <button onclick="toggleFiltro()"
                class="bg-gray-600 text-white px-3 py-2 rounded hover:bg-gray-700 flex items-center gap-2">
                <i class="fas fa-filter"></i>
                Filtro
            </button>

            <!-- EXPORT EXCEL -->
            <button onclick="exportarExcel()"
                class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 flex items-center gap-2">
                <i class="fas fa-file-excel"></i>
                Excel
            </button>

            <!-- EXPORT PDF -->
            <button onclick="exportarPDF()"
                class="bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700 flex items-center gap-2">
                <i class="fas fa-file-pdf"></i>
                PDF
            </button>

        </div>

    </div>

    <!-- ========================= -->
    <!-- PANEL FILTRO -->
    <!-- ========================= -->
    <div id="panelFiltro"
         class="hidden absolute right-6 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg p-4 w-80 z-50">

        <h4 class="font-semibold mb-3 text-gray-700">
            Filtros
        </h4>

        <!-- BUSCAR -->
        <input type="text"
               id="buscar"
               placeholder="Nombre o DNI"
               class="w-full border rounded px-3 py-2 mb-2">

        <!-- ESTADO -->
        <select id="filtroEstado"
                class="w-full border rounded px-3 py-2 mb-2">

            <option value="">Todos</option>
            <option value="Activo">Activo</option>
            <option value="Baja">Baja</option>

        </select>

        <!-- CURSO -->
        <select id="filtroCurso"
                onchange="cargarGruposFiltro(this.value)"
                class="w-full border rounded px-3 py-2 mb-2">

            <option value="">Todos</option>

            <?php foreach($cursos as $c): ?>
                <option>
                    <?= htmlspecialchars($c['nombre_curso']) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <!-- GRUPO -->
        <select id="filtroGrupo"
                class="w-full border rounded px-3 py-2 mb-3">

            <option value="">Todos</option>

        </select>

        <!-- BOTONES -->
        <div class="flex gap-2">

            <button onclick="aplicarFiltro()"
                class="bg-teal-600 text-white px-3 py-2 rounded w-full">
                Aplicar
            </button>

            <button onclick="limpiarFiltro()"
                class="bg-gray-400 text-white px-3 py-2 rounded w-full">
                Limpiar
            </button>

        </div>

    </div>

    <!-- ========================= -->
    <!-- TABLA -->
    <!-- ========================= -->
    <div class="overflow-auto max-h-[500px] border border-gray-200 rounded">

        <table class="min-w-[1800px] w-full border border-gray-200">

            <thead class="bg-teal-600 text-white sticky top-0 z-10">
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Nombre</th>
                    <th class="p-3">DNI</th>
                    <th class="p-3">Edad</th>
                    <th class="p-3">Teléfono</th>
                    <th class="p-3">Padres</th>
                    <th class="p-3">Apoderado</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Dirección</th>
                    <th class="p-3">Nombre Apoderado</th>
                    <th class="p-3">DNI Apoderado</th>
                    <th class="p-3">Correo Apoderado</th>
                    <th class="p-3">Contacto Pago</th>
                    <th class="p-3">Emergencia</th>
                    <th class="p-3">Ciclo</th>
                    <th class="p-3">Captación</th>
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
                    <td class="p-2 font-semibold">
                        <?= htmlspecialchars($a['nombre']) ?>
                    </td>
                    <td class="p-2"><?= $a['dni'] ?></td>
                    <td class="p-2"><?= $a['edad'] ?? '-' ?></td>
                    <td class="p-2"><?= $a['telefono'] ?? '-' ?></td>
                    <td class="p-2"><?= $a['telefonopadres'] ?? '-' ?></td>
                    <td class="p-2"><?= $a['telefonoapoderado'] ?? '-' ?></td>
                    <td class="p-2"><?= $a['email'] ?? '-' ?></td>
                    <td class="p-2"><?= $a['direccion'] ?? '-' ?></td>
                    <td class="p-2"><?= $a['nombre_apoderado'] ?? '-' ?></td>
                    <td class="p-2"><?= $a['dni_apoderado'] ?? '-' ?></td>
                    <td class="p-2"><?= $a['correo_apoderado'] ?? '-' ?></td>

                    <td class="p-2"><?= $a['contacto_pago'] ?></td>
                    <td class="p-2"><?= $a['notificar_emergencia'] ?? '-' ?></td>
                    <td class="p-2"><?= $a['tipo_ciclo'] ?? '-' ?></td>
                    <td class="p-2"><?= $a['medio_captacion'] ?? '-' ?></td>
                    <td class="p-2"><?= $a['nombre_curso'] ?? '-' ?></td>
                    <td class="p-2"><?= $a['nombre_grupo'] ?? '-' ?></td>
                    <td class="p-2"><?= $a['fecha_registro'] ?></td>

                    <td class="p-2">
                        <?= $a['fecha_baja'] ?? '—' ?>
                    </td>

                    <td class="p-2">
                        <?= $a['estado'] ?>
                    </td>

                    <td class="p-2 flex gap-2">

                        <button
                            onclick="abrirEditarAlumno(<?= $a['id_alumno'] ?>)"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                            Editar
                        </button>


                        <?php if($a['estado'] == 'Activo'): ?>
                            <a href="../process/process_baja_alumno.php?id=<?= $a['id_alumno'] ?>"
                               class="bg-red-500 text-white px-3 py-1 rounded text-sm">
                               Baja
                            </a>
                        <?php else: ?>
                            <a href="../process/process_reactivar_alumno.php?id=<?= $a['id_alumno'] ?>"
                               class="bg-green-500 text-white px-3 py-1 rounded text-sm">
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
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>

// Función para cargar grupos según el curso seleccionado
function cargarGrupos(idCurso, groupSelectId = 'id_grupo', horarioInputId = 'horario') {
    const selectGrupo = document.getElementById(groupSelectId);
    const inputHorario = document.getElementById(horarioInputId);

    if (!selectGrupo) {
        return;
    }

    // Limpiar selects
    selectGrupo.innerHTML = '<option value="">-- Cargando... --</option>';
    if (inputHorario) {
        inputHorario.value = '';
    }

    if (!idCurso) {
        selectGrupo.innerHTML = '<option value="">-- Primero seleccione curso --</option>';
        return;
    }

    // Hacer petición AJAX
    fetch(`../process/get_grupos.php?id_curso=${idCurso}`)
        .then(response => response.json())
        .then(data => {
            selectGrupo.innerHTML = '<option value="">-- Seleccione Grupo --</option>';

            if (data.success && data.grupos.length > 0) {
                data.grupos.forEach(grupo => {
                    const option = document.createElement('option');
                    option.value = grupo.id_grupo;
                    option.textContent = grupo.nombre_grupo;
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

    const inputHorario =
        document.getElementById('horario');

    if (!idGrupo) {
        inputHorario.value = '';
        return;
    }

    fetch(
        `../process/get_horario_grupo.php?id_grupo=${idGrupo}`
    )

    .then(res => res.json())

    .then(data => {

        if (data.success) {

            inputHorario.value =
                data.horario;

        } else {

            inputHorario.value =
                'Horario no encontrado';

        }

    })

    .catch(error => {

        console.error(error);

        inputHorario.value =
            'Error al cargar horario';

    });

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

const alumnosData = <?= json_encode($alumnos, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;

function getVisibleAlumnos() {
    const texto = document.getElementById('buscar').value.toLowerCase();
    const estado = document.getElementById('filtroEstado').value;
    const curso = document.getElementById('filtroCurso').value.toLowerCase();
    const grupo = document.getElementById('filtroGrupo').value.toLowerCase();

    return alumnosData.filter(a => {
        const nombre = (a.nombre ?? '').toLowerCase();
        const dni = (a.dni ?? '').toLowerCase();
        const cursoFila = (a.nombre_curso ?? '').toLowerCase();
        const grupoFila = (a.nombre_grupo ?? '').toLowerCase();
        const estadoFila = (a.estado ?? '').trim();

        const matchTexto = !texto || nombre.includes(texto) || dni.includes(texto);
        const matchEstado = !estado || estado === '' || estadoFila === estado;
        const matchCurso = !curso || cursoFila.includes(curso);
        const matchGrupo = !grupo || grupoFila.includes(grupo);

        return matchTexto && matchEstado && matchCurso && matchGrupo;
    });
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

    let panel = document.getElementById('panelFiltro');

    if(panel){
        panel.classList.toggle('hidden');
    }

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

        let email = fila.children[7].innerText.toLowerCase();
        let direccion = fila.children[8].innerText.toLowerCase();

        let cursoFila = fila.children[16].innerText.toLowerCase();
        let grupoFila = fila.children[17].innerText.toLowerCase();

        let estadoFila = fila.children[20].innerText.trim();

        let matchTexto =
            nombre.includes(texto) ||
            dni.includes(texto) ||
            email.includes(texto) ||
            direccion.includes(texto);

        let matchEstado =
            estado === "" || estadoFila === estado;

        let matchCurso =
            curso === "" || cursoFila.includes(curso);

        let matchGrupo =
            grupo === "" || grupoFila.includes(grupo);

        fila.style.display =
            (matchTexto && matchEstado && matchCurso && matchGrupo)
            ? "" : "none";

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

    const visibleAlumnos = getVisibleAlumnos();

    let contenido = `
    <meta charset="UTF-8">
    <table border="1">
    <tr style="background:#9b00ff; color:white;">
        <th>ID</th>
        <th>Nombre</th>
        <th>DNI</th>
        <th>Edad</th>
        <th>Teléfono</th>
        <th>Padres</th>
        <th>Apoderado Tel</th>
        <th>Email</th>
        <th>Dirección</th>
        <th>Apoderado Nombre</th>
        <th>DNI Apoderado</th>
        <th>Correo Apoderado</th>
        <th>Contacto Pago</th>
        <th>Emergencia</th>
        <th>Ciclo</th>
        <th>Captación</th>
        <th>Curso</th>
        <th>Grupo</th>
        <th>Registro</th>
        <th>Fecha Baja</th>
        <th>Estado</th>
    </tr>
    `;

    visibleAlumnos.forEach(a => {
        contenido += `
        <tr>
            <td>${a.id_alumno ?? ''}</td>
            <td>${a.nombre ?? ''}</td>
            <td>${a.dni ?? ''}</td>
            <td>${a.edad ?? ''}</td>
            <td>${a.telefono ?? ''}</td>
            <td>${a.telefonopadres ?? ''}</td>
            <td>${a.telefonoapoderado ?? ''}</td>
            <td>${a.email ?? ''}</td>
            <td>${a.direccion ?? ''}</td>
            <td>${a.nombre_apoderado ?? ''}</td>
            <td>${a.dni_apoderado ?? ''}</td>
            <td>${a.correo_apoderado ?? ''}</td>
            <td>${a.contacto_pago ?? ''}</td>
            <td>${a.notificar_emergencia ?? ''}</td>
            <td>${a.tipo_ciclo ?? ''}</td>
            <td>${a.medio_captacion ?? ''}</td>
            <td>${a.nombre_curso ?? ''}</td>
            <td>${a.nombre_grupo ?? ''}</td>
            <td>${a.fecha_registro ?? ''}</td>
            <td>${a.fecha_baja ?? ''}</td>
            <td>${a.estado ?? ''}</td>
        </tr>`;
    });

    contenido += "</table>";

    const blob = new Blob(["\ufeff", contenido], {
        type: "application/vnd.ms-excel;charset=utf-8;"
    });

    const a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = "reporte_alumnos.xls";
    a.click();
}




function exportarPDF(){

    const visibleAlumnos = getVisibleAlumnos();
    const fecha = new Date().toLocaleDateString();

    let rowsHtml = visibleAlumnos.map(a => `
        <tr>
            <td>${a.id_alumno ?? ''}</td>
            <td>${a.nombre ?? ''}</td>
            <td>${a.dni ?? ''}</td>
            <td>${a.edad ?? ''}</td>
            <td>${a.telefono ?? ''}</td>
            <td>${a.telefonopadres ?? ''}</td>
            <td>${a.telefonoapoderado ?? ''}</td>
            <td>${a.email ?? ''}</td>
            <td>${a.direccion ?? ''}</td>
            <td>${a.nombre_apoderado ?? ''}</td>
            <td>${a.dni_apoderado ?? ''}</td>
            <td>${a.correo_apoderado ?? ''}</td>
            <td>${a.contacto_pago ?? ''}</td>
            <td>${a.notificar_emergencia ?? ''}</td>
            <td>${a.tipo_ciclo ?? ''}</td>
            <td>${a.medio_captacion ?? ''}</td>
            <td>${a.nombre_curso ?? ''}</td>
            <td>${a.nombre_grupo ?? ''}</td>
            <td>${a.fecha_registro ?? ''}</td>
            <td>${a.fecha_baja ?? ''}</td>
            <td>${a.estado ?? ''}</td>
        </tr>
    `).join('');

    const html = `
    <div style="font-family: Arial; padding:20px;">

        <!-- HEADER -->
        <div style="display:flex; align-items:center; gap:15px; border-bottom:3px solid #9b00ff; padding-bottom:10px;">
            <img src="../img/logo-beatcell.png" width="70">

            <div>
                <h2 style="margin:0; color:#9b00ff;">BEATCELL</h2>
                <small style="color:#555;">Sistema de Gestión de Alumnos</small>
            </div>
        </div>

        <p style="margin-top:10px;"><strong>Fecha:</strong> ${fecha}</p>

        <!-- TABLA -->
        <table border="1"
               style="width:100%;
               border-collapse: collapse;
               font-size:10px;
               margin-top:10px;">

            <thead>
                <tr style="background:#0d1b2a; color:white;">
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>DNI</th>
                    <th>Edad</th>
                    <th>Teléfono</th>
                    <th>Padres</th>
                    <th>Apoderado</th>
                    <th>Email</th>
                    <th>Dirección</th>
                    <th>Apoderado</th>
                    <th>DNI Apod.</th>
                    <th>Correo Apod.</th>
                    <th>Pago</th>
                    <th>Emerg.</th>
                    <th>Ciclo</th>
                    <th>Captación</th>
                    <th>Curso</th>
                    <th>Grupo</th>
                    <th>Registro</th>
                    <th>Baja</th>
                    <th>Estado</th>
                </tr>
            </thead>

            <tbody>
                ${rowsHtml}
            </tbody>

        </table>

        <!-- FOOTER -->
        <p style="margin-top:15px; font-size:11px; color:#9b00ff;">
            Reporte generado automáticamente por BEATCELL
        </p>

    </div>
    `;

    html2pdf().set({
        margin: 0.3,
        filename: 'reporte_alumnos.pdf',
        html2canvas: { scale: 2 },
        jsPDF: { orientation: 'landscape' }
    }).from(html).save();
}


window.abrirEditarAlumno = function(id){

    let modal =
        document.getElementById(
            'modalEditarAlumno'
        );

    let contenedor =
        document.getElementById(
            'contenidoEditarAlumno'
        );

    if(!modal || !contenedor){

        alert("Modal no encontrado");
        return;

    }

    modal.classList.remove('hidden');

    contenedor.innerHTML =
        '<div class="text-center py-10">Cargando...</div>';

    fetch(`editar_alumno.php?id=${id}`)

    .then(res => res.text())

    .then(html => {

        contenedor.innerHTML = html;

    })

    .catch(() => {

        contenedor.innerHTML =
            '<div>Error al cargar</div>';

    });

}

window.cerrarModalEditar = function(){

    let modal =
        document.getElementById(
            'modalEditarAlumno'
        );

    if(modal){

        modal.classList.add('hidden');

    }

}


window.addEventListener("click", function(e){

    let modal =
        document.getElementById(
            "modalEditarAlumno"
        );

    if(
        modal &&
        e.target === modal
    ){

        window.cerrarModalEditar();

    }

});




</script>

<!-- MODAL EDITAR ALUMNO -->

<div id="modalEditarAlumno"
     class="fixed inset-0
            bg-black bg-opacity-70
            flex items-center justify-center
            hidden
            z-50">

    <div class="
        bg-gray-900
        text-white
        rounded-xl
        shadow-2xl
        w-full
        max-w-4xl
        p-6
        relative
        border border-blue-500
        max-h-[90vh]
        overflow-y-auto
    ">

        <!-- BOTON CERRAR -->

        <button
            onclick="window.cerrarModalEditar()"
            class="
                absolute
                top-3
                right-3
                text-gray-400
                hover:text-white
                text-xl
            ">
            ✕
        </button>

        <!-- TITULO -->

        <h2 class="
            text-2xl
            font-bold
            mb-4
            text-blue-400
        ">
            Editar Alumno
        </h2>

        <!-- CONTENIDO DINAMICO -->

        <div id="contenidoEditarAlumno">

            <div class="text-center py-10">
                Cargando...
            </div>

        </div>

    </div>

</div>


<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>