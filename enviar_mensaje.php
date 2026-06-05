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

/*Datos*/
$emisor = $_SESSION['usuario_id'];

$receptor = $_POST['receptor'] ?? null;

$mensaje = trim(
    $_POST['mensaje'] ?? ''
);

/*Validaciones*/
if (!$receptor) {
    exit;
}

if ($mensaje === '') {
    exit;
}

/*Insertar mensaje*/
$stmt = $pdo->prepare("
    INSERT INTO mensajes
    (
        emisor_id,
        receptor_id,
        mensaje
    )
    VALUES
    (
        ?,
        ?,
        ?
    )
");

$stmt->execute([
    $emisor,
    $receptor,
    $mensaje
]);

echo "ok";