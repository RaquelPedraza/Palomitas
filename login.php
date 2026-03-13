<?php
session_start();
require_once 'config/secrets.php';

$host = '127.0.0.1'; $port = '3307'; $db = 'palomitas'; $user = 'raquel'; $pass = 'cine';
$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $password = $_POST['password'] ?? '';

    // Buscamos al usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre = ?");
    $stmt->execute([$nombre]);
    $usuario_db = $stmt->fetch();

    // Verificamos si existe y si la contraseña coincide
    if ($usuario_db && password_verify($password, $usuario_db['password'])) {
        // ¡LOGIN CORRECTO! Guardamos sus datos en la sesión
        $_SESSION['usuario_id'] = $usuario_db['id_usuario'];
        $_SESSION['usuario_nombre'] = $usuario_db['nombre'];
        $_SESSION['usuario_rol'] = $usuario_db['rol'];
        
        // Redirigimos al catálogo
        header("Location: index.php");
        exit;
    } else {
        $error = "<p class='rojo'>Usuario o contraseña incorrectos.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login | Palomitas</title>
    <link rel="stylesheet" href="css/estilos.css?v=2">
    <style>body { background-color: #141414; color: white; font-family: Arial, sans-serif; margin: 0; }</style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">🍿 Palomitas</a>
        <div class="enlaces">
            <a href="index.php">Catálogo</a>
            <a href="registro.php">Crear Cuenta</a>
        </div>
    </nav>

    <div class="contenedor-formulario">
        <h2>Iniciar Sesión</h2>
        <?= $error ?>
        <form method="POST" action="login.php">
            <div class="grupo-input">
                <input type="text" name="nombre" placeholder="Nombre de usuario" required>
            </div>
            <div class="grupo-input">
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn-rojo">Entrar</button>
        </form>
    </div>
</body>
</html>