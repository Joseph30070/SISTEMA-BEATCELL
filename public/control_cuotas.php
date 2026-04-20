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
    class="w-full md:w-1/3 border px-3 py-2 mb-4 rounded"
    oninput="filtrarCuotas()">

<!-- ========================= -->
<!-- TABLA CUOTAS -->
<!-- ========================= -->

<h3 class="text-xl font-bold mb-2">
    Control de Cuotas
</h3>

<div class="bg-white p-6 rounded shadow overflow-x-auto">

<table class="min-w-full">

<thead class="bg-gray-100">
<tr>
<th class="p-3">Alumno</th>
<th class="p-3">Cuota</th>
<th class="p-3">Monto</th>
<th class="p-3">Pagado</th>
<th class="p-3">Vencimiento</th>
<th class="p-3">Estado</th>
<th class="p-3">Acción</th>
</tr>
</thead>

<tbody id="tablaCuotas"></tbody>

</table>

</div>

<!-- ============================================ -->
<!-- MODAL PAGO (existente, sin cambios)          -->
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

<p class="text-sm text-gray-600 mt-2">Monto a pagar</p>
<p id="modalMonto" class="text-green-600 text-2xl font-bold"></p>

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
<!-- MODAL PLAN DE PAGO (nuevo)                  -->
<!-- ============================================ -->

<div id="modalPlan"
     class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">

<div class="bg-white rounded shadow-xl w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">

    <!-- HEADER -->
    <div class="bg-teal-700 text-white px-6 py-4 rounded-t flex justify-between items-center">
        <div>
            <h3 class="text-xl font-bold">Crear Plan de Pago</h3>
            <p id="planAlumnoNombre" class="text-teal-200 text-sm"></p>
        </div>
        <button onclick="cerrarModalPlan()" class="text-white text-2xl leading-none">&times;</button>
    </div>

    <div class="p-6">

        <!-- INFO ALUMNO -->
        <div class="bg-gray-50 border rounded p-3 mb-5 text-sm text-gray-700 grid grid-cols-2 gap-2">
            <div><span class="font-semibold">Curso:</span> <span id="planCurso"></span></div>
            <div><span class="font-semibold">Grupo:</span> <span id="planGrupo"></span></div>
            <div><span class="font-semibold">Tipo de ciclo:</span> <span id="planTipoCiclo"></span></div>
        </div>

        <!-- PROMOCIÓN -->
        <div class="mb-5">
            <label class="font-semibold block mb-1">Promoción</label>

            <!-- Badge si ya tiene promo asignada -->
            <div id="promoAsignadaBadge" class="hidden mb-2">
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                    ✓ Promoción activa: <span id="promoAsignadaNombre"></span>
                </span>
            </div>

            <!-- Selector de promo -->
            <select id="selectPromo" class="w-full border px-3 py-2 rounded" onchange="aplicarPromo()">
                <option value="">— Sin promoción —</option>
            </select>

            <p id="promoDescripcion" class="text-xs text-gray-500 mt-1 hidden"></p>
        </div>

        <!-- TIPO DE CICLO / MONTO BASE -->
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

        <!-- DESCUENTO -->
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

        <!-- RESUMEN MONTO -->
        <div class="bg-teal-50 border border-teal-200 rounded p-3 mb-5 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                Monto base: <span id="resumenBase" class="font-semibold text-gray-800">S/ 0</span>
                <span id="resumenDescuentoTexto" class="text-red-500 ml-2 hidden"></span>
            </div>
            <div class="text-xl font-bold text-teal-700">
                Total: <span id="resumenTotal">S/ 0</span>
            </div>
        </div>

        <!-- TABLA DE CUOTAS EDITABLE -->
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

        <!-- ACCIONES -->
        <div class="flex justify-end gap-3">
            <button onclick="cerrarModalPlan()"
                class="bg-gray-300 px-5 py-2 rounded hover:bg-gray-400">
                Cancelar
            </button>
            <button onclick="confirmarPlan()"
                class="bg-teal-600 text-white px-5 py-2 rounded hover:bg-teal-700 font-semibold">
                Confirmar Plan
            </button>
        </div>

    </div><!-- /p-6 -->
</div><!-- /modal inner -->
</div><!-- /modalPlan -->


<script>

let cuotas     = [];
let matriculas = [];
let seleccionado     = null;
let tipoSeleccionado = null;

