<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

header('Content-Type: application/json');

require_once 'config/secrets.php';

$pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
    $user,
    $pass
);

$id_usuario = $_SESSION['usuario_id'];
$id_produccion = $_GET['id_produccion'] ?? null;

$stmt = $pdo->prepare("
    SELECT 
        l.id_lista,
        l.nombre_lista,
        CASE 
            WHEN lp.id_produccion IS NOT NULL THEN 1
            ELSE 0
        END AS contiene
    FROM listas l
    LEFT JOIN lista_produccion lp 
        ON lp.id_lista = l.id_lista 
        AND lp.id_produccion = ?
    WHERE l.id_usuario = ?
    ORDER BY l.nombre_lista ASC
");

$stmt->execute([$id_produccion, $id_usuario]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
