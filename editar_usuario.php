<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado");
    }
require_once 'config/secrets.php';

$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);

$id = $_GET['id'] ?? null;
if (
    $_SESSION['usuario_id'] != $id &&
    $_SESSION['usuario_rol'] !== 'admin'
) {
    die("No autorizado");
}

if (!$id) {
    header("Location: index.php");
    exit;
}

// SEGURIDAD
// Solo puede editar:
// - el propio usuario
// - o un admin

if (
    $_SESSION['usuario_id'] != $id &&
    $_SESSION['usuario_rol'] !== 'admin'
) {
    die("No autorizado");
}

// Obtener usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    die("Usuario no encontrado");
}

// Guardar cambios
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar_password'] ?? '';

    if (!empty($password)) {

        if ($password !== $confirmar) {
            die("Las contraseñas no coinciden");
        }

        if (!preg_match('/(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
            die("La contraseña debe tener mínimo 8 caracteres, una mayúscula y un número");
        }

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            UPDATE usuarios
            SET nombre = ?, email = ?, password = ?
            WHERE id_usuario = ?
        ");

        $stmt->execute([
            $nombre,
            $email,
            $hash_password,
            $id
        ]);

    } else {

        $stmt = $pdo->prepare("
            UPDATE usuarios
            SET nombre = ?, email = ?
            WHERE id_usuario = ?
        ");

        $stmt->execute([
            $nombre,
            $email,
            $id
        ]);
    }

    $stmt->execute([$nombre, $email, $id]);

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="css/estilos.css?v=5">
    <style>
        h2 {
            display: flex;
            justify-content: center;
        }

        body{
            background:#141414;
            color:white;
            font-family:Arial;
        }

        .contenedor{
            max-width:500px;
            margin:80px auto;
            background:#1f1f1f;
            padding:30px;
            border-radius:10px;
        }

        form{
            display:flex;
            flex-direction:column;
            gap:15px;
        }

        input{
            padding:12px;
            border:none;
            background:#2a2a2a;
            color:white;
            font-size: 1em;
        }

        button{
            background:#e50914;
            color:white;
            border:none;
            padding:12px;
            cursor:pointer;
            font-size: large;
        }

        button:hover{
            background:#b20710;
        }
    </style>
</head>
<body>
<!-- NAVBAR -->
    <?php include 'includes/navbar.php'; ?>

    <a href="javascript:history.back()" class="boton-volver">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Volver
    </a>
<div class="contenedor">

<h2>EDITAR PERFIL</h2>

<form method="POST" action="editar_usuario.php" enctype="multipart/form-data">

    <input
        type="text"
        name="nombre"
        value="<?= htmlspecialchars($usuario['nombre']) ?>"
        required
    >

    <input
        type="email"
        name="email"
        value="<?= htmlspecialchars($usuario['email']) ?>"
        required
    >

    <input
        type="password"
        name="password"
        placeholder="Nueva contraseña"
    >

    <input
        type="password"
        name="confirmar_password"
        placeholder="Confirmar nueva contraseña"
    >
    <label>Cambiar foto de perfil:</label>
    <input type="file" name="avatar" accept="image/*">

    

    <button type="submit">
        Guardar Cambios
    </button>

    
</form>

</div>

</body>
</html>