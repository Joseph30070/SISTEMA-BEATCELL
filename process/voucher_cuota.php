<?php

require_once "../config/db.php";

$id_cuota = $_GET['id'] ?? 0;

if (!$id_cuota) {
    echo "ID de cuota requerido";
    exit;
}

// Obtener datos de la cuota
$sql = "
SELECT
    c.id_cuota,
    c.numero_cuota,
    c.monto_cuota,
    c.monto_pagado,
    c.fecha_pago,
    c.metodo_pago,
    c.estado,
    p.id_plan,
    m.id_matricula,
    a.nombre AS alumno,
    a.dni,
    a.telefono
FROM cuotas c
INNER JOIN planes_pago p ON c.id_plan = p.id_plan
INNER JOIN matriculas m ON p.id_matricula = m.id_matricula
INNER JOIN alumnos a ON m.id_alumno = a.id_alumno
WHERE c.id_cuota = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_cuota]);
$cuota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cuota) {
    echo "Cuota no encontrada";
    exit;
}

$codigo = "VCH-CTA-" . str_pad($cuota['id_cuota'], 6, "0", STR_PAD_LEFT);
$saldo_pendiente = $cuota['monto_cuota'] - $cuota['monto_pagado'];
$es_pago_completo = ($cuota['monto_pagado'] >= $cuota['monto_cuota']);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Voucher de Pago - Cuota <?php echo $cuota['numero_cuota']; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .voucher {
            background: white;
            border-radius: 12px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #009688;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 28px;
            color: #009688;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 13px;
            color: #999;
        }

        .codigo {
            text-align: center;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #333;
        }

        .seccion {
            margin-bottom: 25px;
        }

        .seccion-titulo {
            font-size: 12px;
            font-weight: bold;
            color: #009688;
            text-transform: uppercase;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }

        .dato-fila {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .dato-label {
            color: #666;
            font-weight: 500;
        }

        .dato-valor {
            color: #333;
            font-weight: bold;
        }

        .estado-completo {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }

        .estado-parcial {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .estado-titulo {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .monto-total {
            background: #009688;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }

        .monto-total-label {
            font-size: 13px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .monto-total-valor {
            font-size: 32px;
            font-weight: bold;
        }

        .monto-sub {
            font-size: 12px;
            margin-top: 10px;
            opacity: 0.85;
        }

        .pie {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #999;
            font-size: 12px;
        }

        .botones {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            justify-content: center;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-print {
            background: #009688;
            color: white;
        }

        .btn-print:hover {
            background: #00796b;
        }

        .btn-close {
            background: #e0e0e0;
            color: #333;
        }

        .btn-close:hover {
            background: #d0d0d0;
        }

        @media print {
            body {
                background: white;
            }
            .botones {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="voucher">
        <div class="header">
            <h1>✓ COMPROBANTE DE PAGO</h1>
            <p>Cuota <?php echo $cuota['numero_cuota']; ?></p>
        </div>

        <div class="codigo">
            Código: <?php echo $codigo; ?>
        </div>

        <!-- ESTADO DEL PAGO -->
        <?php if ($es_pago_completo): ?>
            <div class="estado-completo">
                <div class="estado-titulo">✓ PAGO COMPLETADO</div>
                <div>Cuota pagada en su totalidad</div>
            </div>
        <?php else: ?>
            <div class="estado-parcial">
                <div class="estado-titulo">⚠ PAGO PARCIAL</div>
                <div>Aún hay saldo pendiente de pago</div>
            </div>
        <?php endif; ?>

        <!-- DATOS DEL ALUMNO -->
        <div class="seccion">
            <div class="seccion-titulo">Datos del Alumno</div>
            <div class="dato-fila">
                <span class="dato-label">Nombre</span>
                <span class="dato-valor"><?php echo htmlspecialchars($cuota['alumno']); ?></span>
            </div>
            <div class="dato-fila">
                <span class="dato-label">DNI</span>
                <span class="dato-valor"><?php echo htmlspecialchars($cuota['dni']); ?></span>
            </div>
            <div class="dato-fila">
                <span class="dato-label">Teléfono</span>
                <span class="dato-valor"><?php echo htmlspecialchars($cuota['telefono']); ?></span>
            </div>
        </div>

        <!-- DATOS DE LA CUOTA -->
        <div class="seccion">
            <div class="seccion-titulo">Detalle de la Cuota</div>
            <div class="dato-fila">
                <span class="dato-label">Cuota N°</span>
                <span class="dato-valor"><?php echo $cuota['numero_cuota']; ?></span>
            </div>
            <div class="dato-fila">
                <span class="dato-label">Monto Total de Cuota</span>
                <span class="dato-valor">S/ <?php echo number_format($cuota['monto_cuota'], 2); ?></span>
            </div>
        </div>

        <!-- DETALLE DE PAGOS -->
        <div class="seccion">
            <div class="seccion-titulo">Detalle de Pago</div>
            <div class="dato-fila">
                <span class="dato-label">Monto Pagado</span>
                <span class="dato-valor" style="color: #28a745;">S/ <?php echo number_format($cuota['monto_pagado'], 2); ?></span>
            </div>
            <?php if (!$es_pago_completo): ?>
                <div class="dato-fila">
                    <span class="dato-label">Saldo Pendiente</span>
                    <span class="dato-valor" style="color: #ffc107;">S/ <?php echo number_format($saldo_pendiente, 2); ?></span>
                </div>
            <?php endif; ?>
            <div class="dato-fila">
                <span class="dato-label">Método de Pago</span>
                <span class="dato-valor"><?php echo htmlspecialchars($cuota['metodo_pago'] ?? 'N/A'); ?></span>
            </div>
            <div class="dato-fila">
                <span class="dato-label">Fecha de Pago</span>
                <span class="dato-valor"><?php echo date('d/m/Y', strtotime($cuota['fecha_pago'] ?? date('Y-m-d'))); ?></span>
            </div>
        </div>

        <!-- MONTO PRINCIPAL -->
        <div class="monto-total">
            <div class="monto-total-label">MONTO PAGADO EN ESTA TRANSACCIÓN</div>
            <div class="monto-total-valor">S/ <?php echo number_format($cuota['monto_pagado'], 2); ?></div>
            <?php if (!$es_pago_completo): ?>
                <div class="monto-sub">
                    Saldo pendiente: S/ <?php echo number_format($saldo_pendiente, 2); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="pie">
            <p>Comprobante generado el <?php echo date('d/m/Y H:i:s'); ?></p>
            <p style="margin-top: 10px;">Sistema Beatcell - Gestión de Pagos</p>
        </div>

        <div class="botones">
            <button class="btn btn-print" onclick="window.print()">🖨 Imprimir</button>
            <button class="btn btn-close" onclick="window.close()">✕ Cerrar</button>
        </div>
    </div>
</body>
</html>
