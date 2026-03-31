<?php
$config = require __DIR__ . '/config.php';

$host = $config['db']['host'] ?? '127.0.0.1';
$port = $config['db']['port'] ?? 3306;
$dbname = $config['db']['name'] ?? 'beatcell_db';
$user = $config['db']['user'] ?? 'root';
$pass = $config['db']['pass'] ?? '';
$charset = $config['db']['charset'] ?? 'utf8mb4';

// Si el host viene con puerto incluido
if (strpos($host, ':') !== false) {
    [$hostOnly, $portOnly] = explode(':', $host, 2);
    $host = $hostOnly ?: $host;
    $port = is_numeric($portOnly) ? (int) $portOnly : $port;
}

// 🔥 PUERTOS A PROBAR
$ports = [$port, 3307];

// Eliminar duplicados (por si port ya es 3307)
$ports = array_unique($ports);

$pdo = null;
$error = null;

foreach ($ports as $p) {
    try {
        $dsn = "mysql:host={$host};port={$p};dbname={$dbname};charset={$charset}";
        
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // ✅ Si conecta, salimos del loop
        return $pdo;

    } catch (PDOException $e) {
        $error = $e->getMessage();
        // sigue intentando con el siguiente puerto
    }
}

// ❌ Si ninguno funcionó
die("Error de conexión a la base de datos.\nIntentado en puertos: " . implode(', ', $ports) . "\nDetalle: " . $error);
