<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'no_login']);
    exit;
}

require_once 'config/secrets.php'; 

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => 'db_error']);
    exit;
}

$id_seguidor = $_SESSION['usuario_id'];
$id_seguido = $_POST['id'] ?? null;

// Evitar que te sigas a ti mismo o que falte la ID
if (!$id_seguido || $id_seguidor == $id_seguido) {
    echo json_encode(['ok' => false, 'error' => 'invalid_id']);
    exit;
}

// Comprobar si ya lo sigo
$stmt = $pdo->prepare("SELECT id_seguidor FROM seguidores WHERE id_seguidor = ? AND id_seguido = ?");
$stmt->execute([$id_seguidor, $id_seguido]);
$existe = $stmt->fetch();

if ($existe) {
    // DEJAR DE SEGUIR
    $delete = $pdo->prepare("DELETE FROM seguidores WHERE id_seguidor = ? AND id_seguido = ?");
    $delete->execute([$id_seguidor, $id_seguido]);
    echo json_encode(['ok' => true, 'estado' => 'quitado']);
} else {
    // SEGUIR
    $insert = $pdo->prepare("INSERT INTO seguidores (id_seguidor, id_seguido) VALUES (?, ?)");
    $insert->execute([$id_seguidor, $id_seguido]);
    echo json_encode(['ok' => true, 'estado' => 'añadido']);
}