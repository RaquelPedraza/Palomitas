<?php
session_start();
require_once 'config/secrets.php';

$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);

$error = "";
$nombre = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $password = $_POST['password'] ?? '';

    // Buscamos al usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre = ?");
    $stmt->execute([$nombre]);
    $usuario_db = $stmt->fetch();

    // Comprobar si está bloqueado
    if ($usuario_db && $usuario_db['bloqueado_hasta'] && $usuario_db['bloqueado_hasta'] > date('Y-m-d H:i:s')) {
        $error = "<p class='rojo'>Cuenta bloqueada temporalmente. Inténtalo más tarde.</p>";
    } else {

    // Verificamos si existe y si la contraseña coincide
    if ($usuario_db && password_verify($password, $usuario_db['password'])) {

    // LOGIN CORRECTO → resetear intentos
        $stmt = $pdo->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = ?");
        $stmt->execute([$usuario_db['id_usuario']]);

        // Guardar sesión
        $_SESSION['usuario_id'] = $usuario_db['id_usuario'];
        session_regenerate_id(true);

        $_SESSION['usuario_nombre'] = $usuario_db['nombre'];
        $_SESSION['usuario_rol'] = $usuario_db['rol'];

        // Seguridad extra
        session_regenerate_id(true);

        // Redirigir
        header("Location: index.php");
        exit;

    } else {

        if ($usuario_db) {
            $intentos = $usuario_db['intentos_fallidos'] + 1;

            if ($intentos >= 5) {
                $bloqueo = date('Y-m-d H:i:s', strtotime('+5 minutes'));

                $stmt = $pdo->prepare("UPDATE usuarios SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id_usuario = ?");
                $stmt->execute([$intentos, $bloqueo, $usuario_db['id_usuario']]);

                $error = "<p class='rojo'>Demasiados intentos. Cuenta bloqueada 5 minutos.</p>";
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET intentos_fallidos = ? WHERE id_usuario = ?");
                $stmt->execute([$intentos, $usuario_db['id_usuario']]);

                $error = "<p class='rojo'>Usuario o contraseña incorrectos.</p>";
            }

        } else {
            $error = "<p class='rojo'>Usuario o contraseña incorrectos.</p>";
        }
    }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login | Palomitas</title>
    <link rel="stylesheet" href="css/estilos.css?v=3">
    <style>body { background-color: #141414; color: white; margin: 0; }</style>
</head>
<body>
     <nav class="navbar">
        <a href="index.php" class="logo" style="display: flex; align-items: center; text-decoration: none;">
            <img src="img/logo.png" alt="Logo Palomitas" style="height: 60px; margin-right: 10px;">
        </a>
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
                <input
                    type="text"
                    name="nombre"
                    placeholder="Nombre de usuario"
                    value="<?= htmlspecialchars($nombre) ?>"
                    required
                >
            </div>
            <div class="grupo-input">
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn-rojo">Entrar</button>
        </form>
    </div>
</body>
</html>