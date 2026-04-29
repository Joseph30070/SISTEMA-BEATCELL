<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'SECRETARIO']);

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
<!-- TABLA MATRÍCULAS          -->
<!-- ========================= -->

<h3 class="text-xl font-bold mb-2">
    Matrículas Pendientes
</h3>

<div class="bg-white p-4 rounded shadow mb-6 overflow-x-auto">

<table class="min-w-full text-center">
<thead class="bg-gray-100">
<tr>
<th class="p-2">Alumno</th>
<th class="p-2">Tipo</th>
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
    class="w-full md:w-1/3 border px-3 py-2 mb-4 rounded"
    oninput="filtrarCuotas()">

<!-- ========================= -->
<!-- TABLA CUOTAS              -->
<!-- ========================= -->

<h3 class="text-xl font-bold mb-2">
    Control de Cuotas
</h3>

<div class="bg-white p-6 rounded shadow overflow-x-auto">

<table class="min-w-full">
<thead class="bg-gray-100">
<tr>
<th class="p-3 text-left font-semibold text-gray-700 border-b-2 border-gray-300">Alumno</th>
<th class="p-3 text-left font-semibold text-gray-700 border-b-2 border-gray-300">DNI</th>
<th class="p-3 text-left font-semibold text-gray-700 border-b-2 border-gray-300">Curso</th>
<th class="p-3 text-left font-semibold text-gray-700 border-b-2 border-gray-300">Grupo</th>
<th class="p-3 text-center font-semibold text-gray-700 border-b-2 border-gray-300">Cuota</th>
<th class="p-3 text-center font-semibold text-gray-700 border-b-2 border-gray-300">Monto</th>
<th class="p-3 text-center font-semibold text-gray-700 border-b-2 border-gray-300">Pagado</th>
<th class="p-3 text-center font-semibold text-gray-700 border-b-2 border-gray-300">Vencimiento</th>
<th class="p-3 text-center font-semibold text-gray-700 border-b-2 border-gray-300">Estado</th>
<th class="p-3 text-center font-semibold text-gray-700 border-b-2 border-gray-300">Acción</th>
</tr>
</thead>
<tbody id="tablaCuotas"></tbody>
</table>

</div>

<!-- ============================================ -->
<!-- MODAL PAGO                                   -->
<!-- ============================================ -->

<div id="modal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-40">

<div class="bg-white p-6 rounded shadow w-96">

<h3 class="text-xl font-semibold mb-4 text-center">
    Registrar Pago
</h3>

<div class="border rounded p-3 mb-4 bg-gray-50">

    <p class="text-sm text-gray-600">Alumno</p>
    <p id="modalAlumno" class="font-bold text-lg"></p>

    <p class="text-sm text-gray-600 mt-2">Concepto</p>
    <p id="modalCuota" class="font-semibold"></p>

    <p class="text-sm text-gray-600 mt-2">Monto total</p>
    <p id="modalMonto" class="text-green-600 text-2xl font-bold"></p>

    <!-- saldo pendiente solo si hay pago parcial previo -->
    <div id="modalSaldoWrap" class="hidden mt-1">
        <p class="text-sm text-gray-600">Saldo pendiente</p>
        <p id="modalSaldo" class="text-orange-500 font-semibold"></p>
    </div>

</div>

<!-- Monto a pagar -->
<label class="font-semibold">Monto a pagar (S/)</label>
<div class="flex items-center gap-2 mb-3">
    <input type="number" id="montoPagar" min="0.01" step="0.01"
        placeholder="Dejar vacío para pagar total"
        class="w-full border px-3 py-2 rounded">
    <button type="button" onclick="pagarTotal()"
        class="text-xs bg-gray-200 px-2 py-2 rounded whitespace-nowrap hover:bg-gray-300">
        Total
    </button>
</div>

<label class="font-semibold">Fecha de pago</label>
<input type="date" id="fechaPago" class="w-full border px-3 py-2 mb-3 rounded">

<label class="font-semibold">Método de pago</label>
<select id="metodoPago" class="w-full border px-3 py-2 mb-4 rounded">
    <option>Efectivo</option>
    <option>Yape / Plin</option>
    <option>Transferencia</option>
</select>

<div class="flex justify-end gap-2">
    <button onclick="cerrarModal()" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
    <button onclick="confirmarPago()" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">Confirmar Pago</button>
</div>

</div>
</div>


<!-- ============================================ -->
<!-- MODAL PLAN DE PAGO                           -->
<!-- ============================================ -->