// datos del plan activo en el modal
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
        tabla.innerHTML += `
        <tr class="border-t">
            <td class="p-2">${m.alumno}</td>
            <td class="p-2">S/ ${m.monto_matricula}</td>
            <td class="p-2">${m.fecha_vencimiento}</td>
            <td class="p-2">
                <span class="bg-yellow-200 px-2 py-1 rounded">${m.estado}</span>
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
// RENDER CUOTAS (AGRUPADO)
// =========================

function renderCuotas(){
    let tabla = document.getElementById("tablaCuotas");
    tabla.innerHTML = "";

    const busqueda = document.getElementById("buscar").value.toLowerCase();

    // Agrupar cuotas por alumno
    let alumnosMap = {};
    cuotas.forEach((c, idx) => {
        if(busqueda && !c.alumno.toLowerCase().includes(busqueda)) return;
        
        if(!alumnosMap[c.alumno]){
            alumnosMap[c.alumno] = {
                nombre: c.alumno,
                dni: c.dni,
                telefono: c.telefono,
                id_matricula: c.id_matricula,
                numero_cuota: c.numero_cuota,
                tiene_plan: c.tiene_plan,
                cuotas: []
            };
        }
        alumnosMap[c.alumno].cuotas.push({
            ...c,
            idx: idx
        });
    });

    // Renderizar tabla agrupada
    let alumnoIndex = 0;
    Object.keys(alumnosMap).forEach(nombreAlumno => {
        let alumno = alumnosMap[nombreAlumno];
        let totalCuotas = alumno.cuotas.length;
        let cuotasPagadas = alumno.cuotas.filter(c => c.estado === "Pagada").length;
        let estadoGeneral = cuotasPagadas === totalCuotas ? "Pagado" : "Pendiente";
        let colorGeneral = estadoGeneral === "Pagado" ? "bg-green-200" : "bg-yellow-200";
        let safeId = "alumno_" + alumnoIndex;

        // Fila principal del alumno
        tabla.innerHTML += `
        <tr class="border-t bg-gray-50 cursor-pointer hover:bg-gray-100" 
            onclick="toggleExpand('${safeId}')">
            <td class="p-3 font-bold">${alumno.nombre}</td>
            <td class="p-3 text-center">
                <span class="text-xs bg-blue-100 px-2 py-1 rounded">${totalCuotas} cuota${totalCuotas !== 1 ? 's' : ''}</span>
            </td>
            <td class="p-3 text-center">
                <span class="text-xs">${cuotasPagadas}/${totalCuotas}</span>
            </td>
            <td class="p-3"></td>
            <td class="p-3"></td>
            <td class="p-3 text-center">
                <span class="px-2 py-1 rounded ${colorGeneral}">${estadoGeneral}</span>
            </td>
            <td class="p-3 text-center">
                <span id="expandIcon_${safeId}" class="text-lg">▼</span>
            </td>
        </tr>
        `;

        // Filas de cuotas (inicialmente ocultas)
        alumno.cuotas.forEach((c) => {
            let color = "bg-yellow-200";
            if(c.estado === "Pagada")   color = "bg-green-200";
            if(c.estado === "Atrasada") color = "bg-red-200";
            if(c.estado === "Parcial")  color = "bg-orange-200";

            // botón según estado
            let btnAccion = "";
            if(c.numero_cuota == 0 && c.tiene_plan == 0){
                // matrícula pagada sin plan → botón crear plan
                btnAccion = `
                    <button onclick="abrirPlanGeneral(${c.id_matricula})"
                        class="bg-teal-600 text-white px-2 py-1 rounded text-xs">
                        Crear Plan
                    </button>`;
            } else if(c.numero_cuota == 0 && c.tiene_plan == 1){
                // matrícula pagada con plan → no se puede hacer nada
                btnAccion = "-";
            } else if(c.estado === "Pagada"){
                // cuota pagada
                btnAccion = "-";
            } else {
                // pendiente o parcial → botón pagar
                btnAccion = `
                    <button onclick="abrirModalCuota(${c.idx})"
                        class="bg-blue-500 text-white px-2 py-1 rounded text-xs">
                        Pagar
                    </button>`;
            }

            tabla.innerHTML += `
            <tr class="border-t hidden cuota-row-${safeId} pl-8" style="display:none;">
                <td class="p-2 pl-8 text-gray-600">└─ ${c.numero_cuota == 0 ? 'Matrícula' : 'Cuota ' + c.numero_cuota}</td>
                <td class="p-2"></td>
                <td class="p-2 text-right">S/ ${c.monto_cuota}</td>
                <td class="p-2 text-right">S/ ${c.monto_pagado ?? '0.00'}</td>
                <td class="p-2">${c.fecha_vencimiento ?? ""}</td>
                <td class="p-2">
                    <span class="px-2 py-1 rounded text-sm ${color}">${c.estado}</span>
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
// TOGGLE EXPANDIR/CONTRAER
// =========================

