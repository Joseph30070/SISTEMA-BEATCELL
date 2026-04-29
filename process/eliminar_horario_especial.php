<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR']); // 🔐 SOLO ADMIN

$pdo = require __DIR__ . '/../config/db.php';

$grupo = $_POST['grupo'] ?? null;

if (!$grupo) {
    echo "error";
    exit;
}

try {

    $pdo->beginTransaction();

    /*
    1. Obtener id del grupo
    */
    $stmt = $pdo->prepare("
        SELECT id_grupo
        FROM grupos
        WHERE nombre_grupo = ?
    ");

    $stmt->execute([$grupo]);

    $id_grupo = $stmt->fetchColumn();

    if (!$id_grupo) {
        throw new Exception("Grupo no encontrado");
    }

    /*
    2. Eliminar horarios especiales
    */
    $stmt = $pdo->prepare("
        DELETE FROM horarios_especiales
        WHERE id_grupo = ?
    ");

    $stmt->execute([$id_grupo]);

    /*
    3. Verificar si el grupo tiene horario normal
    */
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM grupos
        WHERE id_grupo = ?
        AND dias IS NOT NULL
        AND dias <> ''
    ");

    $stmt->execute([$id_grupo]);

    $tieneHorarioNormal = $stmt->fetchColumn();

    /*
    4. Si NO tiene horario normal → eliminar grupo
    */
    if (!$tieneHorarioNormal) {

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

    echo "error";
}
