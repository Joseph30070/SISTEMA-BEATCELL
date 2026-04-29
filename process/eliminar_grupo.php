<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR']); // 🔐 SOLO ADMIN

$pdo = require __DIR__ . '/../config/db.php';

$id = $_POST['id_grupo'] ?? null;

if (!$id) {
    echo "error";
    exit;
}

try {

    $stmt = $pdo->prepare("
        DELETE FROM grupos
        WHERE id_grupo = ?
    ");

    $stmt->execute([$id]);

    echo "ok";

} catch (Exception $e) {

    echo "error";

}
