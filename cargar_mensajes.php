<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    exit;
}

require_once 'config/secrets.php';

$pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
    $user,
    $pass
);

// Usuario actual
$miId = $_SESSION['usuario_id'];

// Usuario seleccionado
$otroUsuario = $_GET['usuario'] ?? null;

if (!$otroUsuario) {
    exit;
}

/*Obtener conversación*/
$stmt = $pdo->prepare("
    SELECT *
    FROM mensajes
    WHERE
    (
        emisor_id = ?
        AND receptor_id = ?
    )
    OR
    (
        emisor_id = ?
        AND receptor_id = ?
    )
    ORDER BY fecha_envio ASC
");

$stmt->execute([
    $miId,
    $otroUsuario,
    $otroUsuario,
    $miId
]);

$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*Marcar como leidos*/
$update = $pdo->prepare("
    UPDATE mensajes
    SET leido = 1
    WHERE receptor_id = ?
    AND emisor_id = ?
");

$update->execute([
    $miId,
    $otroUsuario
]);

header('Content-Type: application/json');

echo json_encode($mensajes);
