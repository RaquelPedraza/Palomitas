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

                    echo json_encode([
                        'ok' => true,
                        'action' => 'updated',
                        'id_resena' => $existe['id_resena'],
                        'titulo_resena' => $titulo,
                        'contenido' => $contenido,
                        'puntuacion' => $puntuacion
                    ]);

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

                    echo json_encode([
                        'ok' => true,
                        'action' => 'created',
                        'id_resena' => $id,
                        'titulo_resena' => $titulo,
                        'contenido' => $contenido,
                        'puntuacion' => $puntuacion
                    ]);

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

            echo json_encode([
                'ok' => true,
                'action' => 'edited',
                'id_resena' => $id_resena,
                'titulo_resena' => $titulo,
                'contenido' => $contenido,
                'puntuacion' => $puntuacion
            ]);

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

            echo json_encode([
                'ok' => true,
                'action' => 'deleted',
                'id_resena' => $id_resena
            ]);

            exit;
        }

        echo json_encode([
            'ok' => false,
            'error' => 'accion_no_valida'
        ]);

    } catch (PDOException $e) {

        echo json_encode([
            'ok' => false,
            'error' => $e->getMessage()
        ]);
    }
?>