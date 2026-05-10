<?php
session_start();

require_once 'config/secrets.php';

$pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
    $user,
    $pass
);

$id_usuario = $_SESSION['usuario_id'] ?? null;
$id_lista = $_GET['id'] ?? null;

if (!$id_lista) {
    die("Lista no encontrada");
}

/* OBTENER LISTA */
$stmt = $pdo->prepare("
    SELECT *
    FROM listas
    WHERE id_lista = ?
");

$stmt->execute([$id_lista]);

$lista = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lista) {
    die("La lista no existe");
}

if (
    $lista['visibilidad'] === 'privada'
    &&
    $lista['id_usuario'] != $id_usuario
) {

    die("No tienes permiso para ver esta lista");

}

/* PELÍCULAS DE LA LISTA */
$stmt = $pdo->prepare("
    SELECT p.*

    FROM producciones p

    INNER JOIN lista_produccion lp
        ON p.id_produccion = lp.id_produccion

    WHERE lp.id_lista = ?
");

$stmt->execute([$id_lista]);

$peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>
            <?= htmlspecialchars($lista['nombre_lista']) ?>
        </title>
        <link rel="stylesheet" href="css/estilos.css">
        <link rel="stylesheet" href="css/lista.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <!-- AJAX -->
        <script src="js/listas.js" defer></script>
    </head>
    <body>
        <!-- NAVBAR -->
        <?php include 'includes/navbar.php'; ?>

        <div class="lista-header">
            <div class="lista-header-overlay">
                <div class="lista-header-info">

                    <!-- NOMBRE DE LA LISTA Y LA VISIBILIDAD -->
                    <h2 class="titulo-seccion">
                        <?php if ($lista['visibilidad'] === 'publica'): ?>
                            <i class="fa-solid fa-earth-americas"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-lock"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($lista['nombre_lista']) ?>
                    </h2>

                    <!-- DESCRIPCIÓN -->
                    <?php if (!empty($lista['descripcion'])): ?>
                        <p class="lista-descripcion">
                            <?= htmlspecialchars($lista['descripcion']) ?>
                        </p>
                    <?php endif; ?>

                    <!-- TOTAL DE PELÍCULAS -->
                    <span class="lista-total">
                        <?= count($peliculas) ?> películas
                    </span>

                    <!-- EDICIÓN DE LA LISTA -->
                    <?php if ($lista['id_usuario'] == $id_usuario): ?>
                        <div class="lista-owner-actions">
                            <!-- Editar -->
                            <button
                                onclick="editarLista(
                                    <?= $lista['id_lista'] ?>,
                                    '<?= htmlspecialchars(addslashes($lista['nombre_lista'])) ?>',
                                    '<?= htmlspecialchars(addslashes($lista['descripcion'])) ?>',
                                    '<?= $lista['visibilidad'] ?>'
                                )"
                            >
                                <i class="fa-solid fa-pen"></i>
                                Editar
                            </button>

                            <!-- Eliminar -->
                            <button onclick="eliminarLista(<?= $lista['id_lista'] ?>)">
                                <i class="fa-solid fa-trash"></i>
                                Eliminar
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- GALERIA DE PELÍCULAS -->
        <div class="galeria">
            <?php foreach ($peliculas as $peli): ?>
                <div class="tarjeta">
                    <a href="detalles.php?id=<?= $peli['id_produccion'] ?>&from=ver_lista.php?id=<?= $id_lista ?>" style="text-decoration:none; color:inherit;">
                        <img
                            src="<?= htmlspecialchars($peli['portada'] ?: 'img/no-poster.png') ?>"
                            alt="<?= htmlspecialchars($peli['titulo']) ?>"
                        >
                        <div class="info">
                            <div class="titulo">
                                <?= htmlspecialchars($peli['titulo']) ?>
                            </div>
                            <div class="meta-datos">
                                <span class="anio">
                                    <?= $peli['anio'] ?>
                                </span>
                                <?php if (
                                    !empty($peli['pais'])
                                    &&
                                    $peli['pais'] !== '??'
                                ): ?>

                                    <span class="etiqueta-pais">
                                        <?= $peli['pais'] ?>
                                    </span>

                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- LISTA VACÍA -->
        <?php if (count($peliculas) === 0): ?>
            <p class="nada">
                Esta lista aún no tiene películas.
            </p>
        <?php endif; ?>
    </body>
</html>
