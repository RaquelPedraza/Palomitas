<?php
session_start();

require_once 'config/secrets.php';

try {

$pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4"
);

} catch (PDOException $e) {

    die("Error de conexión: " . $e->getMessage());

}

/*
| SEGURIDAD
*/

if (
    !isset($_SESSION['usuario_id']) ||
    !isset($_GET['id']) ||
    !isset($_GET['accion'])
) {

    header("Location: index.php");
    exit;
}

/*
| DATOS
*/

$mi_id = $_SESSION['usuario_id'];

$id_perfil = $_GET['id'];

$accion = $_GET['accion'];

/*
| EVITAR SEGUIRSE A SÍ MISMO
*/

if ($mi_id == $id_perfil) {

    header("Location: perfil.php");
    exit;
}

try {

    /*SEGUIR
    */

    if ($accion === 'follow') {

        $sql = "
            INSERT IGNORE INTO seguidores
            (id_seguidor, id_seguido)
            VALUES (?, ?)
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([$mi_id, $id_perfil]);
    }

    /*
    | DEJAR DE SEGUIR
    */

    elseif ($accion === 'unfollow') {

        $sql = "
            DELETE FROM seguidores
            WHERE id_seguidor = ?
            AND id_seguido = ?
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([$mi_id, $id_perfil]);
    }

} catch (PDOException $e) {

    die("Error en seguimiento: " . $e->getMessage());

}

/*
| VOLVER AL PERFIL
*/

header("Location: perfil.php?id=" . $id_perfil);

exit;
?>