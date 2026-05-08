<?php
session_start();
require_once 'config/secrets.php';

$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);

$id = $_GET['id'] ?? null;

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

    $stmt = $pdo->prepare("
        UPDATE usuarios
        SET nombre = ?, email = ?
        WHERE id_usuario = ?
    ");

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
        }

        button{
            background:#e50914;
            color:white;
            border:none;
            padding:12px;
            cursor:pointer;
            font-weight:bold;
        }

        button:hover{
            background:#b20710;
        }
    </style>
</head>
<body>

<div class="contenedor">

<h1>Editar Perfil</h1>

<form method="POST">

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

    <button type="submit">
        Guardar Cambios
    </button>

</form>

</div>

</body>
</html>