<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

$title  = "Control de Cuotas";
$active = "cuotas";

ob_start();
?>

<h2 class="text-3xl font-bold text-gray-800 mb-6">
    Control de Cuotas
</h2>

<p class="text-gray-600 mb-6">
    Gestión de pagos de alumnos
</p>

<!-- RESUMEN -->
<div class="grid md:grid-cols-3 gap-4 mb-6">

    <div class="bg-white p-4 rounded shadow">
        <h3 class="text-gray-600">Total Cobrado</h3>
        <p class="text-2xl font-bold text-green-600" id="totalPagadas">S/ 0</p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <h3 class="text-gray-600">Pendiente</h3>
        <p class="text-2xl font-bold text-yellow-500" id="totalPendiente">S/ 0</p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <h3 class="text-gray-600">Atrasadas</h3>
        <p class="text-2xl font-bold text-red-500" id="totalAtrasadas">S/ 0</p>
    </div>

</div>

<!-- ========================= -->
<!-- TABLA MATRÍCULAS -->
<!-- ========================= -->

<h3 class="text-xl font-bold mb-2">
    Matrículas Pendientes
</h3>

<div class="bg-white p-4 rounded shadow mb-6 overflow-x-auto">

<table class="min-w-full text-center">

<thead class="bg-gray-100">
<tr>
<th class="p-2">Alumno</th>
<th class="p-2">Monto Matrícula</th>
<th class="p-2">Vencimiento</th>
<th class="p-2">Estado</th>
<th class="p-2">Acción</th>
</tr>
</thead>

<tbody id="tablaMatriculas"></tbody>

</table>

</div>

<!-- BUSCADOR -->
<input id="buscar"
    placeholder="Buscar alumno..."
    class="w-full md:w-1/3 border px-3 py-2 mb-4 rounded">

<!-- BOTÓN CREAR PLAN -->
<button onclick="abrirPlanGeneral()"
    class="mb-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
    Crear Plan de Pago
</button>

<!-- ========================= -->
<!-- TABLA CUOTAS -->
<!-- ========================= -->

<h3 class="text-xl font-bold mb-2">
    Control de Cuotas
</h3>

<p class="text-sm text-gray-600 mb-4">
    Se está trabajando en este bloque todavía. Aquí aparecen las matrículas que ya pagaron mientras se completa el control de cuotas.
</p>

<div class="bg-white p-6 rounded shadow overflow-x-auto">

<table class="min-w-full">

<thead class="bg-gray-100">
<tr>
<th class="p-3">Alumno</th>
<th class="p-3">Cuota</th>
<th class="p-3">Monto</th>
<th class="p-3">Vencimiento</th>
<th class="p-3">Estado</th>
<th class="p-3">Acción</th>
</tr>
</thead>

<tbody id="tablaCuotas"></tbody>

</table>

</div>

<!-- MODAL PAGO -->

<div id="modal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">

<div class="bg-white p-6 rounded shadow w-96">

<h3 class="text-xl font-semibold mb-4 text-center">
    Registrar Pago
</h3>

<div class="border rounded p-3 mb-4 bg-gray-50">

<p class="text-sm text-gray-600">
Alumno
</p>

<p id="modalAlumno"
   class="font-bold text-lg">
</p>

<p class="text-sm text-gray-600 mt-2">
Concepto
</p>

<p id="modalCuota"
   class="font-semibold">
</p>

<p class="text-sm text-gray-600 mt-2">
Monto a pagar
</p>

<p id="modalMonto"
   class="text-green-600 text-2xl font-bold">
</p>

</div>

<label class="font-semibold">
Fecha de pago
</label>

<input type="date"
       id="fechaPago"
       class="w-full border px-3 py-2 mb-3 rounded">

<label class="font-semibold">
Método de pago
</label>

<select id="metodoPago"
        class="w-full border px-3 py-2 mb-4 rounded">

<option>Efectivo</option>
<option>Yape / Plin</option>
<option>Transferencia</option>

</select>

<div class="flex justify-end gap-2">

<button onclick="cerrarModal()"
class="bg-gray-300 px-4 py-2 rounded">
Cancelar
</button>

<button onclick="confirmarPago()"
class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">
Confirmar Pago
</button>

</div>

</div>

</div>


<script>

let cuotas = [];
let matriculas = [];
let seleccionado = null;
let tipoSeleccionado = null;

// =========================
// FETCH MATRÍCULAS
// =========================

function fetchMatriculas(){

fetch("../process/get_matriculas_pendientes.php")

.then(res => res.json())

.then(res => {

if(res.status === "success"){

matriculas = res.data;

renderMatriculas();

}

});

}

// =========================
// FETCH CUOTAS
// =========================

function fetchCuotas(){

fetch("../process/get_cuotas.php")

.then(res => res.json())

.then(res => {

if(res.status === "success"){

cuotas = res.data;

renderCuotas();

}

});

}

// =========================
// RENDER MATRÍCULAS
// =========================