function toggleExpand(safeId){
    const rows = document.querySelectorAll('.cuota-row-' + safeId);
    const icon = document.getElementById('expandIcon_' + safeId);
    
    if(!icon){
        console.warn('Icon not found for: ' + safeId);
        return;
    }

    rows.forEach(row => {
        if(row.style.display === "none"){
            row.style.display = "table-row";
        } else {
            row.style.display = "none";
        }
    });

    // Cambiar ícono
    icon.innerText = icon.innerText === "▼" ? "▶" : "▼";
}

// =========================
// BUSCADOR
// =========================

function filtrarCuotas(){
    renderCuotas();
}

// =========================
// ABRIR MODAL MATRÍCULA
// =========================

function abrirModalMatricula(i){
    tipoSeleccionado = "matricula";
    seleccionado = i;

    document.getElementById("modalAlumno").innerText = matriculas[i].alumno;
    document.getElementById("modalCuota").innerText  = "Matrícula";
    document.getElementById("modalMonto").innerText  = "S/ " + matriculas[i].monto_matricula;

    document.getElementById("modal").classList.remove("hidden");
    document.getElementById("modal").classList.add("flex");
    document.getElementById("fechaPago").value = new Date().toISOString().split("T")[0];
}

// =========================
// ABRIR MODAL CUOTA
// =========================

function abrirModalCuota(i){
    tipoSeleccionado = "cuota";
    seleccionado = i;

    document.getElementById("modalAlumno").innerText = cuotas[i].alumno;
    document.getElementById("modalCuota").innerText  = "Cuota " + cuotas[i].numero_cuota;
    document.getElementById("modalMonto").innerText  = "S/ " + cuotas[i].monto_cuota;

    document.getElementById("modal").classList.remove("hidden");
    document.getElementById("modal").classList.add("flex");
    document.getElementById("fechaPago").value = new Date().toISOString().split("T")[0];
}

// =========================
// CONFIRMAR PAGO (sin cambios)
// =========================

function confirmarPago(){
    let fecha  = document.getElementById("fechaPago").value;
    let metodo = document.getElementById("metodoPago").value;

    if(!fecha){ alert("Seleccione una fecha"); return; }

    let id;
    if(tipoSeleccionado === "matricula"){
        id = matriculas[seleccionado].id_matricula;
    } else {
        id = cuotas[seleccionado].id_cuota;
    }

    fetch("../process/registrar_pago.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:
            "id_cuota="     + id +
            "&numero_cuota=" + (tipoSeleccionado === "matricula" ? 0 : cuotas[seleccionado].numero_cuota) +
            "&fecha_pago="   + fecha +
            "&metodo_pago="  + metodo
    })
    .then(res => res.json())
    .then(res => {
        if(res.status === "success"){
            cerrarModal();
            fetchMatriculas();
            fetchCuotas();

            if(tipoSeleccionado === "matricula"){
                if(confirm("Alumno matriculado correctamente.\n\n¿Deseas generar el voucher de pago?")){
                    generarVoucher(id);
                }
            } else {
                alert("Pago registrado correctamente");
            }
        } else {
            alert(res.message);
        }
    });
}

function generarVoucher(id){
    window.open("../process/voucher_pago.php?id=" + id, "_blank");
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
        if(c.estado === "Pagada")   pagado    += monto;
        if(c.estado === "Pendiente" || c.estado === "Parcial") pendiente += monto;
        if(c.estado === "Atrasada") atrasado  += monto;
    });

    document.getElementById("totalPagadas").innerText   = "S/ " + pagado;
    document.getElementById("totalPendiente").innerText = "S/ " + pendiente;
    document.getElementById("totalAtrasadas").innerText = "S/ " + atrasado;
}

// ===================================
// ABRIR MODAL PLAN (nuevo)
// ===================================

