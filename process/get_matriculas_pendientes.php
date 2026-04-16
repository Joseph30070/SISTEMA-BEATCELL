<?php

require_once "../config/db.php";

header("Content-Type: application/json");

try {

$sql = "

SELECT

m.id_matricula,
a.nombre AS alumno,

m.monto_matricula,
m.monto_pagado,

DATE_ADD(
m.fecha_matricula,
INTERVAL 7 DAY
) AS fecha_vencimiento,

CASE

WHEN m.monto_pagado >= m.monto_matricula
THEN 'Pagada'

ELSE 'Pendiente'

END AS estado

FROM matriculas m

INNER JOIN alumnos a
ON m.id_alumno = a.id_alumno

WHERE

m.estado = 'Activo'
AND
m.monto_pagado < m.monto_matricula

ORDER BY fecha_vencimiento ASC

";

$stmt = $pdo->prepare($sql);

$stmt->execute();

$data =
$stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([

"status" => "success",
"data" => $data

]);

} catch (Exception $e) {

echo json_encode([

"status" => "error",
"message" => $e->getMessage()

]);

}