<div id="modalPlan"
     class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">

<div class="bg-white rounded shadow-xl w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">

    <div class="bg-teal-700 text-white px-6 py-4 rounded-t flex justify-between items-center">
        <div>
            <h3 class="text-xl font-bold">Crear Plan de Pago</h3>
            <p id="planAlumnoNombre" class="text-teal-200 text-sm"></p>
        </div>
        <button onclick="cerrarModalPlan()" class="text-white text-2xl leading-none">&times;</button>
    </div>

    <div class="p-6">

        <div class="bg-gray-50 border rounded p-3 mb-5 text-sm text-gray-700 grid grid-cols-2 gap-2">
            <div><span class="font-semibold">Curso:</span> <span id="planCurso"></span></div>
            <div><span class="font-semibold">Grupo:</span> <span id="planGrupo"></span></div>
            <div><span class="font-semibold">Tipo de ciclo:</span> <span id="planTipoCiclo"></span></div>
        </div>

        <!-- PROMOCIÓN -->
        <div class="mb-5">
            <label class="font-semibold block mb-1">Promoción</label>

            <div id="promoAsignadaBadge" class="hidden mb-2">
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                    ✓ Promoción activa: <span id="promoAsignadaNombre"></span>
                </span>
            </div>

            <select id="selectPromo" class="w-full border px-3 py-2 rounded" onchange="aplicarPromo()">
                <option value="">— Sin promoción —</option>
            </select>

            <p id="promoDescripcion" class="text-xs text-gray-500 mt-1 hidden"></p>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="font-semibold block mb-1">Tipo de ciclo</label>
                <select id="planCicloSelect" class="w-full border px-3 py-2 rounded" onchange="recalcularPlan()">
                    <option value="Normal">Normal (4 meses - S/150)</option>
                    <option value="Acelerado">Acelerado (2 meses - S/250)</option>
                    <option value="Especializacion">Especialización (1 pago - S/300)</option>
                </select>
            </div>
            <div>
                <label class="font-semibold block mb-1">Cantidad de cuotas</label>
                <input type="number" id="planCantCuotas" min="1" max="12"
                    class="w-full border px-3 py-2 rounded"
                    onchange="generarFilasCuotas()">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="font-semibold block mb-1">Monto mensual (S/)</label>
                <input type="number" id="planMontoBase" min="0" step="0.01"
                    class="w-full border px-3 py-2 rounded"
                    onchange="generarFilasCuotas()">
            </div>
            <div>
                <label class="font-semibold block mb-1">Fecha inicio</label>
                <input type="date" id="planFechaInicio"
                    class="w-full border px-3 py-2 rounded"
                    onchange="generarFilasCuotas()">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="font-semibold block mb-1">Tipo de descuento</label>
                <select id="planTipoDescuento" class="w-full border px-3 py-2 rounded" onchange="recalcularDescuento()">
                    <option value="Ninguno">Ninguno</option>
                    <option value="Porcentaje">Porcentaje (%)</option>
                    <option value="Monto fijo">Monto fijo (S/)</option>
                    <option value="Beca">Beca (100%)</option>
                </select>
            </div>
            <div>
                <label class="font-semibold block mb-1">Valor descuento</label>
                <input type="number" id="planValorDescuento" min="0" step="0.01"
                    placeholder="0"
                    class="w-full border px-3 py-2 rounded"
                    onchange="recalcularDescuento()">
            </div>
        </div>

        <div class="bg-teal-50 border border-teal-200 rounded p-3 mb-5 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                Monto base: <span id="resumenBase" class="font-semibold text-gray-800">S/ 0</span>
                <span id="resumenDescuentoTexto" class="text-red-500 ml-2 hidden"></span>
            </div>
            <div class="text-xl font-bold text-teal-700">
                Total: <span id="resumenTotal">S/ 0</span>
            </div>
        </div>

        <div class="mb-5">
            <h4 class="font-semibold mb-2">Cuotas a generar</h4>
            <p class="text-xs text-gray-500 mb-2">Puedes editar monto y fecha de cada cuota antes de confirmar.</p>
            <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 border">N°</th>
                        <th class="p-2 border">Monto (S/)</th>
                        <th class="p-2 border">Fecha vencimiento</th>
                    </tr>
                </thead>
                <tbody id="filassCuotas"></tbody>
            </table>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button onclick="cerrarModalPlan()" class="bg-gray-300 px-5 py-2 rounded hover:bg-gray-400">Cancelar</button>
            <button onclick="confirmarPlan()" class="bg-teal-600 text-white px-5 py-2 rounded hover:bg-teal-700 font-semibold">Confirmar Plan</button>
        </div>

    </div>