function abrirPlanGeneral(id_matricula){

    fetch("../process/get_plan_pago.php?id_matricula=" + id_matricula)
    .then(res => res.json())
    .then(res => {

        if(res.status !== "success"){
            alert(res.message);
            return;
        }

        if(res.tiene_plan){
            alert("Este alumno ya tiene un plan de pago creado.");
            return;
        }

        planMatriculaActual = res.matricula;
        promosDisponibles   = res.promos_disponibles || [];

        // Llenar info alumno
        document.getElementById("planAlumnoNombre").innerText =
            res.matricula.alumno;
        document.getElementById("planCurso").innerText =
            res.matricula.nombre_curso;
        document.getElementById("planGrupo").innerText =
            res.matricula.nombre_grupo;
        document.getElementById("planTipoCiclo").innerText =
            res.matricula.tipo_ciclo || "No definido";

        // Promo ya asignada
        const badge = document.getElementById("promoAsignadaBadge");
        if(res.promo_asignada){
            document.getElementById("promoAsignadaNombre").innerText =
                res.promo_asignada.nombre_promocion;
            badge.classList.remove("hidden");
        } else {
            badge.classList.add("hidden");
        }

        // Llenar selector de promos
        const selectPromo = document.getElementById("selectPromo");
        selectPromo.innerHTML = '<option value="">— Sin promoción —</option>';
        promosDisponibles.forEach(p => {
            const opt = document.createElement("option");
            opt.value       = p.id_promocion;
            opt.textContent = p.nombre_promocion;
            opt.dataset.desc = p.descripcion || "";
            if(res.promo_asignada && res.promo_asignada.id_promocion == p.id_promocion){
                opt.selected = true;
            }
            selectPromo.appendChild(opt);
        });

        // Precargar tipo ciclo según alumno
        const cicloMap = {
            "Normal":        "Normal",
            "Acelerado":     "Acelerado",
            "Especializacion":"Especializacion"
        };
        const cicloAlumno = res.matricula.tipo_ciclo || "Normal";
        const selectCiclo = document.getElementById("planCicloSelect");
        for(let opt of selectCiclo.options){
            if(opt.value === cicloMap[cicloAlumno]){
                opt.selected = true;
                break;
            }
        }

        // Fecha de inicio = hoy
        document.getElementById("planFechaInicio").value =
            new Date().toISOString().split("T")[0];

        // Limpiar descuento
        document.getElementById("planTipoDescuento").value = "Ninguno";
        document.getElementById("planValorDescuento").value = "";

        // Recalcular plan con los valores precargados
        recalcularPlan();

        // Mostrar modal
        document.getElementById("modalPlan").classList.remove("hidden");
        document.getElementById("modalPlan").classList.add("flex");
    });
}

function cerrarModalPlan(){
    document.getElementById("modalPlan").classList.add("hidden");
    document.getElementById("modalPlan").classList.remove("flex");
    planMatriculaActual = null;
}

// ===================================
// TARIFARIO BASE
// ===================================

const TARIFARIO = {
    Normal:          { monto: 150, cuotas: 4 },
    Acelerado:       { monto: 250, cuotas: 2 },
    Especializacion: { monto: 300, cuotas: 1 }
};

const TARIFARIO_PROMO = {
    Normal:    100,
    Acelerado: 200
};

// ===================================
// RECALCULAR PLAN (ciclo + promo)
// ===================================

function recalcularPlan(){

    const ciclo    = document.getElementById("planCicloSelect").value;
    const tarifario = TARIFARIO[ciclo] || TARIFARIO["Normal"];

    // ¿Tiene promo seleccionada?
    const idPromo = document.getElementById("selectPromo").value;
    let montoBase = tarifario.monto;

    if(idPromo && TARIFARIO_PROMO[ciclo]){
        montoBase = TARIFARIO_PROMO[ciclo];
    }

    document.getElementById("planMontoBase").value   = montoBase;
    document.getElementById("planCantCuotas").value  = tarifario.cuotas;

    recalcularDescuento();
}

// ===================================
// APLICAR PROMO (al cambiar selector)
// ===================================

function aplicarPromo(){
    const select = document.getElementById("selectPromo");
    const opt    = select.options[select.selectedIndex];
    const desc   = opt ? opt.dataset.desc : "";

    const pDesc = document.getElementById("promoDescripcion");
    if(desc){
        pDesc.innerText = desc;
        pDesc.classList.remove("hidden");
    } else {
        pDesc.classList.add("hidden");
    }

    recalcularPlan();
}

// ===================================
// RECALCULAR DESCUENTO ADICIONAL
// ===================================

