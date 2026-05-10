<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/secrets.php';

$pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
    $user,
    $pass
);

$id_usuario = $_SESSION['usuario_id'];

//LISTA DE PELÍCULAS FAVORITAS
$stmt = $pdo->prepare("
    SELECT p.*
    FROM producciones p
    INNER JOIN favoritos f 
        ON p.id_produccion = f.id_produccion
    WHERE f.id_usuario = ?
    ORDER BY f.id_favorito DESC
");

$stmt->execute([$id_usuario]);
$favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

//LISTAS DEL USUARIO
$stmt = $pdo->prepare("
    SELECT *
    FROM listas
    WHERE id_usuario = ?
    ORDER BY id_lista DESC
");

$stmt->execute([$id_usuario]);
$listas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Listas</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AJAX -->
    <script src="js/favoritos.js" defer></script>
    <script src="js/listas.js" defer></script>
</head>

<body>
    <!-- NAVABAR -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- FAVORITOS -->
    <h2 class="titulo-seccion">Favoritos</h2>

    <?php if (count($favoritos) === 0): ?>
        <p class="nada">
            Aún no has añadido nada a favoritos.
        </p>
    <?php endif; ?>

    <?php if (count($favoritos) > 0): ?>
        <div class="carrusel-wrapper">
            <button class="flecha izquierda" onclick="scrollCarrusel('carrusel', -300)">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <div class="carrusel" id="carrusel">
                <?php foreach ($favoritos as $peli): ?>
                    <div class="tarjeta">
                        <a
                            href="detalles.php?id=<?= $peli['id_produccion'] ?>&from=mis_listas.php"
                            style="text-decoration:none; color:inherit;"
                        >
                            <img
                                src="<?= htmlspecialchars($peli['portada'] ?: 'img/no-poster.png') ?>"
                                alt="<?= htmlspecialchars($peli['titulo']) ?>"
                            >
                            <div class="info">
                                <div class="titulo">
                                    <?= $peli['titulo'] ?>
                                </div>
                                <div class="meta-datos">
                                    <span class="anio">
                                        <?= $peli['anio'] ?>
                                    </span>
                                    <?php if (!empty($peli['pais']) && $peli['pais'] !== '??'): ?>
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

            <button class="flecha derecha" onclick="scrollCarrusel('carrusel', 300)">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- LISTAS -->
    <h2 class="titulo-seccion">Mis listas</h2>

    <div class="listas">
        <!-- Crear listas -->
        <div class="lista-card crear-lista-btn" onclick="abrirModal('modalCrearListas')">
            <div class="lista-media plus">
                <i class="fa-solid fa-plus"></i>
            </div>
            <div class="lista-info">
                <div class="lista-titulo">Crear lista</div>
            </div>
        </div>

        <div id="modalCrearListas" class="modal">
            <div class="modal-contenido">
                <span class="cerrar" onclick="cerrarModal('modalCrearListas')">&times;</span>
                <h2 class="titulo-modal">Crear nueva lista</h2>
                <form id="formCrearLista">
                        <input type="text" name="nombre_lista" placeholder="Nombre de la lista" required class="input-general">
                        <textarea name="descripcion" rows="4" placeholder="Descripción" class="textarea-general"></textarea>
                        <div class="visibilidad">
                            <input type="radio" name="visibilidad" value="publica" checked id="publica">
                            <label for="publica" class="opcion-visibilidad">
                                <i class="fa-solid fa-earth-americas"></i>
                                Pública
                            </label>
                            <input type="radio" name="visibilidad" value="privada" id="privada">
                            <label for="privada" class="opcion-visibilidad">
                                <i class="fa-solid fa-lock"></i>
                                Privada
                            </label>
                        </div>
                        <button type="submit" class="btn-rojo">Crear lista</button>                                  
                </form>
            </div> 
        </div>

        <?php foreach ($listas as $lista): ?>
            <!-- Consulta para obtener las portadas de las primeras 4 producciones de cada lista -->
            
            <?php
                $stmt = $pdo->prepare("
                    SELECT p.portada
                    FROM lista_produccion lp
                    INNER JOIN producciones p 
                        ON p.id_produccion = lp.id_produccion
                    WHERE lp.id_lista = ?
                    LIMIT 4
                ");
                $stmt->execute([$lista['id_lista']]);
                $pelis = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div class="lista-card" data-lista-id="<?= $lista['id_lista'] ?>">
                 <a href="ver_lista.php?id=<?= $lista['id_lista'] ?>">
                    <div class="lista-media">
                        <?php foreach ($pelis as $peli): ?>
                            <img src="<?= $peli['portada'] ?? 'img/no-poster.png' ?>" alt="Portada de la película">
                        <?php endforeach; ?>
                    </div>
                    <div class="lista-info">
                        <div class="lista-titulo"><?= htmlspecialchars($lista['nombre_lista']) ?></div>
                        <?php if ($lista['visibilidad'] === 'publica'): ?>
                            <i class="fa-solid fa-earth-americas"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-lock"></i>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>

    </div>
    <script src="js/modales.js"></script>          
    <script>
        function scrollCarrusel(id, valor) {
            const carrusel = document.getElementById(id);
            if (!carrusel) return;
            carrusel.scrollBy({
                left: valor,
                behavior: 'smooth'
            });
        }
    </script>

    <?php include 'includes/footer.php'; ?>

</body>
</html>