</div>
</div>

<script>
const ROLE = <?php echo json_encode($_SESSION['rol'] ?? ''); ?>;
</script>

<script>


let cuotas           = [];
let matriculas       = [];
let seleccionado     = null;
let tipoSeleccionado = null;
let montoTotalActual = 0;

let planMatriculaActual = null;
let promosDisponibles   = [];

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

    let tabla = document.getElementById("tablaMatriculas");
    tabla.innerHTML = "";

    matriculas.forEach((m, i) => {

        const esEspecializacion = m.tipo_ciclo === 'Especialización';

        const badgeTipo = esEspecializacion
            ? `<span class="bg-purple-200 px-2 py-1 rounded text-xs">
                Especialización
               </span>`
            : `<span class="bg-blue-200 px-2 py-1 rounded text-xs">
                Regular
               </span>`;

        let botonAccion = "";

        // =========================
        // SECRETARIO
        // =========================

        if (ROLE === "SECRETARIO") {

            if (!esEspecializacion) {

                botonAccion = `<button onclick="abrirModalMatricula(${i})"
                    class="bg-blue-500 text-white px-2 py-1 rounded">
                    Pagar
                </button>`;

            } else {

                botonAccion = `<span class="text-gray-400 text-xs">
                    No permitido
                </span>`;

            }

        }

        // =========================
        // ADMINISTRADOR
        // =========================

        else {

            botonAccion = esEspecializacion
                ? `<button onclick="confirmarEspecializacion(${i})"
                    class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600">
                    Confirmar
                </button>`
                : `<button onclick="abrirModalMatricula(${i})"
                    class="bg-blue-500 text-white px-2 py-1 rounded">
                    Pagar
                </button>`;
        }

        tabla.innerHTML += `
        <tr class="border-t">
            <td class="p-2">${m.alumno}</td>
            <td class="p-2">${badgeTipo}</td>
            <td class="p-2">S/ ${m.monto_matricula}</td>
            <td class="p-2">${m.fecha_vencimiento}</td>
            <td class="p-2">
                <span class="bg-yellow-200 px-2 py-1 rounded">
                    ${m.estado}
                </span>
            </td>
            <td class="p-2">${botonAccion}</td>
        </tr>
        `;
    });

}


// =========================
// GENERAR PDF PLAN
// =========================

function generarPDF(id_plan){
    window.open("../process/plan_pago_pdf.php?id_plan=" + id_plan, "_blank");
}

function confirmarEspecializacion(i){
    const matricula = matriculas[i];
    if(!confirm(`¿Confirmar especialización para ${matricula.alumno}? Se creará un plan de pago con una cuota de S/ 300.`)){
        return;
    }

    fetch("../process/confirmar_especializacion.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id_matricula=${matricula.id_matricula}`
    })
    .then(res => res.json())
    .then(res => {
        if(res.status === "success"){
            alert("Plan de pago creado exitosamente. Ahora puedes gestionar el pago en la tabla de cuotas.");
            fetchMatriculas();
            fetchCuotas();
            // Abrir PDF del plan
            window.open("../process/plan_pago_pdf.php?id_plan=" + res.id_plan, "_blank");
        } else {
            alert("Error: " + res.message);
        }
    })
    .catch(err => {
        alert("Error de conexión");
    });
}

// =========================
// RENDER CUOTAS (AGRUPADO)
// =========================

