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
</head>
<body>

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

</body>
</html>