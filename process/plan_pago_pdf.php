<?php

require_once "../config/db.php";

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$id_plan = $_GET['id_plan'] ?? 0;

if (!$id_plan) {
    echo "ID de plan requerido";
    exit;
}

// Obtener datos del plan y cuotas
$sql = "
SELECT
    p.id_plan,
    p.monto_base,
    p.tipo_descuento,
    p.porcentaje_descuento,
    p.monto_final,
    p.cantidad_cuotas,
    p.fecha_inicio,
    a.nombre AS alumno,
    a.dni,
    a.telefono,
    m.id_matricula,
    c.numero_cuota,
    c.monto_cuota,
    c.fecha_vencimiento,
    c.estado
FROM planes_pago p
INNER JOIN matriculas m ON p.id_matricula = m.id_matricula
INNER JOIN alumnos a ON m.id_alumno = a.id_alumno
INNER JOIN cuotas c ON p.id_plan = c.id_plan
WHERE p.id_plan = ?
ORDER BY c.numero_cuota
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_plan]);
$cuotas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cuotas)) {
    echo "Plan de pago no encontrado";
    exit;
}

$plan = $cuotas[0]; // Los primeros datos son del plan

// Generar HTML para el PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Plan de Pago - ' . $plan['alumno'] . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 15px; background-color: #f9f9f9; }
        .header { text-align: center; background-color: #009688; color: white; padding: 15px; border-radius: 10px; margin-bottom: 15px; }
        .info { background-color: white; padding: 15px; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .info table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .info td { padding: 8px; border-bottom: 1px solid #eee; }
        .info td:first-child { font-weight: bold; background-color: #f0f0f0; width: 40%; }
        .cuotas { background-color: white; padding: 15px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .cuotas h3 { color: #009688; margin-bottom: 12px; font-size: 16px; }
        .cuotas table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .cuotas th, .cuotas td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        .cuotas th { background-color: #009688; color: white; }
        .cuotas tr:nth-child(even) { background-color: #f9f9f9; }
        .total { background-color: #009688; color: white; padding: 12px; text-align: center; font-size: 16px; font-weight: bold; border-radius: 10px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Plan de Pago</h1>
        <h3>Sistema Beatcell</h3>
        <p style="font-size: 12px; margin-top: 8px;">Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>
    </div>

    <div class="info">
        <table>
            <tr><td>Alumno</td><td>' . $plan['alumno'] . '</td></tr>
            <tr><td>DNI</td><td>' . $plan['dni'] . '</td></tr>
            <tr><td>Teléfono</td><td>' . $plan['telefono'] . '</td></tr>
            <tr><td>ID Matrícula</td><td>' . $plan['id_matricula'] . '</td></tr>
            <tr><td>ID Plan</td><td>' . $plan['id_plan'] . '</td></tr>
            <tr><td>Monto Base</td><td>S/ ' . number_format($plan['monto_base'], 2) . '</td></tr>
            <tr><td>Descuento</td><td>' . ($plan['tipo_descuento'] ?: 'Ninguno') . ' (' . $plan['porcentaje_descuento'] . '%)</td></tr>
            <tr><td>Monto Final</td><td>S/ ' . number_format($plan['monto_final'], 2) . '</td></tr>
            <tr><td>Cantidad de Cuotas</td><td>' . $plan['cantidad_cuotas'] . '</td></tr>
            <tr><td>Fecha de Inicio</td><td>' . date('d/m/Y', strtotime($plan['fecha_inicio'])) . '</td></tr>
        </table>
    </div>

    <div class="cuotas">
        <h3>Detalle de Cuotas</h3>
        <table>
            <thead>
                <tr>
                    <th>N° Cuota</th>
                    <th>Monto Total</th>
                    <th>Pagado</th>
                    <th>Pendiente</th>
                    <th>Fecha Vencimiento</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>';

foreach ($cuotas as $cuota) {
    $monto_pagado = floatval($cuota['monto_pagado'] ?? 0);
    $monto_total = floatval($cuota['monto_cuota']);
    $monto_pendiente = $monto_total - $monto_pagado;
    
    $html .= '
                <tr>
                    <td>' . $cuota['numero_cuota'] . '</td>
                    <td>S/ ' . number_format($monto_total, 2) . '</td>
                    <td>S/ ' . number_format($monto_pagado, 2) . '</td>
                    <td>S/ ' . number_format($monto_pendiente, 2) . '</td>
                    <td>' . $cuota['fecha_vencimiento'] . '</td>
                    <td>' . $cuota['estado'] . '</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>
    </div>

    <div class="total">
        Total a Pagar: S/ ' . number_format($plan['monto_final'], 2) . '
    </div>
</body>
</html>';

// Generar PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream('plan_pago_' . $plan['id_plan'] . '.pdf', array('Attachment' => false));

?>