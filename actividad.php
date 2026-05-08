<?php
session_start();
require_once 'config/secrets.php';
$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);

$mi_id = $_SESSION['usuario_id'] ?? null;
if (!$mi_id) { header("Location: login.php"); exit(); }

// Consulta para ver reseñas de gente a la que sigo
$sql = "SELECT u.nombre, p.titulo, r.fecha, u.avatar, p.id_produccion
        FROM seguidores s
        JOIN usuarios u ON s.id_seguido = u.id_usuario
        JOIN resenas r ON u.id_usuario = r.id_usuario
        JOIN producciones p ON r.id_produccion = p.id_produccion
        WHERE s.id_seguidor = ?
        ORDER BY r.fecha DESC LIMIT 10";

$stmt = $pdo->prepare($sql);
$stmt->execute([$mi_id]);
$actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>