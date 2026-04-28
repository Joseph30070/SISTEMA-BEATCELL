
<?php
$pdo = require __DIR__ . '/../config/db.php';
session_start();

$usuario  = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
$stmt->execute([$usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && trim($password) === trim($user['password'])) {

    $_SESSION['id_usuario'] = $user['id_usuario'];
    $_SESSION['nombre']     = $user['nombre'];
    $_SESSION['rol']        = strtoupper($user['rol']);

    header("Location: ../public/home.php");
    exit;

}else {
    header("Location: ../public/login.php?error=Usuario o contraseña incorrectos");
    exit;
}