function renderCuotas(){
    let tabla = document.getElementById("tablaCuotas");
    tabla.innerHTML = "";

    const busqueda = document.getElementById("buscar").value.toLowerCase();

    let alumnosMap = {};
    cuotas.forEach((c, idx) => {
        if(busqueda && !(
            (c.alumno || '').toLowerCase().includes(busqueda) ||
            (c.dni || '').toLowerCase().includes(busqueda) ||
            (c.nombre_curso || '').toLowerCase().includes(busqueda) ||
            (c.nombre_grupo || '').toLowerCase().includes(busqueda)
        )) return;


        if(!alumnosMap[c.alumno]){
            alumnosMap[c.alumno] = {
                nombre: c.alumno,
                dni: c.dni,
                nombre_curso: c.nombre_curso,
                nombre_grupo: c.nombre_grupo,
                id_matricula: c.id_matricula,
                tiene_plan: c.tiene_plan,
                id_plan: null,
                cuotas: []
            };
        }

        // Actualizar id_plan si viene de una cuota real
        if(c.numero_cuota > 0 && c.id_plan){
            alumnosMap[c.alumno].id_plan = c.id_plan;
        }

        alumnosMap[c.alumno].cuotas.push({ ...c, idx: idx });
    });

    let alumnoIndex = 0;
    Object.keys(alumnosMap).forEach(nombreAlumno => {
        let alumno        = alumnosMap[nombreAlumno];
        let totalCuotas   = alumno.cuotas.length;
        let cuotasPagadas = alumno.cuotas.filter(c => c.estado === "Pagada").length;
        let estadoGeneral = cuotasPagadas === totalCuotas ? "Pagado" : "Pendiente";
        let colorGeneral  = estadoGeneral === "Pagado" ? "bg-green-100 text-green-800 border-green-300" : "bg-yellow-100 text-yellow-800 border-yellow-300";
        let safeId        = "alumno_" + alumnoIndex;

        tabla.innerHTML += `
        <tr class="border-b border-gray-200 bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors"
            onclick="toggleExpand('${safeId}')">
            <td class="p-3 font-bold text-gray-900">${alumno.nombre}</td>
            <td class="p-3 text-gray-700 font-mono text-sm">${alumno.dni || 'N/A'}</td>
            <td class="p-3 text-gray-700">
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">${alumno.nombre_curso || 'N/A'}</span>
            </td>
            <td class="p-3 text-gray-700">
                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-medium">${alumno.nombre_grupo || 'N/A'}</span>
            </td>
            <td class="p-3 text-center">
                <span class="text-xs bg-blue-100 px-2 py-1 rounded font-medium">${totalCuotas} cuota${totalCuotas !== 1 ? 's' : ''}</span>
            </td>
            <td class="p-3 text-center">
                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded font-medium">${cuotasPagadas}/${totalCuotas}</span>
            </td>
            <td class="p-3 text-center">
                <span class="px-2 py-1 rounded text-xs font-medium border ${colorGeneral}">${estadoGeneral}</span>
            </td>
            <td class="p-3 text-center">
                <span id="expandIcon_${safeId}" class="text-lg cursor-pointer transition-transform">▼</span>
                ${(ROLE === "ADMINISTRADOR" && alumno.id_plan) 
                    ? `<button onclick="generarPDF(${alumno.id_plan})"
                        class="bg-red-500 text-white px-2 py-1 rounded text-xs ml-2 hover:bg-red-600 transition-colors">
                        PDF
                    </button>` 
                    : ''}
            </td>

        </tr>
        `;

        alumno.cuotas.forEach((c) => {
            let color = "bg-yellow-100 text-yellow-800 border-yellow-300";
            if(c.estado === "Pagada")   color = "bg-green-100 text-green-800 border-green-300";
            if(c.estado === "Atrasada") color = "bg-red-100 text-red-800 border-red-300";
            if(c.estado === "Parcial")  color = "bg-orange-100 text-orange-800 border-orange-300";

            let btnAccion = "";

            // ==========================
            // SECRETARIO
            // ==========================
            if (ROLE === "SECRETARIO") {

                // NO puede hacer nada en cuotas
                btnAccion = `<span class="text-gray-400 text-xs">Solo lectura</span>`;

            } else {

                // ==========================
                // ADMINISTRADOR
                // ==========================

                if(c.numero_cuota == 0 && (!c.tiene_plan || c.tiene_plan == 0)){

                    btnAccion = `<button onclick="abrirPlanGeneral(${c.id_matricula})"
                        class="bg-teal-600 text-white px-3 py-1 rounded text-xs hover:bg-teal-700">
                        Crear Plan
                    </button>`;

                } else if(c.numero_cuota == 0 && c.tiene_plan == 1){

                    btnAccion = `<span class="text-gray-500 text-xs">Plan creado</span>`;

                } else if(c.estado === "Pagada"){

                    btnAccion = `<span class="text-green-600 text-xs">✓ Pagado</span>`;

                } else {

                    btnAccion = `<button onclick="abrirModalCuota(${c.idx})"
                        class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">
                        Pagar
                    </button>`;
                }
            }


            tabla.innerHTML += `
            <tr class="border-b border-gray-100 cuota-row-${safeId} hover:bg-gray-50 transition-colors" style="display:none;">
                <td class="p-2 pl-8 text-gray-600">
                    <span class="text-sm">└─ ${c.numero_cuota == 0 ? 'Matrícula' : 'Cuota ' + c.numero_cuota}</span>
                </td>
                <td class="p-2"></td>
                <td class="p-2"></td>
                <td class="p-2"></td>
                <td class="p-2 text-right font-semibold text-gray-900">S/ ${parseFloat(c.monto_cuota || 0).toFixed(2)}</td>
                <td class="p-2 text-right text-gray-700">S/ ${parseFloat(c.monto_pagado || 0).toFixed(2)}</td>
                <td class="p-2 text-center text-sm text-gray-700">${c.fecha_vencimiento ? new Date(c.fecha_vencimiento).toLocaleDateString('es-ES') : 'N/A'}</td>
                <td class="p-2 text-center">
                    <span class="px-2 py-1 rounded text-xs font-medium border ${color}">${c.estado}</span>
                </td>
                <td class="p-2 text-center">${btnAccion}</td>
            </tr>
            `;
        });

        alumnoIndex++;
    });

    calcularTotales();
}

