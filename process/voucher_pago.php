<?php

require_once "../config/db.php";

$id = $_GET['id'] ?? 0;

$sql = "

SELECT

a.nombre AS alumno,
a.dni,
m.id_matricula,
m.monto_pagado,
m.fecha_matricula

FROM matriculas m

INNER JOIN alumnos a
ON m.id_alumno = a.id_alumno

WHERE m.id_matricula = ?

";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$data){
    echo "Registro no encontrado";
    exit;
}

$codigo = "VCH-" . str_pad($id, 6, "0", STR_PAD_LEFT);

?>

<!DOCTYPE html>
<html>
<head>

<title>Voucher de Pago</title>

<style>

body{
    font-family: Arial, sans-serif;
    background-color: #f3f4f6;
    padding: 30px;
}

.voucher{
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    max-width: 460px;
    margin: auto;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
}

.logo{
    text-align: center;
    margin-bottom: 18px;
}

.logo img{
    max-width: 140px;
}

.titulo{
    text-align: center;
    font-weight: bold;
    font-size: 20px;
    margin-bottom: 18px;
}

.dato{
    margin-bottom: 12px;
}

.label{
    font-size: 13px;
    color: #6b7280;
}

.value{
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.total{
    margin-top: 18px;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
    font-size: 20px;
    font-weight: bold;
    color: #0f766e;
}

.footer{
    text-align: center;
    font-size: 13px;
    margin-top: 20px;
    color: #6b7280;
}

.btn{
    margin-top: 20px;
    text-align: center;
}

button{
    background: #0f766e;
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    cursor: pointer;
}

button:hover{
    background: #115e59;
}

@media print{
    button{ display:none; }
    body{ background:white; }
    .voucher{ box-shadow:none; }
}

</style>

</head>

<body>

<div class="voucher">

<div class="logo">
    <img src="../img/logo-beatcell.png">
</div>

<div class="titulo">
    COMPROBANTE DE PAGO
</div>

<div class="dato">
    <div class="label">Código</div>
    <div class="value"><?php echo $codigo; ?></div>
</div>

<div class="dato">
    <div class="label">Alumno</div>
    <div class="value"><?php echo $data['alumno']; ?></div>
</div>

<div class="dato">
    <div class="label">DNI</div>
    <div class="value"><?php echo $data['dni']; ?></div>
</div>

<div class="dato">
    <div class="label">ID Matrícula</div>
    <div class="value"><?php echo $data['id_matricula']; ?></div>
</div>

<div class="dato">
    <div class="label">Concepto</div>
    <div class="value">Pago de Matrícula</div>
</div>

<div class="dato">
    <div class="label">Fecha de registro</div>
    <div class="value"><?php echo $data['fecha_matricula']; ?></div>
</div>

<div class="total">
    Total pagado: S/ <?php echo number_format($data['monto_pagado'], 2); ?>
</div>

<div class="footer">
    Gracias por su pago. Este comprobante es válido como constancia.
</div>

<div class="btn">
    <button onclick="window.print()">Imprimir / Guardar PDF</button>
</div>

</div>

</body>
</html>

