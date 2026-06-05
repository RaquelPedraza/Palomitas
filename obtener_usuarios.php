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
    u.id_usuario,
    u.nombre,
    u.avatar,

    (
        SELECT COUNT(*)
        FROM mensajes m
        WHERE m.emisor_id = u.id_usuario
        AND m.receptor_id = ?
        AND m.leido = 0
    ) AS pendientes

FROM usuarios u

WHERE u.id_usuario != ?

ORDER BY nombre ASC
");

$stmt->execute([
    $_SESSION['usuario_id'],
    $_SESSION['usuario_id']
]);

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Indicamos que devolveremos JSON
header('Content-Type: application/json');

// Enviamos datos
echo json_encode($usuarios);
