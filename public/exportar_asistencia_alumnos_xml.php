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

$nombreArchivo = "asistencia_alumnos_" . $fecha . ".xml";

header("Content-Type: application/xml; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$nombreArchivo\"");

$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><reporte_asistencia_alumnos></reporte_asistencia_alumnos>');

$xml->addChild('fecha', htmlspecialchars($fecha));
$xml->addChild('curso', htmlspecialchars($datos[0]['nombre_curso'] ?? 'Curso seleccionado'));
$xml->addChild('grupo', htmlspecialchars($datos[0]['nombre_grupo'] ?? 'Grupo seleccionado'));

$alumnos = $xml->addChild('alumnos');

foreach ($datos as $fila) {
    $alumno = $alumnos->addChild('alumno');
    $alumno->addChild('nombre', htmlspecialchars($fila['alumno'] ?? ''));
    $alumno->addChild('dni', htmlspecialchars($fila['dni'] ?? ''));
    $alumno->addChild('telefono', htmlspecialchars($fila['telefono'] ?? ''));
    $alumno->addChild('estado', htmlspecialchars($fila['estado'] ?? ''));
    $alumno->addChild('hora_entrada', htmlspecialchars($fila['hora_entrada'] ?? ''));
    $alumno->addChild('hora_salida', htmlspecialchars($fila['hora_salida'] ?? ''));
}

echo $xml->asXML();
exit;