<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

$pdo = require __DIR__ . '/../config/db.php';

$id = $_GET['id'] ?? null;

if(!$id){
    die("ID inválido");
}

/* =========================
   OBTENER ALUMNO
========================= */

$stmt = $pdo->prepare("
SELECT 
    a.*,
    m.id_grupo,
    g.id_curso
FROM alumnos a
LEFT JOIN matriculas m 
    ON m.id_alumno = a.id_alumno
LEFT JOIN grupos g
    ON g.id_grupo = m.id_grupo
WHERE a.id_alumno = ?
");

$stmt->execute([$id]);
$alumno = $stmt->fetch();

if(!$alumno){
    die("Alumno no encontrado");
}

/* =========================
   CURSOS
========================= */

$cursos = $pdo->query("
SELECT * FROM cursos
ORDER BY nombre_curso
")->fetchAll();

/* =========================
   GRUPOS DEL CURSO ACTUAL
========================= */

$grupos = [];

if($alumno['id_grupo']){

    $stmt = $pdo->prepare("
    SELECT *
    FROM grupos
    WHERE id_curso = ?
    ");

    $stmt->execute([$alumno['id_curso']]);
    $grupos = $stmt->fetchAll();
}
?>

<form action="../process/process_editar_alumno.php"
      method="POST"
      class="space-y-6 text-white">

<input type="hidden"
       name="id_alumno"
       value="<?= $alumno['id_alumno'] ?>">

<!-- ========================= -->
<!-- DATOS PERSONALES -->
<!-- ========================= -->

<div class="bg-gray-800 p-6 rounded-xl shadow border border-gray-700 space-y-4">

<h3 class="font-semibold text-lg text-blue-400">
Datos del Alumno
</h3>

<label>Nombre</label>
<input name="nombre"
value="<?= htmlspecialchars($alumno['nombre']) ?>"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<label>DNI</label>
<input name="dni"
value="<?= $alumno['dni'] ?>"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<label>Edad</label>
<input name="edad"
value="<?= $alumno['edad'] ?>"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<label>Teléfono</label>
<input name="telefono"
value="<?= $alumno['telefono'] ?>"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<label>Teléfono Padres</label>
<input name="telefonopadres"
value="<?= $alumno['telefonopadres'] ?>"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<label>Teléfono Apoderado</label>
<input name="telefonoapoderado"
value="<?= $alumno['telefonoapoderado'] ?>"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<label>Email</label>
<input name="email"
value="<?= $alumno['email'] ?>"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<label>Dirección</label>
<input name="direccion"
value="<?= $alumno['direccion'] ?>"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

</div>

<!-- ========================= -->
<!-- APODERADO -->
<!-- ========================= -->

<div class="bg-gray-800 p-6 rounded-xl shadow border border-gray-700 space-y-4">

<h3 class="font-semibold text-lg text-blue-400">
Datos del Apoderado
</h3>

<label>Nombre Apoderado</label>
<input name="nombre_apoderado"
value="<?= $alumno['nombre_apoderado'] ?>"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<label>DNI Apoderado</label>
<input name="dni_apoderado"
value="<?= $alumno['dni_apoderado'] ?>"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<label>Correo Apoderado</label>
<input name="correo_apoderado"
value="<?= $alumno['correo_apoderado'] ?>"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<label>Notificar en Emergencia</label>
<input name="notificar_emergencia"
value="<?= $alumno['notificar_emergencia'] ?>"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<label>Contacto Pago</label>

<select name="contacto_pago"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<option value="Alumno"
<?= $alumno['contacto_pago']=='Alumno'?'selected':'' ?>>
Alumno
</option>

<option value="Padre"
<?= $alumno['contacto_pago']=='Padre'?'selected':'' ?>>
Padre
</option>

<option value="Apoderado"
<?= $alumno['contacto_pago']=='Apoderado'?'selected':'' ?>>
Apoderado
</option>

</select>

</div>

<!-- ========================= -->
<!-- ACADÉMICO -->
<!-- ========================= -->

<div class="bg-gray-800 p-6 rounded-xl shadow border border-gray-700 space-y-4">

<h3 class="font-semibold text-lg text-blue-400">
Datos Académicos
</h3>

<label>Tipo Ciclo</label>

<select name="tipo_ciclo"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<option value="Normal"
<?= $alumno['tipo_ciclo']=='Normal'?'selected':'' ?>>
Normal (4 meses)
</option>

<option value="Acelerado"
<?= $alumno['tipo_ciclo']=='Acelerado'?'selected':'' ?>>
Acelerado (2 meses)
</option>

<option value="Especialización"
<?= $alumno['tipo_ciclo']=='Especialización'?'selected':'' ?>>
Especialización (1 semana/mes)
</option>

</select>

<label>Medio Captación</label>

<input name="medio_captacion"
value="<?= $alumno['medio_captacion'] ?>"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<label>Curso</label>

<select name="id_curso"
        id="id_curso_modal"
        onchange="cargarGrupos(this.value, 'id_grupo_modal')"
        class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<option value="">
Seleccione curso
</option>

<?php foreach($cursos as $c): ?>

<option value="<?= $c['id_curso'] ?>"
<?= $c['id_curso'] == $alumno['id_curso'] ? 'selected' : '' ?>>

<?= $c['nombre_curso'] ?>

</option>

<?php endforeach; ?>

</select>

<label>Grupo</label>

<select name="id_grupo"
id="id_grupo_modal"
class="w-full bg-gray-900 border border-gray-600 px-3 py-2 rounded">

<option value="">
Seleccione grupo
</option>

<?php foreach($grupos as $g): ?>

<option value="<?= $g['id_grupo'] ?>"
<?= $g['id_grupo']==$alumno['id_grupo']
? 'selected'
: '' ?>>

<?= $g['nombre_grupo'] ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="flex justify-end gap-3">

<button
type="button"
onclick="window.cerrarModalEditar()"
class="bg-gray-600 hover:bg-gray-700 px-5 py-2 rounded">

Cancelar

</button>


<button
class="bg-blue-600 hover:bg-blue-700 px-5 py-2 rounded font-semibold">
Guardar cambios
</button>

</div>

</form>

<script>

function cargarGrupos(idCurso){

    const selectGrupo =
        document.getElementById(
            'id_grupo_modal'
        );

    if(!selectGrupo) return;

    // limpiar siempre

    selectGrupo.innerHTML = '';

    // placeholder inicial

    const optionInicial =
        document.createElement('option');

    optionInicial.value = '';
    optionInicial.textContent =
        'Seleccione grupo';

    selectGrupo.appendChild(
        optionInicial
    );

    if(!idCurso){

        return;

    }

    // mostrar cargando

    const loading =
        document.createElement('option');

    loading.textContent =
        'Cargando...';

    selectGrupo.appendChild(
        loading
    );

    fetch(
        `../process/get_grupos.php?id_curso=${idCurso}`
    )

    .then(response => response.json())

    .then(data => {

        // limpiar otra vez

        selectGrupo.innerHTML = '';

        const defaultOption =
            document.createElement(
                'option'
            );

        defaultOption.value = '';
        defaultOption.textContent =
            'Seleccione grupo';

        selectGrupo.appendChild(
            defaultOption
        );

        if(
            data.success &&
            data.grupos.length > 0
        ){

            data.grupos.forEach(grupo => {

                const option =
                    document.createElement(
                        'option'
                    );

                option.value =
                    grupo.id_grupo;

                option.textContent =
                    grupo.nombre_grupo;

                selectGrupo.appendChild(
                    option
                );

            });

        }

    })

    .catch(error => {

        console.error(error);

        selectGrupo.innerHTML =
            '<option>Error al cargar</option>';

    });

}




</script>