// =========================
// TOGGLE EXPANDIR
// =========================

function toggleExpand(safeId){
    const rows = document.querySelectorAll('.cuota-row-' + safeId);
    const icon = document.getElementById('expandIcon_' + safeId);
    if(!icon) return;
    rows.forEach(row => {
        row.style.display = row.style.display === "none" ? "table-row" : "none";
    });
    icon.innerText = icon.innerText === "▼" ? "▶" : "▼";
}

// =========================
// BUSCADOR
// =========================

function filtrarCuotas(){ renderCuotas(); }

// =========================
// ABRIR MODAL MATRÍCULA
// =========================

function abrirModalMatricula(i){
    tipoSeleccionado = "matricula";
    seleccionado     = i;
    montoTotalActual = parseFloat(matriculas[i].monto_matricula);

    const saldoPendiente = montoTotalActual - parseFloat(matriculas[i].monto_pagado || 0);

    document.getElementById("modalAlumno").innerText = matriculas[i].alumno;
    document.getElementById("modalCuota").innerText  = "Matrícula";
    document.getElementById("modalMonto").innerText  = "S/ " + montoTotalActual.toFixed(2);

    const saldoWrap = document.getElementById("modalSaldoWrap");
    if(saldoPendiente < montoTotalActual){
        document.getElementById("modalSaldo").innerText = "S/ " + saldoPendiente.toFixed(2);
        saldoWrap.classList.remove("hidden");
    } else {
        saldoWrap.classList.add("hidden");
    }

    document.getElementById("montoPagar").value = "";
    document.getElementById("fechaPago").value  = new Date().toISOString().split("T")[0];
    document.getElementById("modal").classList.remove("hidden");
    document.getElementById("modal").classList.add("flex");
}

// =========================
// ABRIR MODAL CUOTA
// =========================

function abrirModalCuota(i){
    tipoSeleccionado = "cuota";
    seleccionado     = i;
    montoTotalActual = parseFloat(cuotas[i].monto_cuota);

    const montoPagado    = parseFloat(cuotas[i].monto_pagado || 0);
    const saldoPendiente = montoTotalActual - montoPagado;

    document.getElementById("modalAlumno").innerText = cuotas[i].alumno;
    document.getElementById("modalCuota").innerText  = "Cuota " + cuotas[i].numero_cuota;
    document.getElementById("modalMonto").innerText  = "S/ " + montoTotalActual.toFixed(2);

    const saldoWrap = document.getElementById("modalSaldoWrap");
    if(montoPagado > 0){
        document.getElementById("modalSaldo").innerText = "S/ " + saldoPendiente.toFixed(2);
        saldoWrap.classList.remove("hidden");
    } else {
        saldoWrap.classList.add("hidden");
    }

    document.getElementById("montoPagar").value = "";
    document.getElementById("fechaPago").value  = new Date().toISOString().split("T")[0];
    document.getElementById("modal").classList.remove("hidden");
    document.getElementById("modal").classList.add("flex");
}

// Botón "Total" rellena el saldo pendiente
function pagarTotal(){
    if(tipoSeleccionado === "matricula"){
        const saldo = montoTotalActual - parseFloat(matriculas[seleccionado].monto_pagado || 0);
        document.getElementById("montoPagar").value = saldo.toFixed(2);
    } else {
        const saldo = parseFloat(cuotas[seleccionado].monto_cuota) - parseFloat(cuotas[seleccionado].monto_pagado || 0);
        document.getElementById("montoPagar").value = saldo.toFixed(2);
    }
}

