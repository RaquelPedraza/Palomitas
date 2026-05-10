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
                            href="detalles.php?id=<?= $peli['id_produccion'] ?>"
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
                <h2>Crear nueva lista</h2>
                <form id="formCrearLista">
                        <input type="text" name="nombre_lista" placeholder="Nombre de la lista" required>
                        <textarea name="descripcion" rows="4" placeholder="Descripción" class="textarea-general"></textarea>
                        <input type="radio" name="visibilidad" value="publica" checked>
                        <input type="radio" name="visibilidad" value="privada">
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

            <div class="lista-card">
                 <a href="ver_lista.php?id=<?= $lista['id_lista'] ?>">
                   <div class="lista-media">
                        <?php if (empty($pelis)): ?>
                            <?php 
                                $titulo = mb_strtolower($lista['nombre_lista'] ?? '', 'UTF-8');
                                $desc = mb_strtolower($lista['descripcion'] ?? '', 'UTF-8');
                                $texto_busqueda = $titulo . ' ' . $desc;

                                $poster_vacio = match(true) {
                                    (strpos($texto_busqueda, 'argentin') !== false) => 'img/paises/default-argentina.png',
                                    (strpos($texto_busqueda, 'chile') !== false) => 'img/paises/default-chile.png',
                                    (strpos($texto_busqueda, 'colombia') !== false) => 'img/paises/default-colombia.png',
                                    (strpos($texto_busqueda, 'cuba') !== false) => 'img/paises/default-cuba.png', 
                                    (strpos($texto_busqueda, 'dominic') !== false) => 'img/paises/default-dominicana.png', 
                                    (strpos($texto_busqueda, 'ecua') !== false) => 'img/paises/default-ecuador.png', 
                                    (strpos($texto_busqueda, 'españ') !== false)    => 'img/paises/default-espana.png',
                                    (strpos($texto_busqueda, 'franc') !== false)    => 'img/paises/default-francia.png',
                                    (strpos($texto_busqueda, 'itali') !== false)   => 'img/paises/default-italia.png',
                                    (strpos($texto_busqueda, 'mexic') !== false)    => 'img/paises/default-mexico.png',
                                    (strpos($texto_busqueda, 'peru') !== false)    => 'img/paises/default-peru.png',
                                    (strpos($texto_busqueda, 'puert') !== false)    => 'img/paises/default-ptoRico.png',
                                    (strpos($texto_busqueda, 'estad') !== false)    => 'img/paises/default-usa.png',
                                    (strpos($texto_busqueda, 'venez') !== false)    => 'img/paises/default-venezuela.png',
                                    
                                    (strpos($texto_busqueda, 'ac') !== false)   => 'img/generos/default-accion.png',
                                    (strpos($texto_busqueda, 'crime') !== false)   => 'img/generos/default-crimen.png',
                                    (strpos($texto_busqueda, 'docum') !== false)   => 'img/generos/default-documental.png',
                                    (strpos($texto_busqueda, 'dram') !== false)   => 'img/generos/default-drama.png',
                                    (strpos($texto_busqueda, 'isto') !== false)   => 'img/generos/default-historico.png',
                                    (strpos($texto_busqueda, 'musi') !== false)   => 'img/generos/default-musical.png',
                                    (strpos($texto_busqueda, 'roman') !== false)   => 'img/generos/default-romance.png',
                                    (strpos($texto_busqueda, 'cie') !== false)   => 'img/generos/default-scifi.png',
                                    (strpos($texto_busqueda, 'terror') !== false)   => 'img/generos/default-terror.png',
                                    (strpos($texto_busqueda, 'ril') !== false)   => 'img/generos/default-thriller.png',
                                    (strpos($texto_busqueda, 'este') !== false)   => 'img/generos/default-western.png',

                                    (strpos($texto_busqueda, 'feli') !== false)   => 'img/moods/default-felicidad.png',
                                    (strpos($texto_busqueda, 'inspir') !== false)   => 'img/moods/default-inspiracion.png',
                                    (strpos($texto_busqueda, 'trist') !== false)   => 'img/moods/default-melancolia.png',
                                    (strpos($texto_busqueda, 'llorar') !== false)   => 'img/moods/default-nostalgia.png',
                                    (strpos($texto_busqueda, 'pens') !== false)   => 'img/moods/default-reflexion.png',
                                    default => 'img/no-poster.png'
                                };
                            ?>
                            <img src="<?= $poster_vacio ?>" alt="Colección vacía">

                        <?php else: ?>
                            <?php foreach ($pelis as $peli): ?>
                                <img src="<?= htmlspecialchars($peli['portada'] ?? 'img/no-poster.png') ?>" alt="Portada de la película">
                            <?php endforeach; ?>
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
