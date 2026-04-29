<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR']);

$pdo = require __DIR__ . '/../config/db.php';

$id_grupo = $_POST['id_grupo'] ?? null;

if (!$id_grupo) {
    echo "error: sin id";
    exit;
}

try {

    $pdo->beginTransaction();

    /*
    1. Eliminar horarios especiales del grupo
    */
    $stmt = $pdo->prepare("
        DELETE FROM horarios_especiales
        WHERE id_grupo = ?
    ");
    $stmt->execute([$id_grupo]);

    /*
    2. Verificar si el grupo tiene horario normal
    */
    $stmt = $pdo->prepare("
        SELECT dias
        FROM grupos
        WHERE id_grupo = ?
    ");
    $stmt->execute([$id_grupo]);

    $dias = $stmt->fetchColumn();

    /*
    3. Si NO tiene horario normal → eliminar grupo
    */
    if (empty($dias)) {

        $stmt = $pdo->prepare("
            DELETE FROM grupos
            WHERE id_grupo = ?
        ");
        $stmt->execute([$id_grupo]);
    }

    $pdo->commit();

    echo "ok";

} catch (Exception $e) {

    $pdo->rollBack();

    echo "error: " . $e->getMessage();
}

