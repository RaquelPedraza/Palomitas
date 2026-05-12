<?php
    session_start();

    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit;
    }

   require_once 'config/secrets.php';

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);

        $action = $_POST['action'] ?? null;
        $id_usuario = $_SESSION['usuario_id'];

        $esAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        /* CREAR RESEÑA */
        if ($action === 'crear') {

            $id_peli = $_POST['pelicula_id'];
            $puntuacion = $_POST['puntuacion'] ?? null;
            $contenido = $_POST['texto'] ?? null;
            $titulo = $_POST['titulo_resena'] ?? null;

            // comprobar si ya existe
            $check = $pdo->prepare("
                SELECT id_resena 
                FROM resenas 
                WHERE id_produccion = ? AND id_usuario = ?
            ");

            $check->execute([$id_peli, $id_usuario]);
            $existe = $check->fetch(PDO::FETCH_ASSOC);

            if ($existe) {

                // actualizar existente automáticamente
                $stmt = $pdo->prepare("
                    UPDATE resenas
                    SET puntuacion = ?, titulo_resena = ?, contenido = ?, fecha = NOW()
                    WHERE id_resena = ? AND id_usuario = ?
                ");

                $stmt->execute([
                    $puntuacion,
                    $titulo,
                    $contenido,
                    $existe['id_resena'],
                    $id_usuario
                ]);

                // Respuesta JSON para AJAX
                if ($esAjax) {

                    header("Location: detalles.php?id=" . $_POST['pelicula_id']);

                    exit;
                }

                // Redireccionar para petición normal
                header("Location: detalles.php?id=" . $id_peli);
                exit;

            } else {

                // crear nueva
                $stmt = $pdo->prepare("
                    INSERT INTO resenas
                    (id_produccion, id_usuario, puntuacion, titulo_resena, contenido, fecha)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");

                $stmt->execute([
                    $id_peli,
                    $id_usuario,
                    $puntuacion,
                    $titulo,
                    $contenido
                ]);

                $id = $pdo->lastInsertId();

                // AJAX
                if ($esAjax) {

                   header("Location: detalles.php?id=" . $_POST['pelicula_id']);

                    exit;
                }

                // NORMAL
                header("Location: detalles.php?id=" . $id_peli);
                exit;
            }
        }

        /* EDITAR RESEÑA */
        if ($action === 'editar') {

            $id_resena = $_POST['id_resena'];
            $puntuacion = $_POST['puntuacion'] ?? null;
            $contenido = $_POST['texto'] ?? null;
            $titulo = $_POST['titulo_resena'] ?? null;

            $stmt = $pdo->prepare("
                UPDATE resenas
                SET titulo_resena = ?, contenido = ?, puntuacion = ?, fecha = NOW()
                WHERE id_resena = ? AND id_usuario = ?
            ");

            $stmt->execute([
                $titulo,
                $contenido,
                $puntuacion,
                $id_resena,
                $id_usuario
            ]);

            header("Location: detalles.php?id=" . $_POST['pelicula_id']);

            exit;
        }

        /* ELIMINAR RESEÑA */
        if ($action === 'eliminar') {

            $id_resena = $_POST['id_resena'];

            $stmt = $pdo->prepare("
                DELETE FROM resenas
                WHERE id_resena = ? AND id_usuario = ?
            ");

            $stmt->execute([$id_resena, $id_usuario]);

            header("Location: detalles.php?id=" . $_POST['pelicula_id']);

            exit;
        }

        // Comprobamos si tenemos la peli para volver a ella, si no, lo mandamos al catálogo
        $id_retorno = $_POST['pelicula_id'] ?? '';
        if ($id_retorno) {
            header("Location: detalles.php?id=" . $id_retorno . "&error=1");
        } else {
            header("Location: index.php"); 
        }
        exit;

    } catch (PDOException $e) {

        $id_retorno = $_POST['pelicula_id'] ?? '';
        if ($id_retorno) {
            header("Location: detalles.php?id=" . $id_retorno . "&error=1");
        } else {
            header("Location: index.php"); 
        }
        exit;
    }
?>