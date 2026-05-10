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
                            <button onclick="abrirModalEditarLista(
                                <?= $lista['id_lista'] ?>,
                                '<?= htmlspecialchars(addslashes($lista['nombre_lista'])) ?>',
                                '<?= htmlspecialchars(addslashes($lista['descripcion'])) ?>',
                                '<?= $lista['visibilidad'] ?>'
                            )">
                                <i class="fa-solid fa-pen"></i>
                                Editar
                            </button>

                            <!-- Eliminar -->
                            <button onclick="abrirModalEliminarLista(<?= $lista['id_lista'] ?>)">
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
                <div class="tarjeta pelicula" data-id="<?= $peli['id_produccion'] ?>">
                    <?php if ($lista['id_usuario'] == $id_usuario): ?>
                        <button class="btn-eliminar-pelicula"
                            onclick="eliminarPeliculaLista(<?= $peli['id_produccion'] ?>)">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    <?php endif; ?>

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

        <!-- MODALES -->
         <div id="modalEditarLista" class="modal">
            <div class="modal-contenido">
                <span class="cerrar" onclick="cerrarModal('modalEditarLista')">&times;</span>

                <h2 class="titulo-modal">Editar lista</h2>

                <form id="formEditarLista">
                    <input type="hidden" name="id_lista" id="editar_id_lista">
                    <input type="text" name="nombre_lista" id="editar_nombre" class="input-general" required>

                    <textarea name="descripcion" id="editar_descripcion" class="textarea-general"></textarea>

                    <div class="visibilidad">
                        <input type="radio" name="visibilidad" value="publica" id="edit_publica">
                        <label for="edit_publica" class="opcion-visibilidad">
                            <i class="fa-solid fa-earth-americas"></i>
                            Pública
                        </label>

                        <input type="radio" name="visibilidad" value="privada" id="edit_privada">
                        <label for="edit_privada" class="opcion-visibilidad">
                            <i class="fa-solid fa-lock"></i>
                            Privada
                        </label>
                    </div>

                    <button type="submit" class="btn-rojo">Guardar cambios</button>
                </form>
            </div>
        </div>
        <div id="modalEliminarLista" class="modal">
            <div class="modal-contenido">
                <h2 class="titulo-modal">¿Desea eliminar la lista?</h2>
                <p style="color: #888; text-align: center;">Se borrará la lista y todo su contenido.</p>
                <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">
                    <button id="confirmarEliminarLista" class="btn-rojo btnlistas">Eliminar</button>
                    <button onclick="cerrarModal('modalEliminarLista')" class="btn-secundario">Cancelar</button>
                </div>
            </div>
        </div>

        <script src="js/modales.js"></script>
        <?php include 'includes/footer.php'; ?>
    </body>
</html>
