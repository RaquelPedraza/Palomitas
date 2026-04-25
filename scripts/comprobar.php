<?php
// scripts/comprobar.php
header('Content-Type: application/json');

require_once '../config/secrets.php';

// Conexión a tu BD Palomitas
$host = '127.0.0.1';
$port = '3306';
$db   = 'palomitas';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["ok" => false, "mensaje" => "Error de BD: " . $e->getMessage()]);
    exit;
}

// Recibimos el texto que escribe el usuario en el formulario
$usuario_input = $_GET['usuario'] ?? '';
$resp = ["ok" => false, "mensaje" => ""];

if ($usuario_input == "") {
    $resp["mensaje"] = "No se ha enviado usuario.";
} else {
    // 🔍 EL CAMBIO CLAVE: Buscamos en tu columna 'nombre'
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre = ?");
    $stmt->execute([$usuario_input]);
    $existe = $stmt->fetch();

    if ($existe) {
        $resp["ok"] = false;
        $resp["mensaje"] = "El usuario '$usuario_input' NO está disponible (ya existe)";
    } else {
        $resp["ok"] = true;
        $resp["mensaje"] = "¡El nombre '$usuario_input' está disponible!";
    }
}

echo json_encode($resp);
?>