function renderMatriculas(){

let tabla =
document.getElementById("tablaMatriculas");

tabla.innerHTML = "";

matriculas.forEach((m,i)=>{

tabla.innerHTML += `

<tr class="border-t">

<td class="p-2">
${m.alumno}
</td>

<td class="p-2">
S/ ${m.monto_matricula}
</td>

<td class="p-2">
${m.fecha_vencimiento}
</td>

<td class="p-2">

<span class="bg-yellow-200 px-2 py-1 rounded">
${m.estado}
</span>

</td>

<td class="p-2">

<button onclick="abrirModalMatricula(${i})"
class="bg-blue-500 text-white px-2 py-1 rounded">
Pagar
</button>

</td>

</tr>

`;

});

}

// =========================
// RENDER CUOTAS
// =========================

function renderCuotas(){

let tabla =
document.getElementById("tablaCuotas");

tabla.innerHTML = "";

cuotas.forEach((c,i)=>{

let color = "bg-yellow-200";

if(c.estado == "Pagada")
color = "bg-green-200";

if(c.estado == "Atrasada")
color = "bg-red-200";

tabla.innerHTML += `

<tr class="border-t text-center">

<td class="p-2">
${c.alumno}
</td>

<td class="p-2">
${c.numero_cuota == 0 ? 'Matrícula' : 'Cuota ' + c.numero_cuota}
</td>

<td class="p-2">
S/ ${c.monto_cuota}
</td>

<td class="p-2">
${c.fecha_vencimiento ?? ""}
</td>

<td class="p-2">

<span class="px-2 py-1 rounded ${color}">
${c.estado}
</span>

</td>

<td class="p-2">

${c.estado != "Pagada"
? `<button onclick="abrirModalCuota(${i})"
class="bg-blue-500 text-white px-2 py-1 rounded">
Pagar
</button>`
: "-"}

</td>

</tr>

`;

});

calcularTotales();

}

// =========================
// ABRIR MODAL MATRÍCULA
// =========================

function abrirModalMatricula(i){

tipoSeleccionado = "matricula";

seleccionado = i;

document.getElementById("modalAlumno").innerText =
matriculas[i].alumno;

document.getElementById("modalCuota").innerText =
"Matrícula";

document.getElementById("modalMonto").innerText =
"S/ " + matriculas[i].monto_matricula;

document.getElementById("modal")
.classList.remove("hidden");

document.getElementById("modal")
.classList.add("flex");

document.getElementById("fechaPago").value =
new Date().toISOString().split("T")[0];


}

// =========================
// ABRIR MODAL CUOTA
// =========================

function abrirModalCuota(i){

tipoSeleccionado = "cuota";

seleccionado = i;

document.getElementById("modalAlumno").innerText =
cuotas[i].alumno;

document.getElementById("modalCuota").innerText =
"Cuota " + cuotas[i].numero_cuota;

document.getElementById("modalMonto").innerText =
"S/ " + cuotas[i].monto_cuota;

document.getElementById("modal")
.classList.remove("hidden");

document.getElementById("modal")
.classList.add("flex");

document.getElementById("fechaPago").value =
new Date().toISOString().split("T")[0];


}

// =========================
// CONFIRMAR PAGO
// =========================

function confirmarPago(){

let fecha =
document.getElementById("fechaPago").value;

let metodo =
document.getElementById("metodoPago").value;

if(!fecha){

alert("Seleccione una fecha");

return;

}

let id;

if(tipoSeleccionado === "matricula"){

id =
matriculas[seleccionado].id_matricula;

}else{

id =
cuotas[seleccionado].id_cuota;

}

fetch("../process/registrar_pago.php",{

method:"POST",

headers:{
"Content-Type":
"application/x-www-form-urlencoded"
},

body:

"id_cuota=" + id +

"&numero_cuota=" +
(tipoSeleccionado === "matricula"
? 0
: cuotas[seleccionado].numero_cuota)

+

"&fecha_pago=" + fecha +

"&metodo_pago=" + metodo

})

.then(res => res.json())

.then(res => {

if(res.status === "success"){

cerrarModal();

fetchMatriculas();

fetchCuotas();

if(tipoSeleccionado === "matricula"){

if(confirm(
"Alumno matriculado correctamente.\n\n¿Deseas generar el voucher de pago?"
)){

generarVoucher(id);

}

}else{

alert("Pago registrado correctamente");

}

}else{

alert(res.message);

}

});

}


function generarVoucher(id){

window.open(

"../process/voucher_pago.php?id=" + id,

"_blank"

);

}



function cerrarModal(){

let modal =
document.getElementById("modal");

modal.classList.add("hidden");

modal.classList.remove("flex");

}

// =========================
// TOTALES
// =========================

function calcularTotales(){

let pagado=0;
let pendiente=0;
let atrasado=0;

cuotas.forEach(c=>{

let monto =
Number(c.monto_cuota);

if(c.estado=="Pagada")
pagado += monto;

if(c.estado=="Pendiente")
pendiente += monto;

if(c.estado=="Atrasada")
atrasado += monto;

});

document.getElementById("totalPagadas").innerText =
"S/ " + pagado;

document.getElementById("totalPendiente").innerText =
"S/ " + pendiente;

document.getElementById("totalAtrasadas").innerText =
"S/ " + atrasado;

}

// =========================
// INIT
// =========================

document.addEventListener(
"DOMContentLoaded",

() => {

fetchMatriculas();

fetchCuotas();

});

</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>


