<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR']);

$pdo = require __DIR__ . '/../config/db.php';

try {

    // =========================
    // 1. OBTENER DATOS
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
    // 2. VERIFICAR O CREAR GRUPO
    // =========================
    $stmt = $pdo->prepare("
        SELECT id_grupo 
        FROM grupos 
        WHERE nombre_grupo = ? AND id_curso = ?
    ");
    $stmt->execute([$nombre_grupo, $id_curso]);

    $grupo = $stmt->fetch();

    if ($grupo) {
        $id_grupo = $grupo['id_grupo'];
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO grupos (id_curso, nombre_grupo) 
            VALUES (?, ?)
        ");
        $stmt->execute([$id_curso, $nombre_grupo]);
        $id_grupo = $pdo->lastInsertId();
    }

    // =========================
    // 3. PREPARAR QUERIES (OPTIMIZADO)
    // =========================

    // 🔍 Validar duplicados
    $stmtCheck = $pdo->prepare("
        SELECT COUNT(*) 
        FROM horarios_especiales 
        WHERE id_grupo = ?
        AND dia_semana = ?
        AND hora_inicio = ?
        AND hora_fin = ?
    ");

    // 💾 Insertar
    $stmtInsert = $pdo->prepare("
        INSERT INTO horarios_especiales
        (id_grupo, dia_semana, hora_inicio, hora_fin)
        VALUES (?, ?, ?, ?)
    ");

    // =========================
    // 4. INSERTAR SIN DUPLICADOS
    // =========================
    foreach ($dias as $dia) {

        $inicio = $hora_inicio[$dia] ?? null;
        $fin = $hora_fin[$dia] ?? null;

        if ($inicio && $fin) {

            // 🔍 Verificar si ya existe
            $stmtCheck->execute([
                $id_grupo,
                $dia,
                $inicio,
                $fin
            ]);

            $existe = $stmtCheck->fetchColumn();

            // ✅ Solo insertar si NO existe
            if (!$existe) {

                $stmtInsert->execute([
                    $id_grupo,
                    $dia,
                    $inicio,
                    $fin
                ]);
            }
        }
    }

    // =========================
    // 5. RESPUESTA
    // =========================
    header("Location: ../public/asignar_cursos.php?success=Horario especial guardado correctamente");
    exit;

} catch (Exception $e) {

    header("Location: ../public/asignar_cursos.php?error=" . urlencode($e->getMessage()));
    exit;
}