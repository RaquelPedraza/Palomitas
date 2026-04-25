<?php
    session_start();

    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit;
    }

   require_once 'config/secrets.php';

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $id_peli = $_POST['pelicula_id'];
            $puntuacion = $_POST['puntuacion'];
            $contenido = $_POST['texto'];
            $id_usuario = $_SESSION['usuario_id'];

            // Comprobar si ya existe reseña
            $check = $pdo->prepare("SELECT id_resena FROM resenas WHERE id_produccion = ? AND id_usuario = ?");
            $check->execute([$id_peli, $id_usuario]);
            $existe = $check->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                // ACTUALIZAR
                $sql = "UPDATE resenas 
                        SET puntuacion = ?, contenido = ?, fecha = NOW()
                        WHERE id_resena = ?";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$puntuacion, $contenido, $existe['id_resena']]);

            } else {
                // AÑADIR
                $sql = "INSERT INTO resenas 
                        (id_produccion, id_usuario, puntuacion, contenido, fecha)
                        VALUES (?, ?, ?, ?, NOW())";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id_peli, $id_usuario, $puntuacion, $contenido]);
            }

            header("Location: detalles.php?id=" . $id_peli);
            exit;
        }

    } catch (PDOException $e) {
        die("Error al guardar reseña: " . $e->getMessage());
    }
?>