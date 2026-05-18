<?php

require_once __DIR__ . '/../config/auth.php';

checkRole(['ADMINISTRADOR']);

$pdo = require __DIR__ . '/../config/db.php';

// =========================
// ZONA HORARIA PERÚ
// =========================
date_default_timezone_set('America/Lima');

try {

    // =========================
    // VALIDAR CREDENCIALES
    // =========================
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validar vacíos
    if (!$usuario || !$password) {
        throw new Exception("Completa las credenciales");
    }

    // =========================
    // BUSCAR ADMINISTRADOR
    // =========================
    $stmt = $pdo->prepare("
        SELECT *
        FROM usuarios
        WHERE usuario = ?
        AND rol = 'ADMINISTRADOR'
    ");

    $stmt->execute([$usuario]);

    $admin = $stmt->fetch();

    if (!$admin) {
        throw new Exception("Usuario administrador no encontrado");
    }

    // =========================
    // VERIFICAR CONTRASEÑA
    // =========================
    // NOTA:
    // Actualmente el sistema usa contraseñas
    // en texto plano.
    // En una futura mejora se migrará
    // a password_hash() y password_verify().
    if ($password !== $admin['password']) {
        throw new Exception("Contraseña incorrecta");
    }

    // =========================
    // OBTENER TODAS LAS TABLAS
    // =========================
    $stmt = $pdo->query("SHOW TABLES");

    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$tablas) {
        throw new Exception("No se encontraron tablas en la base de datos");
    }

    // =========================
    // INICIAR CONTENIDO SQL
    // =========================
    $sql = "-- ======================================\n";
    $sql .= "-- BACKUP AUTOMÁTICO BEATCELL\n";
    $sql .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- ======================================\n";
    $sql .= "-- IMPORTANTE:\n";
    $sql .= "-- Crear previamente una base de datos llamada 'beatcell_db'\n";
    $sql .= "-- antes de importar este archivo.\n";
    $sql .= "-- Luego importar este backup desde phpMyAdmin.\n";
    $sql .= "-- ======================================\n\n";

    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    // =========================
    // RECORRER TABLAS
    // =========================
    foreach ($tablas as $tabla) {

        // =========================
        // ESTRUCTURA TABLA
        // =========================
        $stmtCreate = $pdo->query("
            SHOW CREATE TABLE `$tabla`
        ");

        $createData = $stmtCreate->fetch();

        $createSQL = $createData['Create Table'] ?? '';

        $sql .= "-- ======================================\n";
        $sql .= "-- TABLA: $tabla\n";
        $sql .= "-- ======================================\n\n";

        $sql .= "DROP TABLE IF EXISTS `$tabla`;\n";

        $sql .= $createSQL . ";\n\n";

        // =========================
        // DATOS TABLA
        // =========================
        $stmtDatos = $pdo->query("
            SELECT *
            FROM `$tabla`
        ");

        $filas = $stmtDatos->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($filas)) {

            foreach ($filas as $fila) {

                // Columnas
                $columnas = array_map(
                    fn($c) => "`$c`",
                    array_keys($fila)
                );

                // Valores
                $valores = array_map(function ($valor) use ($pdo) {

                    if (is_null($valor)) {
                        return "NULL";
                    }

                    return $pdo->quote($valor);

                }, array_values($fila));

                // INSERT
                $sql .= "INSERT INTO `$tabla` ("
                    . implode(", ", $columnas)
                    . ") VALUES ("
                    . implode(", ", $valores)
                    . ");\n";
            }

            $sql .= "\n";
        }
    }

    // =========================
    // ACTIVAR FOREIGN KEYS
    // =========================
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

    // =========================
    // NOMBRE ARCHIVO
    // =========================
    $nombreArchivo =
        "backup_beatcell_" .
        date('Y-m-d_H-i-s') .
        ".sql";

    // =========================
    // DESCARGA
    // =========================
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Content-Length: ' . strlen($sql));

    // Evitar cache
    header('Pragma: no-cache');
    header('Expires: 0');

    echo $sql;

    exit;

} catch (Exception $e) {

    header(
        "Location: ../public/perfil.php?error="
        . urlencode($e->getMessage())
    );

    exit;
}