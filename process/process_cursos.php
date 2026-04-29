<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'SECRETARIO']); //  ajustado según tu sistema

$pdo = require __DIR__ . '/../config/db.php';

$nombre = trim($_POST['nombre'] ?? '');

if ($nombre === '') {
    header("Location: ../public/asignar_cursos.php?error=El nombre del curso es obligatorio");
    exit;
}

try {

    // =========================
    // VERIFICAR DUPLICADO
    // =========================
    $stmt = $pdo->prepare("SELECT id_curso FROM cursos WHERE nombre_curso = ?");
    $stmt->execute([$nombre]);

    if ($stmt->fetch()) {
        header("Location: ../public/asignar_cursos.php?error=El curso ya existe");
        exit;
    }

    // =========================
    // INSERTAR CURSO
    // =========================
    $stmt = $pdo->prepare("INSERT INTO cursos (nombre_curso) VALUES (?)");
    $stmt->execute([$nombre]);

    header("Location: ../public/asignar_cursos.php?success=Curso agregado correctamente");
    exit;

} catch (Exception $e) {

    header("Location: ../public/asignar_cursos.php?error=" . urlencode($e->getMessage()));
    exit;
}
