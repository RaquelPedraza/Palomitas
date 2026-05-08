<?php
// Iniciar sesión para poder guardar datos del usuario después
session_start();
require_once 'config/secrets.php';

$error = "";

// Conexión a la BD
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

    //AVATAR 
   $avatar = 'img/default-avatar.png';

    if (!empty($_FILES['avatar']['name'])) {
        $archivo = $_FILES['avatar'];

        // Tipos MIME permitidos
        $tiposPermitidos = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];
        //Extensiones permitidas
        $extensionesPermitidas = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        //Peso máximo 2MB   
        $maxPeso = 2 * 1024 * 1024;

        // Obtener extensión del archivo
        $extension = strtolower(
            pathinfo($archivo['name'], PATHINFO_EXTENSION)
        );

        // Validar que el archivo sea una imagen real
        $esImagenReal = getimagesize($archivo['tmp_name']);
        if (!$esImagenReal) {
            $mensaje_servidor =
                "<p class='rojo'>El archivo no es una imagen válida.</p>";

        }

        //Validar tipo MIME
        elseif (!in_array($archivo['type'], $tiposPermitidos)) {
            $mensaje_servidor =
                "<p class='rojo'>Formato no permitido. Usa JPG, PNG o WEBP.</p>";

        }

        // Validar extensión
        elseif (!in_array($extension, $extensionesPermitidas)) {
            $mensaje_servidor =
                "<p class='rojo'>Extensión no permitida.</p>";
        }

        // Validar peso
        elseif ($archivo['size'] > $maxPeso) {
            $mensaje_servidor =
                "<p class='rojo'>La imagen supera el máximo de 2MB.</p>";
        }

        else {
            // Validar dimensiones
            $dimensiones = getimagesize($archivo['tmp_name']);

            $ancho = $dimensiones[0];
            $alto = $dimensiones[1];

            if ($ancho > 1000 || $alto > 1000) {
                $mensaje_servidor =
                    "<p class='rojo'>La imagen es demasiado grande. Máximo 1000x1000.</p>";
            } else {

                //Nombre único
                $nombreArchivo = uniqid() . "." . $extension;
                $rutaDestino = "img/avatares/" . $nombreArchivo;
                if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                    $avatar = $rutaDestino;
                } else {
                    $mensaje_servidor =
                        "<p class='rojo'>Error al subir la imagen.</p>";
                }
            }
        }
    }

    // VALIDACIÓN DE CONTRASEÑA
        if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
            $mensaje_servidor = "<p class='rojo'>La contraseña debe tener mínimo 8 caracteres, una mayúscula y un número.</p>";
        } else {

    // 1. Comprobar si el usuario ya existe
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre = ? OR email = ?");
        $stmt->execute([$nombre, $email]);

        if ($stmt->fetch()) {

            $mensaje_servidor = "<p class='rojo'>Error: El usuario o email ya están registrados.</p>";

        } else {

            // 2. Encriptar la contraseña
            $hash_password = password_hash($password, PASSWORD_DEFAULT);

            // 3. Insertar usuario
            $stmt = $pdo->prepare("
                INSERT INTO usuarios
                (nombre, email, password, rol, avatar)
                VALUES (?, ?, ?, 'usuario', ?)
            ");

            if ($stmt->execute([$nombre, $email, $hash_password, $avatar])) {

                $mensaje_servidor = "<p class='verde'>¡Registro exitoso! Ya puedes iniciar sesión.</p>";

                @mail(
                    $email,
                    "Bienvenido a Palomitas",
                    "Hola $nombre, gracias por registrarte en Palomitas"
                );
            }
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
        body { background-color: #141414; color: white; margin: 0; }
        .ajax-check { display: flex; gap: 10px; align-items: center; }
        .ajax-check input { flex: 1; }
        .ajax-check button { width: auto; padding: 12px; background: #333; color: white; border: 1px solid #555; cursor: pointer;}
        .ajax-check button:hover { background: #555; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo" style="display: flex; align-items: center; text-decoration: none;">
            <img src="img/logo.png" alt="Logo Palomitas" style="height: 60px; margin-right: 10px;">
        </a>
        <div class="enlaces">
            <a href="index.php">Catálogo</a>
            <a href="login.php">Iniciar Sesión</a>
        </div>
    </nav>

    <div class="contenedor-formulario">
        <h2>Crear Cuenta</h2>
        
        <?= $mensaje_servidor ?>

        <form method="POST" action="registro.php" enctype="multipart/form-data">
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
            <div class="grupo-input">
                <input type="file" name="avatar" id="avatar" accept="image/*">
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