// =========================
// CONFIRMAR PAGO
// =========================

function confirmarPago(){
    let fecha      = document.getElementById("fechaPago").value;
    let metodo     = document.getElementById("metodoPago").value;
    let montoPagar = document.getElementById("montoPagar").value;

    if(!fecha){ alert("Seleccione una fecha"); return; }

    let id;
    if(tipoSeleccionado === "matricula"){
        id = matriculas[seleccionado].id_matricula;
    } else {
        id = cuotas[seleccionado].id_cuota;
    }

    let body =
        "id_cuota="      + id +
        "&numero_cuota=" + (tipoSeleccionado === "matricula" ? 0 : cuotas[seleccionado].numero_cuota) +
        "&fecha_pago="   + fecha +
        "&metodo_pago="  + encodeURIComponent(metodo);

    if(montoPagar && parseFloat(montoPagar) > 0){
        body += "&monto_pagar=" + parseFloat(montoPagar);
    }

    fetch("../process/registrar_pago.php", {
        method:  "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:    body
    })
    .then(res => res.json())
    .then(res => {
        if(res.status === "success"){
            cerrarModal();
            fetchMatriculas();
            fetchCuotas();

            if(tipoSeleccionado === "matricula" && res.estado === "Pagada"){
                if(confirm("Matrícula pagada correctamente.\n\n¿Deseas generar el voucher de pago?")){
                    generarVoucher(id, 'matricula');
                }
            } else if(tipoSeleccionado === "cuota"){
                if(res.estado === "Pagada"){
                    if(confirm("Cuota pagada completamente.\n\n¿Deseas generar el voucher de pago?")){
                        generarVoucher(id, 'cuota');
                    }
                } else if(res.estado === "Parcial"){
                    if(confirm("Pago parcial registrado.\nSaldo pendiente: S/ " + parseFloat(res.restante).toFixed(2) + "\n\n¿Deseas generar el voucher de pago?")){
                        generarVoucher(id, 'cuota');
                    }
                }
            } else {
                alert("Pago registrado correctamente.");
            }
        } else {
            alert("Error: " + res.message);
        }
    });
}

function generarVoucher(id, tipo = 'matricula'){
    if(tipo === 'cuota'){
        window.open("../process/voucher_cuota.php?id=" + id, "_blank");
    } else {
        window.open("../process/voucher_pago.php?id=" + id, "_blank");
    }
}

