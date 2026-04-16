<?php

require_once "../config/db.php";

header("Content-Type: application/json");

try {

$pdo->beginTransaction();

$id_cuota = $_POST['id_cuota'];
$numero_cuota = $_POST['numero_cuota'];

$metodo_pago = $_POST['metodo_pago'];

$fecha_pago =
$_POST['fecha_pago'] ?? date("Y-m-d");

/* =========================
   PAGO DE MATRÍCULA
   ========================= */

if ($numero_cuota == 0) {

$sql = "

SELECT

m.monto_matricula,
m.monto_pagado,
m.id_matricula,
a.id_alumno

FROM matriculas m

INNER JOIN alumnos a
ON m.id_alumno = a.id_alumno

WHERE m.id_matricula = ?

";

$stmt = $pdo->prepare($sql);

$stmt->execute([$id_cuota]);

$matricula =
$stmt->fetch(PDO::FETCH_ASSOC);

if (!$matricula) {

throw new Exception(
"Matrícula no encontrada"
);

}

$monto_restante =

$matricula['monto_matricula']
-
$matricula['monto_pagado'];

if ($monto_restante <= 0) {

throw new Exception(
"La matrícula ya está pagada"
);

}

$nuevo_pagado =

$matricula['monto_pagado']
+
$monto_restante;

$estado =

($nuevo_pagado
>=
$matricula['monto_matricula'])

? "Pagada"
: "Pendiente";

/* Actualizar matrícula */

$sql = "

UPDATE matriculas

SET

monto_pagado = ?,
estado = ?

WHERE id_matricula = ?

";

$stmt = $pdo->prepare($sql);

$stmt->execute([

$nuevo_pagado,
$estado,
$id_cuota

]);

/* Movimiento financiero */

$sql = "

INSERT INTO movimientos_financieros (

id_alumno,
id_matricula,
id_cuota,
tipo_movimiento,
monto,
observacion

)

VALUES (

?, ?, NULL,
'Pago de matrícula',
?,
'Pago registrado'

)

";

$stmt = $pdo->prepare($sql);

$stmt->execute([

$matricula['id_alumno'],
$id_cuota,
$monto_restante

]);

}

/* =========================
   PAGO DE CUOTA
   ========================= */

else {

$sql = "

SELECT

c.monto_cuota,
c.monto_pagado,

a.id_alumno,
m.id_matricula

FROM cuotas c

INNER JOIN planes_pago p
ON c.id_plan = p.id_plan

INNER JOIN matriculas m
ON p.id_matricula = m.id_matricula

INNER JOIN alumnos a
ON m.id_alumno = a.id_alumno

WHERE c.id_cuota = ?

";

$stmt = $pdo->prepare($sql);

$stmt->execute([$id_cuota]);

$cuota =
$stmt->fetch(PDO::FETCH_ASSOC);

if (!$cuota) {

throw new Exception(
"Cuota no encontrada"
);

}

$monto_restante =

$cuota['monto_cuota']
-
$cuota['monto_pagado'];

if ($monto_restante <= 0) {

throw new Exception(
"Esta cuota ya está pagada"
);

}

$nuevo_pagado =

$cuota['monto_pagado']
+
$monto_restante;

$estado =

($nuevo_pagado
>=
$cuota['monto_cuota'])

? "Pagada"
: "Pendiente";

/* Actualizar cuota */

$sql = "

UPDATE cuotas

SET

monto_pagado = ?,
fecha_pago = ?,
metodo_pago = ?,
estado = ?

WHERE id_cuota = ?

";

$stmt = $pdo->prepare($sql);

$stmt->execute([

$nuevo_pagado,
$fecha_pago,
$metodo_pago,
$estado,
$id_cuota

]);

/* Movimiento financiero */

$sql = "

INSERT INTO movimientos_financieros (

id_alumno,
id_matricula,
id_cuota,
tipo_movimiento,
monto,
observacion

)

VALUES (

?, ?, ?,
'Pago de cuota',
?,
'Pago registrado'

)

";

$stmt = $pdo->prepare($sql);

$stmt->execute([

$cuota['id_alumno'],
$cuota['id_matricula'],
$id_cuota,
$monto_restante

]);

}

$pdo->commit();

echo json_encode([

"status" => "success"

]);

} catch (Exception $e) {

$pdo->rollBack();

echo json_encode([

"status" => "error",
"message" => $e->getMessage()

]);

}