function recalcularDescuento(){

    const montoBase    = parseFloat(document.getElementById("planMontoBase").value) || 0;
    const cantCuotas   = parseInt(document.getElementById("planCantCuotas").value)  || 1;
    const tipoDesc     = document.getElementById("planTipoDescuento").value;
    const valorDesc    = parseFloat(document.getElementById("planValorDescuento").value) || 0;

    let montoFinal = montoBase;
    let textoDesc  = "";

    if(tipoDesc === "Porcentaje"){
        const descuento = montoBase * (valorDesc / 100);
        montoFinal = montoBase - descuento;
        textoDesc  = `- ${valorDesc}%`;
    } else if(tipoDesc === "Monto fijo"){
        montoFinal = montoBase - valorDesc;
        textoDesc  = `- S/ ${valorDesc}`;
    } else if(tipoDesc === "Beca"){
        montoFinal = 0;
        textoDesc  = "Beca 100%";
    }

    if(montoFinal < 0) montoFinal = 0;

    const totalPlan = montoFinal * cantCuotas;

    document.getElementById("resumenBase").innerText = "S/ " + montoBase.toFixed(2);
    document.getElementById("resumenTotal").innerText = "S/ " + totalPlan.toFixed(2);

    const spanDesc = document.getElementById("resumenDescuentoTexto");
    if(textoDesc){
        spanDesc.innerText = textoDesc;
        spanDesc.classList.remove("hidden");
    } else {
        spanDesc.classList.add("hidden");
    }

    generarFilasCuotas();
}

// ===================================
// GENERAR FILAS DE CUOTAS EDITABLES
// ===================================

function generarFilasCuotas(){

    const montoBase   = parseFloat(document.getElementById("planMontoBase").value) || 0;
    const cantCuotas  = parseInt(document.getElementById("planCantCuotas").value)  || 1;
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

        // Calcular fecha de vencimiento (+i meses desde fecha inicio)
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
                    id="cuota_monto_${i}"
                    value="${montoCuota.toFixed(2)}"
                    class="w-full border px-2 py-1 rounded text-sm">
            </td>
            <td class="p-2 border">
                <input type="date"
                    id="cuota_fecha_${i}"
                    value="${fechaVenc}"
                    class="w-full border px-2 py-1 rounded text-sm">
            </td>
        </tr>
        `;
    }
}

// ===================================
// CONFIRMAR PLAN
// ===================================

function confirmarPlan(){

    if(!planMatriculaActual){
        alert("Error: no hay matrícula seleccionada");
        return;
    }

    const cantCuotas  = parseInt(document.getElementById("planCantCuotas").value)  || 0;
    const montoBase   = parseFloat(document.getElementById("planMontoBase").value)  || 0;
    const fechaInicio = document.getElementById("planFechaInicio").value;
    const tipoDesc    = document.getElementById("planTipoDescuento").value;
    const valorDesc   = parseFloat(document.getElementById("planValorDescuento").value) || 0;
    const idPromo     = document.getElementById("selectPromo").value;

    if(!fechaInicio){
        alert("Selecciona una fecha de inicio");
        return;
    }

    if(cantCuotas < 1){
        alert("La cantidad de cuotas debe ser al menos 1");
        return;
    }

    // Recoger cuotas editadas
    let cuotasData = [];
    for(let i = 1; i <= cantCuotas; i++){
        const monto = parseFloat(document.getElementById("cuota_monto_" + i).value);
        const fecha = document.getElementById("cuota_fecha_" + i).value;

        if(!monto || monto <= 0){
            alert("El monto de la cuota " + i + " es inválido");
            return;
        }
        if(!fecha){
            alert("Falta la fecha de vencimiento de la cuota " + i);
            return;
        }
        cuotasData.push({ monto_cuota: monto, fecha_vencimiento: fecha });
    }

    // Calcular monto final por cuota
    let montoCuota = montoBase;
    if(tipoDesc === "Porcentaje")  montoCuota = montoBase - (montoBase * valorDesc / 100);
    if(tipoDesc === "Monto fijo")  montoCuota = montoBase - valorDesc;
    if(tipoDesc === "Beca")        montoCuota = 0;

    const montoFinal = montoCuota * cantCuotas;

    // Porcentaje para el backend
    let porcDesc = 0;
    if(tipoDesc === "Porcentaje") porcDesc = valorDesc;
    if(tipoDesc === "Beca")       porcDesc = 100;

    const body =
        "id_matricula="          + planMatriculaActual.id_matricula +
        "&monto_base="           + montoBase +
        "&tipo_descuento="       + encodeURIComponent(tipoDesc) +
        "&porcentaje_descuento=" + porcDesc +
        "&monto_final="          + montoFinal +
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
        } else {
            alert("Error: " + res.message);
        }
    });
}

// =========================
// INIT
// =========================

document.addEventListener("DOMContentLoaded", () => {
    fetchMatriculas();
    fetchCuotas();
});

</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>

