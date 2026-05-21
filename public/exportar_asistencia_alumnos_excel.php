<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'SECRETARIO']);

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

$nombreArchivo = "asistencia_alumnos_" . $fecha . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$nombreArchivo\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF";

$curso = $datos[0]['nombre_curso'] ?? 'Curso seleccionado';
$grupo = $datos[0]['nombre_grupo'] ?? 'Grupo seleccionado';

echo "<table border='1'>";
echo "<tr><th colspan='7'>REPORTE DE ASISTENCIA DE ALUMNOS</th></tr>";
echo "<tr><td colspan='7'><strong>Fecha:</strong> " . htmlspecialchars($fecha) . "</td></tr>";
echo "<tr><td colspan='7'><strong>Curso:</strong> " . htmlspecialchars($curso) . "</td></tr>";
echo "<tr><td colspan='7'><strong>Grupo:</strong> " . htmlspecialchars($grupo) . "</td></tr>";
echo "<tr></tr>";

echo "
<tr>
    <th>#</th>
    <th>Alumno</th>
    <th>DNI</th>
    <th>Teléfono</th>
    <th>Estado</th>
    <th>Entrada</th>
    <th>Salida</th>
</tr>
";

if (count($datos) > 0) {
    $i = 1;

    foreach ($datos as $fila) {
        echo "<tr>";
        echo "<td>" . $i++ . "</td>";
        echo "<td>" . htmlspecialchars($fila['alumno'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($fila['dni'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($fila['telefono'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($fila['estado'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($fila['hora_entrada'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($fila['hora_salida'] ?? '-') . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7'>No se encontraron registros de asistencia.</td></tr>";
}

echo "</table>";
exit;