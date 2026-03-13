<?php
// Iniciar sesión para poder guardar datos del usuario después
session_start();
require_once 'config/secrets.php';

// Conexión a la BD
$host = '127.0.0.1'; $port = '3307'; $db = 'palomitas'; $user = 'raquel'; $pass = 'cine';
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de BD: " . $e->getMessage());
}

$mensaje_servidor = "";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 1. Comprobar si el usuario ya existe (por si no usaron el botón AJAX)
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre = ? OR email = ?");
    $stmt->execute([$nombre, $email]);
    
    if ($stmt->fetch()) {
        $mensaje_servidor = "<p class='rojo'>Error: El usuario o email ya están registrados.</p>";
    } else {
        // 2. Encriptar la contraseña 
        $hash_password = password_hash($password, PASSWORD_DEFAULT);
        
        // 3. Insertar en la Base de Datos
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'usuario')");
        if ($stmt->execute([$nombre, $email, $hash_password])) {
            $mensaje_servidor = "<p class='verde'>¡Registro exitoso! Ya puedes iniciar sesión.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro | Palomitas</title>
    <link rel="stylesheet" href="css/estilos.css?v=2">
    <style>
        body { background-color: #141414; color: white; font-family: Arial, sans-serif; margin: 0; }
        .ajax-check { display: flex; gap: 10px; align-items: center; }
        .ajax-check input { flex: 1; }
        .ajax-check button { width: auto; padding: 12px; background: #333; color: white; border: 1px solid #555; cursor: pointer;}
        .ajax-check button:hover { background: #555; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">🍿 Palomitas</a>
        <div class="enlaces">
            <a href="index.php">Catálogo</a>
            <a href="login.php">Iniciar Sesión</a>
        </div>
    </nav>

    <div class="contenedor-formulario">
        <h2>Crear Cuenta</h2>
        
        <?= $mensaje_servidor ?>

        <form method="POST" action="registro.php">
            <div class="grupo-input ajax-check">
                <input type="text" id="usuario" name="nombre" placeholder="Nombre de usuario" required>
                <button type="button" id="btnComprobar">Comprobar</button>
            </div>
            <div id="mensaje" style="margin-bottom: 15px;"></div> 

            <div class="grupo-input">
                <input type="email" name="email" placeholder="Correo electrónico" required>
            </div>
            <div class="grupo-input">
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            
            <button type="submit" class="btn-rojo">Registrarse</button>
        </form>
    </div>

    <script>
        document.getElementById('btnComprobar').onclick = function() {
            let user = document.getElementById('usuario').value;
            let divMsg = document.getElementById('mensaje');
            divMsg.innerHTML = "";

            if (user === "") {
                divMsg.innerHTML = "<p class='rojo'>Escribe algo primero.</p>";
                return;
            }

            fetch('scripts/comprobar.php?usuario=' + user)
                .then(res => res.json())
                .then(data => {
                    if (data.ok) {
                        divMsg.innerHTML = "<p class='verde'>✔ " + data.mensaje + "</p>";
                    } else {
                        divMsg.innerHTML = "<p class='rojo'>✖ " + data.mensaje + "</p>";
                    }
                });
        };
    </script>
</body>
</html>