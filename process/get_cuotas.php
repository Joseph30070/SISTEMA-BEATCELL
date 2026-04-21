<?php

require_once "../config/db.php";

header("Content-Type: application/json");

try {

$sql = "

/* =========================
   CUOTAS REALES
   ========================= */

SELECT

c.id_cuota,
c.numero_cuota,
c.monto_cuota,
c.monto_pagado,
c.fecha_vencimiento,
c.estado,

a.nombre AS alumno,
a.dni,
a.telefono,

m.id_matricula,
p.id_plan,
1 AS tiene_plan

FROM cuotas c

INNER JOIN planes_pago p
ON c.id_plan = p.id_plan

INNER JOIN matriculas m
ON p.id_matricula = m.id_matricula

INNER JOIN alumnos a
ON m.id_alumno = a.id_alumno


UNION ALL


/* =========================
   MATRÍCULAS PAGADAS
   (VISUAL / INFORMATIVO)
   ========================= */

SELECT

m.id_matricula AS id_cuota,
0 AS numero_cuota,
m.monto_matricula AS monto_cuota,
m.monto_pagado,
DATE_ADD(m.fecha_matricula, INTERVAL 7 DAY) AS fecha_vencimiento,

'Pagada' AS estado,

a.nombre AS alumno,
a.dni,
a.telefono,

m.id_matricula,
NULL AS id_plan,
CASE WHEN EXISTS (
    SELECT 1 FROM planes_pago pp
    WHERE pp.id_matricula = m.id_matricula
) THEN 1 ELSE 0 END AS tiene_plan

FROM matriculas m

INNER JOIN alumnos a
ON m.id_alumno = a.id_alumno

WHERE m.monto_pagado >= m.monto_matricula

ORDER BY fecha_vencimiento ASC

";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

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


