<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR']);

$pdo = require __DIR__ . '/../config/db.php';

try {

    // =========================
    // DATOS PRINCIPALES
    // =========================
    $id_curso = $_POST['id_curso'] ?? null;
    $nombre_grupo = trim($_POST['nombre_grupo'] ?? '');
    $dias = $_POST['dias'] ?? [];

    $hora_inicio = $_POST['hora_inicio'] ?? [];
    $hora_fin = $_POST['hora_fin'] ?? [];

    if (!$id_curso || !$nombre_grupo || empty($dias)) {
        header("Location: ../public/asignar_cursos.php?error=Faltan datos obligatorios");
        exit;
    }

    // =========================
    // 1. VERIFICAR SI GRUPO EXISTE
    // =========================
    $sql = "SELECT id_grupo 
            FROM grupos 
            WHERE nombre_grupo = ? 
            AND id_curso = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre_grupo, $id_curso]);

    $grupo = $stmt->fetch();

    if ($grupo) {

        $id_grupo = $grupo['id_grupo'];

    } else {

        // Crear grupo nuevo
        $sql = "INSERT INTO grupos 
                (id_curso, nombre_grupo) 
                VALUES (?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_curso, $nombre_grupo]);

        $id_grupo = $pdo->lastInsertId();
    }

    // =========================
    // 2. INSERTAR HORARIOS ESPECIALES
    // =========================
    $stmt = $pdo->prepare("
        INSERT INTO horarios_especiales
        (id_grupo, dia_semana, hora_inicio, hora_fin)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($dias as $dia) {

        $inicio = $hora_inicio[$dia] ?? null;
        $fin = $hora_fin[$dia] ?? null;

        if ($inicio && $fin) {

            $stmt->execute([
                $id_grupo,
                $dia,
                $inicio,
                $fin
            ]);
        }
    }

    header("Location: ../public/asignar_cursos.php?success=Horario especial creado correctamente");
    exit;

} catch (Exception $e) {

    header("Location: ../public/asignar_cursos.php?error=" . urlencode($e->getMessage()));
    exit;
}