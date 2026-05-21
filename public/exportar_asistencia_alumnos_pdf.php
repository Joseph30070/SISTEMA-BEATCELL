<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'SECRETARIO']);

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;

$pdo = require __DIR__ . '/../config/db.php';

$fecha = $_GET['fecha'] ?? '';
$id_curso = $_GET['id_curso'] ?? '';
$id_grupo = $_GET['id_grupo'] ?? '';

if ($fecha === '' || $id_curso === '' || $id_grupo === '') {
    die("Faltan parámetros para exportar.");
}

$sql = "
SELECT 
    a.nombre AS alumno,
    a.dni,
    a.telefono,
    c.nombre_curso,
    g.nombre_grupo,
    aa.fecha,
    aa.estado,
    aa.hora_entrada,
    aa.hora_salida
FROM asistencias aa
INNER JOIN alumnos a ON aa.id_alumno = a.id_alumno
INNER JOIN matriculas m ON a.id_alumno = m.id_alumno
INNER JOIN grupos g ON m.id_grupo = g.id_grupo
INNER JOIN cursos c ON g.id_curso = c.id_curso
WHERE aa.fecha = ?
AND g.id_curso = ?
AND g.id_grupo = ?
ORDER BY a.nombre ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$fecha, $id_curso, $id_grupo]);
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$curso = $datos[0]['nombre_curso'] ?? 'Curso seleccionado';
$grupo = $datos[0]['nombre_grupo'] ?? 'Grupo seleccionado';

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        color: #333;
    }

    h2 {
        text-align: center;
        color: #0f766e;
        margin-bottom: 5px;
    }

    .info {
        margin-bottom: 15px;
        font-size: 13px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #0f766e;
        color: white;
        padding: 7px;
        border: 1px solid #ccc;
    }

    td {
        padding: 6px;
        border: 1px solid #ccc;
        text-align: center;
    }

    .footer {
        margin-top: 20px;
        font-size: 11px;
        text-align: right;
        color: #666;
    }
</style>
</head>
<body>

<h2>Reporte de Asistencia de Alumnos</h2>

<div class="info">
    <strong>Fecha:</strong> ' . htmlspecialchars($fecha) . '<br>
    <strong>Curso:</strong> ' . htmlspecialchars($curso) . '<br>
    <strong>Grupo:</strong> ' . htmlspecialchars($grupo) . '
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Alumno</th>
            <th>DNI</th>
            <th>Teléfono</th>
            <th>Estado</th>
            <th>Entrada</th>
            <th>Salida</th>            
        </tr>
    </thead>
    <tbody>
';

if (count($datos) > 0) {
    $i = 1;

    foreach ($datos as $fila) {
       $html .= '
        <tr>
            <td>' . $i++ . '</td>
            <td>' . htmlspecialchars($fila['alumno'] ?? '') . '</td>
            <td>' . htmlspecialchars($fila['dni'] ?? '') . '</td>
            <td>' . htmlspecialchars($fila['telefono'] ?? '') . '</td>
            <td>' . htmlspecialchars($fila['estado'] ?? '') . '</td>
            <td>' . htmlspecialchars($fila['hora_entrada'] ?? '-') . '</td>
            <td>' . htmlspecialchars($fila['hora_salida'] ?? '-') . '</td>
        </tr>';
    }
} else {
    $html .= '
        <tr>
            <td colspan="8">No se encontraron registros de asistencia.</td>
        </tr>';
}

$html .= '
    </tbody>
</table>

<div class="footer">
    Sistema Beatcell - Reporte generado automáticamente
</div>

</body>
</html>
';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "landscape");
$dompdf->render();
$dompdf->stream("asistencia_alumnos_" . $fecha . ".pdf", ["Attachment" => false]);
exit;