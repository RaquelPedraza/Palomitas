<?php
    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['ok' => false, 'error' => 'no_login']);
        exit;
    }

    require_once 'config/secrets.php';

    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );

    $id_usuario = $_SESSION['usuario_id'];
    $id_produccion = $_POST['id'] ?? null;

    if (!$id_produccion) {
        echo json_encode(['ok' => false, 'error' => 'no_id']);
        exit;
    }

    // comprobar si existe
    $stmt = $pdo->prepare("
        SELECT id_favorito 
        FROM favoritos 
        WHERE id_usuario = ? AND id_produccion = ?
    ");
    $stmt->execute([$id_usuario, $id_produccion]);

    $existe = $stmt->fetch();

    if ($existe) {
        //QUITAR
        $delete = $pdo->prepare("
            DELETE FROM favoritos 
            WHERE id_usuario = ? AND id_produccion = ?
        ");
        $delete->execute([$id_usuario, $id_produccion]);

        echo json_encode(['ok' => true, 'estado' => 'quitado']);
    } else {
        //AÑADIR
        $insert = $pdo->prepare("
            INSERT INTO favoritos (id_usuario, id_produccion)
            VALUES (?, ?)
        ");
        $insert->execute([$id_usuario, $id_produccion]);

        echo json_encode(['ok' => true, 'estado' => 'añadido']);
    }
?>