<?php
session_start();
header('Content-Type: application/json');

require_once 'config/secrets.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'no_login']);
    exit;
}

$pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
    $user,
    $pass
);

$id_usuario = $_SESSION['usuario_id'];
$action = $_POST['action'] ?? null;

switch ($action) {
    // CREAR LISTA
    case 'crear':

        $nombre = $_POST['nombre_lista'] ?? null;
        $visibilidad = $_POST['visibilidad'] ?? 'publica';
        $descripcion = $_POST['descripcion'] ?? null;

        if (!$nombre) {
            echo json_encode(['ok' => false, 'error' => 'sin_nombre']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO listas (id_usuario, nombre_lista, descripcion, visibilidad)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$id_usuario, $nombre, $descripcion, $visibilidad]);
        echo json_encode(['ok' => true, 'action' => 'creado']);
        break;



    // EDITAR LISTA
    case 'editar':

        $id_lista = $_POST['id_lista'] ?? null;
        $nombre = $_POST['nombre_lista'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;
        $visibilidad = $_POST['visibilidad'] ?? null;

        $stmt = $pdo->prepare("
            UPDATE listas
            SET nombre_lista = ?, descripcion = ?, visibilidad = ?
            WHERE id_lista = ? AND id_usuario = ?
        ");
        $stmt->execute([$nombre, $descripcion, $visibilidad, $id_lista, $id_usuario]);
        echo json_encode(['ok' => true, 'action' => 'editado']);
        break;



    // ELIMINAR LISTA
    case 'eliminar':

        $id_lista = $_POST['id_lista'] ?? null;

        // Eliminar relaciones
        $stmt = $pdo->prepare("
            DELETE FROM lista_produccion
            WHERE id_lista = ?
        ");
        $stmt->execute([$id_lista]);

        // Eliminar lista
        $stmt = $pdo->prepare("
            DELETE FROM listas
            WHERE id_lista = ? AND id_usuario = ?
        ");
        $stmt->execute([$id_lista, $id_usuario]);
        echo json_encode(['ok' => true, 'action' => 'eliminado']);
        break;
    
    // AÑADIR PELÍCULA A LISTA
    case 'add':

        $id_lista = $_POST['id_lista'] ?? null;
        $id_produccion = $_POST['id_produccion'] ?? null;

        if (!$id_lista || !$id_produccion) {
            echo json_encode([
                'ok' => false,
                'error' => 'datos_invalidos'
            ]);
            exit;
        }

        // comprobar propiedad
        $stmt = $pdo->prepare("
            SELECT id_lista
            FROM listas
            WHERE id_lista = ? AND id_usuario = ?
        ");
        $stmt->execute([$id_lista, $id_usuario]);

        if (!$stmt->fetch()) {

            echo json_encode([
                'ok' => false,
                'error' => 'sin_permiso'
            ]);

            exit;
        }

        // insertar relación
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO lista_produccion
            (id_lista, id_produccion)
            VALUES (?, ?)
        ");

        $stmt->execute([$id_lista, $id_produccion]);

        echo json_encode([
            'ok' => true,
            'action' => 'añadido'
        ]);

        break;

    default:
        echo json_encode(['ok' => false, 'error' => 'accion_no_valida']);
}