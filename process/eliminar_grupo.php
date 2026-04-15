<?php

$pdo = require __DIR__ . '/../config/db.php';

$id = $_POST['id_grupo'];

$stmt = $pdo->prepare("
    DELETE FROM grupos
    WHERE id_grupo = ?
");

$stmt->execute([$id]);

echo "ok";