function cerrarModal(){
    let modal = document.getElementById("modal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

// =========================
// TOTALES
// =========================

function calcularTotales(){
    let pagado = 0, pendiente = 0, atrasado = 0;
    cuotas.forEach(c => {
        let monto = Number(c.monto_cuota);
        if(c.estado === "Pagada")                              pagado    += monto;
        if(c.estado === "Pendiente" || c.estado === "Parcial") pendiente += monto;
        if(c.estado === "Atrasada")                            atrasado  += monto;
    });
    document.getElementById("totalPagadas").innerText   = "S/ " + pagado;
    document.getElementById("totalPendiente").innerText = "S/ " + pendiente;
    document.getElementById("totalAtrasadas").innerText = "S/ " + atrasado;
}

// ===================================
// MODAL PLAN
// ===================================

function abrirPlanGeneral(id_matricula){
    fetch("../process/get_plan_pago.php?id_matricula=" + id_matricula)
    .then(res => res.json())
    .then(res => {
        if(res.status !== "success"){ alert(res.message); return; }
        if(res.tiene_plan){ alert("Este alumno ya tiene un plan de pago creado."); return; }

        planMatriculaActual = res.matricula;
        promosDisponibles   = res.promos_disponibles || [];

        document.getElementById("planAlumnoNombre").innerText = res.matricula.alumno;
        document.getElementById("planCurso").innerText        = res.matricula.nombre_curso;
        document.getElementById("planGrupo").innerText        = res.matricula.nombre_grupo;
        document.getElementById("planTipoCiclo").innerText    = res.matricula.tipo_ciclo || "No definido";

        const badge = document.getElementById("promoAsignadaBadge");
        if(res.promo_asignada){
            document.getElementById("promoAsignadaNombre").innerText = res.promo_asignada.nombre_promocion;
            badge.classList.remove("hidden");
        } else {
            badge.classList.add("hidden");
        }

        const selectPromo = document.getElementById("selectPromo");
        selectPromo.innerHTML = '<option value="">— Sin promoción —</option>';
        promosDisponibles.forEach(p => {
            const opt        = document.createElement("option");
            opt.value        = p.id_promocion;
            opt.textContent  = p.nombre_promocion;
            opt.dataset.desc = p.descripcion || "";
            if(res.promo_asignada && res.promo_asignada.id_promocion == p.id_promocion){
                opt.selected = true;
            }
            selectPromo.appendChild(opt);
        });

        const cicloAlumno = res.matricula.tipo_ciclo || "Normal";
        const selectCiclo = document.getElementById("planCicloSelect");
        for(let opt of selectCiclo.options){
            if(opt.value === cicloAlumno){ opt.selected = true; break; }
        }

        document.getElementById("planFechaInicio").value    = new Date().toISOString().split("T")[0];
        document.getElementById("planTipoDescuento").value  = "Ninguno";
        document.getElementById("planValorDescuento").value = "";

        recalcularPlan();

        document.getElementById("modalPlan").classList.remove("hidden");
        document.getElementById("modalPlan").classList.add("flex");
    });
}

function cerrarModalPlan(){
    document.getElementById("modalPlan").classList.add("hidden");
    document.getElementById("modalPlan").classList.remove("flex");
    planMatriculaActual = null;
}

const TARIFARIO = {
    Normal:          { monto: 150, cuotas: 4 },
    Acelerado:       { monto: 250, cuotas: 2 },
    Especializacion: { monto: 300, cuotas: 1 }
};

const TARIFARIO_PROMO = {
    Normal:    100,
    Acelerado: 200
};

function recalcularPlan(){
    const ciclo     = document.getElementById("planCicloSelect").value;
    const tarifario = TARIFARIO[ciclo] || TARIFARIO["Normal"];
    const idPromo   = document.getElementById("selectPromo").value;

    let montoBase = tarifario.monto;
    if(idPromo && TARIFARIO_PROMO[ciclo]) montoBase = TARIFARIO_PROMO[ciclo];

    document.getElementById("planMontoBase").value  = montoBase;
    document.getElementById("planCantCuotas").value = tarifario.cuotas;
    recalcularDescuento();
}

function aplicarPromo(){
    const select = document.getElementById("selectPromo");
    const opt    = select.options[select.selectedIndex];
    const desc   = opt ? opt.dataset.desc : "";
    const pDesc  = document.getElementById("promoDescripcion");
    if(desc){ pDesc.innerText = desc; pDesc.classList.remove("hidden"); }
    else     { pDesc.classList.add("hidden"); }
    recalcularPlan();
}

function recalcularDescuento(){
    const montoBase  = parseFloat(document.getElementById("planMontoBase").value)      || 0;
    const cantCuotas = parseInt(document.getElementById("planCantCuotas").value)       || 1;
    const tipoDesc   = document.getElementById("planTipoDescuento").value;
    const valorDesc  = parseFloat(document.getElementById("planValorDescuento").value) || 0;

    let montoFinal = montoBase;
    let textoDesc  = "";

    if(tipoDesc === "Porcentaje"){ montoFinal = montoBase - (montoBase * valorDesc / 100); textoDesc = `- ${valorDesc}%`; }
    if(tipoDesc === "Monto fijo"){ montoFinal = montoBase - valorDesc; textoDesc = `- S/ ${valorDesc}`; }
    if(tipoDesc === "Beca")      { montoFinal = 0; textoDesc = "Beca 100%"; }
    if(montoFinal < 0) montoFinal = 0;

    document.getElementById("resumenBase").innerText  = "S/ " + montoBase.toFixed(2);
    document.getElementById("resumenTotal").innerText = "S/ " + (montoFinal * cantCuotas).toFixed(2);

    const spanDesc = document.getElementById("resumenDescuentoTexto");
    if(textoDesc){ spanDesc.innerText = textoDesc; spanDesc.classList.remove("hidden"); }
    else          { spanDesc.classList.add("hidden"); }

    generarFilasCuotas();
}

function generarFilasCuotas(){
    const montoBase   = parseFloat(document.getElementById("planMontoBase").value)      || 0;
    const cantCuotas  = parseInt(document.getElementById("planCantCuotas").value)       || 1;
    const tipoDesc    = document.getElementById("planTipoDescuento").value;
    const valorDesc   = parseFloat(document.getElementById("planValorDescuento").value) || 0;
    const fechaInicio = document.getElementById("planFechaInicio").value;

    let montoCuota = montoBase;
    if(tipoDesc === "Porcentaje")  montoCuota = montoBase - (montoBase * valorDesc / 100);
    if(tipoDesc === "Monto fijo")  montoCuota = montoBase - valorDesc;
    if(tipoDesc === "Beca")        montoCuota = 0;
    if(montoCuota < 0) montoCuota = 0;

    const tbody = document.getElementById("filassCuotas");
    tbody.innerHTML = "";

    for(let i = 1; i <= cantCuotas; i++){
        let fechaVenc = "";
        if(fechaInicio){
            const d = new Date(fechaInicio);
            d.setMonth(d.getMonth() + i);
            fechaVenc = d.toISOString().split("T")[0];
        }
        tbody.innerHTML += `
        <tr>
            <td class="p-2 border text-center">${i}</td>
            <td class="p-2 border">
                <input type="number" min="0" step="0.01"
                    id="cuota_monto_${i}" value="${montoCuota.toFixed(2)}"
                    class="w-full border px-2 py-1 rounded text-sm">
            </td>
            <td class="p-2 border">
                <input type="date" id="cuota_fecha_${i}" value="${fechaVenc}"
                    class="w-full border px-2 py-1 rounded text-sm">
            </td>
        </tr>
        `;
    }
}

function confirmarPlan(){
    if(!planMatriculaActual){ alert("Error: no hay matrícula seleccionada"); return; }

    const cantCuotas  = parseInt(document.getElementById("planCantCuotas").value)       || 0;
    const montoBase   = parseFloat(document.getElementById("planMontoBase").value)      || 0;
    const fechaInicio = document.getElementById("planFechaInicio").value;
    const tipoDesc    = document.getElementById("planTipoDescuento").value;
    const valorDesc   = parseFloat(document.getElementById("planValorDescuento").value) || 0;
    const idPromo     = document.getElementById("selectPromo").value;

    if(!fechaInicio){ alert("Selecciona una fecha de inicio"); return; }
    if(cantCuotas < 1){ alert("La cantidad de cuotas debe ser al menos 1"); return; }

    let cuotasData = [];
    for(let i = 1; i <= cantCuotas; i++){
        const monto = parseFloat(document.getElementById("cuota_monto_" + i).value);
        const fecha = document.getElementById("cuota_fecha_" + i).value;
        if(!monto || monto <= 0){ alert("El monto de la cuota " + i + " es inválido"); return; }
        if(!fecha){ alert("Falta la fecha de vencimiento de la cuota " + i); return; }
        cuotasData.push({ monto_cuota: monto, fecha_vencimiento: fecha });
    }

    let montoCuota = montoBase;
    if(tipoDesc === "Porcentaje")  montoCuota = montoBase - (montoBase * valorDesc / 100);
    if(tipoDesc === "Monto fijo")  montoCuota = montoBase - valorDesc;
    if(tipoDesc === "Beca")        montoCuota = 0;

    let porcDesc = 0;
    if(tipoDesc === "Porcentaje") porcDesc = valorDesc;
    if(tipoDesc === "Beca")       porcDesc = 100;

    const body =
        "id_matricula="          + planMatriculaActual.id_matricula +
        "&monto_base="           + montoBase +
        "&tipo_descuento="       + encodeURIComponent(tipoDesc) +
        "&porcentaje_descuento=" + porcDesc +
        "&monto_final="          + (montoCuota * cantCuotas) +
        "&cantidad_cuotas="      + cantCuotas +
        "&es_becado="            + (tipoDesc === "Beca" ? 1 : 0) +
        "&fecha_inicio="         + fechaInicio +
        "&id_promocion="         + idPromo +
        "&cuotas="               + encodeURIComponent(JSON.stringify(cuotasData));

    fetch("../process/crear_plan_pago.php", {
        method:  "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:    body
    })
    .then(res => res.json())
    .then(res => {
        if(res.status === "success"){
            cerrarModalPlan();
            fetchCuotas();
            alert("Plan de pago creado correctamente.");
            // Abrir PDF del plan
            window.open("../process/plan_pago_pdf.php?id_plan=" + res.id_plan, "_blank");
        } else {
            alert("Error: " + res.message);
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    fetchMatriculas();
    fetchCuotas();
});

</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>

