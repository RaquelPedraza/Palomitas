<?php
session_start();
$host = '127.0.0.1';
$port = '3306'; // o 3307 si usas ese
$db = 'palomitas_db';

$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
// SOLO ADMIN
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// ELIMINAR USUARIO
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];

    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id]);

    header("Location: admin_usuarios.php");
    exit;
}


// CAMBIAR ROL
if (isset($_GET['rol']) && isset($_GET['id'])) {
    $nuevoRol = $_GET['rol'];
    $id = $_GET['id'];

    $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id_usuario = ?");
    $stmt->execute([$nuevoRol, $id]);

    header("Location: admin_usuarios.php");
    exit;
}

// OBTENER USUARIOS
$stmt = $pdo->query("SELECT id_usuario, nombre, email, rol FROM usuarios");
$usuarios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Usuarios</title>
    <link rel="stylesheet" href="css/estilos.css?v=5">

    <style>

    body{
        background:#141414;
        color:white;
        font-family:Arial;
        padding:40px;
    }

    table{
        width:100%;
        border-collapse:collapse;
        margin-top:30px;
        background:#1f1f1f;
    }

    th, td{
        padding:15px;
        border:1px solid #333;
        text-align:center;
    }

    th{
        background:#e50914;
    }

    a{
        color:#ff4b4b;
        text-decoration:none;
        margin:0 5px;
    }

    a:hover{
        text-decoration:underline;
    }

    </style>
</head>
<body>

<h1>Panel de Administración</h1>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Email</th>
        <th>Rol</th>
        <th>Acciones</th>
    </tr>

    <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= $u['id_usuario'] ?></td>
            <td><?= $u['nombre'] ?></td>
            <td><?= $u['email'] ?></td>
            <td><?= $u['rol'] ?></td>

            <td>
                <!-- CAMBIAR ROL -->
                <?php if ($u['rol'] === 'usuario'): ?>
                    <a href="?id=<?= $u['id_usuario'] ?>&rol=admin">Hacer Admin</a>
                <?php else: ?>
                    <a href="?id=<?= $u['id_usuario'] ?>&rol=usuario">Quitar Admin</a>
                <?php endif; ?>

                <!-- EDITAR -->
                <a href="editar_usuario.php?id=<?= $u['id_usuario'] ?>">Editar</a>
                <!-- ELIMINAR -->
                <a href="?eliminar=<?= $u['id_usuario'] ?>" onclick="return confirm('¿Eliminar usuario?')">Eliminar</a>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

<br>
<a href="index.php">⬅ Volver</a>

</body>
</html>