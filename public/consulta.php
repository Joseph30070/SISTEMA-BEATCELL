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

<!-- ========================= -->
<!-- RESUMEN -->
<!-- ========================= -->
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
<!-- TABLA -->
<!-- ========================= -->
<div class="bg-white p-6 rounded shadow overflow-x-auto">

<table class="min-w-full">

<thead class="bg-gray-100">
<tr>
<th class="p-3">Alumno</th>
<th class="p-3">Cuota</th>
<th class="p-3">Monto</th>
<th class="p-3">Estado</th>
<th class="p-3">Acción</th>
</tr>
</thead>

<tbody id="tablaCuotas"></tbody>

</table>

</div>

<!-- ========================= -->
<!-- MODAL PAGO -->
<!-- ========================= -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">

<div class="bg-white p-6 rounded shadow w-80">

<h3 class="text-lg font-semibold mb-3">Registrar Pago</h3>

<p id="modalAlumno" class="font-bold"></p>
<p id="modalCuota"></p>
<p id="modalMonto" class="text-green-600 text-xl font-bold mb-3"></p>

<label>Fecha</label>
<input type="date" id="fechaPago" class="w-full border px-2 py-1 mb-3">

<label>Método</label>
<select id="metodoPago" class="w-full border px-2 py-1 mb-3">
    <option>Efectivo</option>
    <option>Yape / Plin</option>
    <option>Transferencia</option>
</select>

<div class="flex justify-end gap-2">
    <button onclick="cerrarModal()" class="bg-gray-300 px-3 py-1 rounded">
        Cancelar
    </button>

    <button onclick="confirmarPago()" class="bg-teal-600 text-white px-3 py-1 rounded">
        Confirmar
    </button>
</div>

</div>
</div>

<!-- ========================= -->
<!-- MODAL PLAN -->
<!-- ========================= -->
<div id="modalPlan" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">

<div class="bg-white p-6 rounded shadow w-96">

<h3 class="text-lg font-semibold mb-3">Crear Plan de Pago</h3>

<p class="font-bold mb-2" id="planAlumno">Alumno</p>

<label>Monto Total</label>
<input type="number" id="montoTotal" class="w-full border px-2 py-1 mb-2">

<label>Descuento</label>
<input type="number" id="descuento" class="w-full border px-2 py-1 mb-2">

<label>
<input type="checkbox" id="beca"> Beca completa
</label>

<label class="block mt-2">Cantidad de Cuotas</label>
<select id="numCuotas" class="w-full border px-2 py-1 mb-3">
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
</select>

<!-- PREVIEW -->
<div class="bg-gray-100 p-3 rounded mb-3">
<h4 class="font-semibold mb-2">Vista previa</h4>
<div id="previewCuotas"></div>
</div>

<div class="flex justify-end gap-2">
<button onclick="cerrarPlan()" class="bg-gray-300 px-3 py-1 rounded">Cancelar</button>
<button onclick="guardarPlan()" class="bg-teal-600 text-white px-3 py-1 rounded">Guardar</button>
</div>

</div>
</div>

<!-- ========================= -->
<!-- JS -->
<!-- ========================= -->
<script>

let cuotas = [
{ alumno:"Juan Pérez", cuota:"Cuota 1", monto:100, estado:"Pagada" },
{ alumno:"Juan Pérez", cuota:"Cuota 2", monto:100, estado:"Pendiente" },
{ alumno:"María López", cuota:"Cuota 1", monto:120, estado:"Atrasada" },
{ alumno:"Carlos Ruiz", cuota:"Cuota 1", monto:150, estado:"Pendiente" }
];

let seleccionado = null;

// RENDER
function render(){

let tabla = document.getElementById("tablaCuotas");
tabla.innerHTML = "";

cuotas.forEach((c,i)=>{

let color = "bg-yellow-200";
if(c.estado=="Pagada") color="bg-green-200";
if(c.estado=="Atrasada") color="bg-red-200";

tabla.innerHTML += `
<tr class="border-t text-center">
<td class="p-2">${c.alumno}</td>
<td class="p-2">${c.cuota}</td>
<td class="p-2">S/ ${c.monto}</td>
<td class="p-2"><span class="px-2 py-1 rounded ${color}">${c.estado}</span></td>
<td class="p-2">
${c.estado!="Pagada" ? `<button onclick="abrirModal(${i})" class="bg-blue-500 text-white px-2 py-1 rounded">Pagar</button>` : "-"}
</td>
</tr>
`;
});

calcularTotales();
}

// TOTALES
function calcularTotales(){
let pagado=0, pendiente=0, atrasado=0;

cuotas.forEach(c=>{
if(c.estado=="Pagada") pagado+=c.monto;
if(c.estado=="Pendiente") pendiente+=c.monto;
if(c.estado=="Atrasada") atrasado+=c.monto;
});

document.getElementById("totalPagadas").innerText="S/ "+pagado;
document.getElementById("totalPendiente").innerText="S/ "+pendiente;
document.getElementById("totalAtrasadas").innerText="S/ "+atrasado;
}

// MODAL PAGO
function abrirModal(i){
seleccionado = i;

document.getElementById("modalAlumno").innerText = cuotas[i].alumno;
document.getElementById("modalCuota").innerText = cuotas[i].cuota;
document.getElementById("modalMonto").innerText = "S/ "+cuotas[i].monto;

document.getElementById("modal").classList.remove("hidden");
document.getElementById("modal").classList.add("flex");
}

function cerrarModal(){
document.getElementById("modal").classList.add("hidden");
}

function confirmarPago(){
cuotas[seleccionado].estado="Pagada";
cerrarModal();
render();
}

// PLAN
function abrirPlanGeneral(){
document.getElementById("planAlumno").innerText = "Alumno Demo";
document.getElementById("modalPlan").classList.remove("hidden");
document.getElementById("modalPlan").classList.add("flex");
}

function cerrarPlan(){
document.getElementById("modalPlan").classList.add("hidden");
}

// 🔥 MODO PRO CUOTAS
function calcularPreview(){

let monto = Number(document.getElementById("montoTotal").value) || 0;
let descuento = Number(document.getElementById("descuento").value) || 0;
let cuotasNum = Number(document.getElementById("numCuotas").value) || 1;
let beca = document.getElementById("beca").checked;

if(descuento > monto) descuento = monto;

let total = beca ? 0 : (monto - descuento);

// base
let base = Math.floor((total / cuotasNum) * 100) / 100;

// diferencia
let suma = base * cuotasNum;
let diferencia = Math.round((total - suma) * 100) / 100;

let html = "";

for(let i=1;i<=cuotasNum;i++){

let valor = base;

if(i === cuotasNum){
valor = Math.round((base + diferencia) * 100) / 100;
}

html += `<div>Cuota ${i}: S/ ${valor}</div>`;
}

document.getElementById("previewCuotas").innerHTML = html;
}

// EVENTOS (FIX BUG)
document.addEventListener("DOMContentLoaded", () => {

document.getElementById("montoTotal").addEventListener("input", calcularPreview);
document.getElementById("descuento").addEventListener("input", calcularPreview);
document.getElementById("numCuotas").addEventListener("change", calcularPreview);
document.getElementById("beca").addEventListener("change", calcularPreview);

});

// GUARDAR
function guardarPlan(){
alert("Plan creado correctamente (demo)");
cerrarPlan();
}

render();

</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>