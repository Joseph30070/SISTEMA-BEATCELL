<?php
require_once __DIR__ . '/../config/auth.php';
$pdo = require __DIR__ . '/../config/db.php';

$action = $_POST['action'] ?? 'create';

try {

    // =========================
    // CREAR GRUPO
    // =========================
    if ($action === 'create') {

        checkRole(['ADMINISTRADOR', 'SECRETARIO']); // 🔥 agregado

        $id_curso = $_POST['id_curso'] ?? null;
        $nombre_grupo = trim($_POST['nombre_grupo'] ?? '');
        $hora_inicio = $_POST['hora_inicio'] ?? null;
        $hora_fin = $_POST['hora_fin'] ?? null;
        $dias = $_POST['dias'] ?? [];

        $dias_string = !empty($dias) ? implode(", ", $dias) : null;

        if (!$id_curso || !$nombre_grupo || !$hora_inicio || !$hora_fin || empty($dias)) {
            header("Location: ../public/asignar_cursos.php?error=Todos los campos son obligatorios");
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO grupos (id_curso, nombre_grupo, dias, hora_inicio, hora_fin)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $id_curso,
            $nombre_grupo,
            $dias_string,
            $hora_inicio,
            $hora_fin
        ]);

        header("Location: ../public/asignar_cursos.php?success=Grupo creado correctamente");
        exit;
    }

    // =========================
    // ELIMINAR GRUPO
    // =========================
    if ($action === 'delete') {

        checkRole(['ADMINISTRADOR']); // 🔥 solo admin

        $id_grupo = $_POST['id_grupo'] ?? null;

        if (!$id_grupo) {
            header("Location: ../public/asignar_cursos.php?error=ID inválido");
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM grupos WHERE id_grupo = ?");
        $stmt->execute([$id_grupo]);

        header("Location: ../public/asignar_cursos.php?success=Grupo eliminado correctamente");
        exit;
    }

    // =========================
    // EDITAR GRUPO
    // =========================
    if ($action === 'edit') {

        checkRole(['ADMINISTRADOR']); // 🔥 solo admin

        $id_grupo = $_POST['id_grupo'] ?? null;
        $id_curso = $_POST['id_curso'] ?? null;
        $nombre_grupo = trim($_POST['nombre_grupo'] ?? '');
        $hora_inicio = $_POST['hora_inicio'] ?? null;
        $hora_fin = $_POST['hora_fin'] ?? null;
        $dias = $_POST['dias'] ?? [];

        $dias_string = !empty($dias) ? implode(", ", $dias) : null;

        if (!$id_grupo || !$id_curso || !$nombre_grupo || !$hora_inicio || !$hora_fin || empty($dias)) {
            header("Location: ../public/asignar_cursos.php?error=Datos incompletos para editar");
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE grupos 
            SET id_curso = ?, nombre_grupo = ?, dias = ?, hora_inicio = ?, hora_fin = ?
            WHERE id_grupo = ?
        ");

        $stmt->execute([
            $id_curso,
            $nombre_grupo,
            $dias_string,
            $hora_inicio,
            $hora_fin,
            $id_grupo
        ]);

        header("Location: ../public/asignar_cursos.php?success=Grupo actualizado correctamente");
        exit;
    }

} catch (Exception $e) {

    header("Location: ../public/asignar_cursos.php?error=" . urlencode($e->getMessage()));
    exit;
}
