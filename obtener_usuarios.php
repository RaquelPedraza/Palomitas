<?php

// Iniciamos sesión
session_start();

// Si no está autenticado, cortamos ejecución
if (!isset($_SESSION['usuario_id'])) {
    exit;
}

// Conexión BD
require_once 'config/secrets.php';

$pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
    $user,
    $pass
);

// Obtenemos todos los usuarios excepto el actual
$stmt = $pdo->prepare("
    SELECT
        id_usuario,
        nombre,
        avatar
    FROM usuarios
    WHERE id_usuario != ?
    ORDER BY nombre ASC
");

$stmt->execute([
    $_SESSION['usuario_id']
]);

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Indicamos que devolveremos JSON
header('Content-Type: application/json');

// Enviamos datos
echo json_encode($usuarios);
