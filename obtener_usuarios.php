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

// Obtenemos todos los usuarios
// los ordenamos por último mensaje enviado/recibido con el usuario actual, de más reciente a más antiguo. 
//Si no hay mensajes, se ordena alfabéticamente por nombre. 
//También obtenemos el número de mensajes pendientes de leer para cada usuario.
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
    ) AS pendientes,

    (
        SELECT MAX(fecha_envio)
        FROM mensajes m2
        WHERE
            (
                m2.emisor_id = ?
                AND m2.receptor_id = u.id_usuario
            )
            OR
            (
                m2.emisor_id = u.id_usuario
                AND m2.receptor_id = ?
            )
    ) AS ultimo_mensaje

FROM usuarios u

WHERE u.id_usuario != ?

ORDER BY ultimo_mensaje DESC, nombre ASC
");

$stmt->execute([
    $_SESSION['usuario_id'], // pendientes
    $_SESSION['usuario_id'], // primer MAX
    $_SESSION['usuario_id'], // segundo MAX
    $_SESSION['usuario_id']  // WHERE
]);

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Indicamos que devolveremos JSON
header('Content-Type: application/json');

// Enviamos datos
echo json_encode($usuarios);
