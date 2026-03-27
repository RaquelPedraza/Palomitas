<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

//CONEXIÓN
$host = '127.0.0.1'; 
$port = '3307'; 
$db = 'palomitas'; 
$user = 'raquel'; 
$pass = 'cine';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_peli = $_POST['pelicula_id'];
        $contenido = $_POST['texto'];
        $nombre_user = $_SESSION['usuario_nombre'];

        // Verifcar existencia de registro/comentario para evitar duplicados
        $check = $pdo->prepare("SELECT id_resena FROM resenas WHERE id_produccion = ? AND id_usuario = ?");
        $check->execute([$id_peli, $nombre_user]);
        $existe = $check->fetch();

        if ($existe) {
            // Actualización del registro/comentario
            $sql = "UPDATE resenas SET contenido = ? WHERE id_resena = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$contenido, $existe['id_resena']]);
        } else {
            // Inserción del registro/comentario
            $sql = "INSERT INTO resenas (id_produccion, id_usuario, contenido) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_peli, $nombre_user, $contenido]);
        }

        header("Location: detalles.php?id=" . $id_peli);
        exit;
    }
} catch (PDOException $e) {
    die("Error al guardar comentario: " . $e->getMessage());
}