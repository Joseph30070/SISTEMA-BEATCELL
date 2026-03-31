<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'ASESOR']);

$pdo = require __DIR__ . '/../config/db.php';

try {

    $id = $_GET['id'] ?? null;

    if(!$id){
        throw new Exception("ID inválido");
    }

    $stmt = $pdo->prepare("
        UPDATE alumnos 
        SET fecha_baja = NULL 
        WHERE id_alumno = ?
    ");

    $stmt->execute([$id]);

    header("Location: ../public/registro_alumnos.php?success=Alumno reactivado");
    exit;

} catch(Exception $e){

    header("Location: ../public/registro_alumnos.php?error=" . urlencode($e->getMessage()));
    exit;
}
