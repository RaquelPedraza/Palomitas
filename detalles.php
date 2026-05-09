<?php
//Iniciar sesión y cargar configuración
session_start();
require_once 'config/secrets.php';
require_once 'includes/functions.php';

// CAPTURAR EL ID DE LA URL
// Si no hay ID, nos devuelve al inicio
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_pelicula = $_GET['id'];

// CONEXIÓN (secrets.php)
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // CONSULTA SEGURA (? evita hackeos)
    $stmt = $pdo->prepare("SELECT * FROM producciones WHERE id_produccion = ?");
    $stmt->execute([$id_pelicula]);
    $peli = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si el ID no existe en la BD
    if (!$peli) {
        die("❌ Película no encontrada.");
    }

    // NOTA DEL USUARIO
    $notaUsuario = null;
    $esFav = false;

    if (isset($_SESSION['usuario_id'])) {
    $stmtUser = $pdo->prepare("
        SELECT puntuacion 
        FROM resenas 
        WHERE id_usuario = ? AND id_produccion = ?
        LIMIT 1
    ");
    $stmtUser->execute([$_SESSION['usuario_id'], $id_pelicula]);
    $datoUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if ($datoUser) {
        $notaUsuario = $datoUser['puntuacion'];
    }

    if (isset($_SESSION['usuario_id']) && isset($peli['id_produccion'])) {
        $sql_fav = "SELECT 1 FROM favoritos WHERE id_usuario = ? AND id_produccion = ?";
        $stmt_fav = $pdo->prepare($sql_fav);
        $stmt_fav->execute([$_SESSION['usuario_id'], $peli['id_produccion']]);
        
        if ($stmt_fav->fetchColumn()) {
            $esFav = true;
        }
    }
    }

    //CALIFICACIÓN
    $stmtMedia = $pdo->prepare("SELECT AVG(puntuacion) as media FROM resenas WHERE id_produccion = ?");
    $stmtMedia->execute([$id_pelicula]);
    $datoMedia = $stmtMedia->fetch(PDO::FETCH_ASSOC);
    $notaMedia = $datoMedia['media'] ?? 0;

    //COMENTARIOS
    $stmtCom = $pdo->prepare("
        SELECT r.*, u.nombre AS nombre_usuario
        FROM resenas r
        INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
        WHERE r.id_produccion = ?
        AND r.contenido IS NOT NULL
        AND r.contenido != ''
        ORDER BY r.fecha DESC
    ");
    $stmtCom->execute([$id_pelicula]);
    $comentarios = $stmtCom->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    die("Error: " . $e->getMessage());
}

$nombres_paises = [
    'ES' => 'España', 'MX' => 'México', 'AR' => 'Argentina',
    'CO' => 'Colombia', 'CL' => 'Chile', 'PE' => 'Perú',
    'VE' => 'Venezuela', 'EC' => 'Ecuador', 'GT' => 'Guatemala',
    'CU' => 'Cuba', 'BO' => 'Bolivia', 'DO' => 'República Dominicana',
    'HN' => 'Honduras', 'PY' => 'Paraguay', 'SV' => 'El Salvador',
    'NI' => 'Nicaragua', 'CR' => 'Costa Rica', 'PR' => 'Puerto Rico',
    'UY' => 'Uruguay', 'PA' => 'Panamá',
    'US' => 'Estados Unidos', 'FR' => 'Francia', 'IT' => 'Italia'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $peli['titulo'] ?> | Palomitas</title>
    <link rel="stylesheet" href= "css/estilos.css"></link>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body>

    <!-- NAVABAR -->
    <?php include 'includes/navbar.php'; ?>

    <a href="index.php" class="boton-volver">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Volver al catálogo
    </a>
    

    <div class="ficha">
        <div class="poster">
            <img src="<?= $peli['portada'] ?>" alt="Póster">
        </div>

        <div class="datos">
            <h1><?= $peli['titulo'] ?></h1>
            
            <div class="meta">
                <?php if (!empty($peli['pais'])): ?>
                    <?php 
                        $pais_codigo = $peli['pais'];
                        $pais_nombre = isset($nombres_paises[$pais_codigo]) ? $nombres_paises[$pais_codigo] : $pais_codigo;
                    ?>
                    <div class="contenedor-tooltip">
                        <span class="etiqueta" style="background-color: #e50914; color: white;">
                            <?= htmlspecialchars($pais_codigo) ?>
                        </span>
                        <span class="tooltip-personalizado"><?= htmlspecialchars($pais_nombre) ?></span>
                    </div>
            <?php endif; ?>

                <span class="etiqueta"><?= $peli['anio'] ?></span>
                <span class="etiqueta"><?= strtoupper($peli['idioma_original']) ?></span>
                <span class="etiqueta"><?= $peli['anio'] ?></span>
                <span class="etiqueta">PELÍCULA</span>
            </div>

            <div class="nota-media-container">
                <span class="estrellas-media">
                    <i class="fas fa-star"></i>
                </span>
                <b class="numero-nota">
                    <?= number_format($notaMedia, 1) ?>
                </b>
            </div>

            <h3>Sinopsis</h3>
            <p class="sinopsis">
                <?= $peli['sinopsis'] ?>
            </p>
            
            <!-- ACCIONEES DE USUARIO -->            
            <!-- CALIFICACIONES Y COMENTARIOS -->
            <div class="seccion-interactiva">
                <?php if (isset($_SESSION['usuario_nombre'])): ?>
                    <div class="contenedor-formulario-resena"> 
                        <div class="acciones-usuario">
                            <!-- RATING -->
                             <div class="iconos-accion btn-rating" onclick="abrirModal('modalRating')">
                                <i class="fas fa-star"></i>

                                <span class="texto-rating">
                                    <?php if ($notaUsuario !== null): ?>
                                        <?= number_format($notaUsuario, 1) ?>
                                    <?php else: ?>
                                        Valorar
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <!-- COMENTARIO -->
                             <div class="iconos-accion btn-comentario" onclick="abrirModal('modalComentario')">
                                <i class="fas fa-comment"></i>
                                <span>Escribir reseña</span>
                            </div>

                            <!-- FAVORITO Y LISTAS -->
                            <button class="iconos-accion corazon-detalles" onclick="toggleFavorito(this, <?= $peli['id_produccion'] ?>)">
                                <i class="<?= $esFav ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                                <span>Añadir a favoritos</span>
                            </button>

                            <button class="iconos-accion btn-listas" onclick="toggleListas(<?= $id_pelicula ?>)">
                                <i class="fas fa-plus"></i>
                                <span>Añadir a lista</span>
                            </button>

                        </div>
                    </div>               
                <?php endif; ?>

                
                <div class="favorito-listas">
                    
                    
                </div>

                <div id="listasDropdown" class="dropdown-listas" style="display:none;"></div>

                <!-- MODALES -->
                <div id="modalRating" class="modal">
                    <div class="modal-contenido">
                        <span class="cerrar" onclick="cerrarModal('modalRating')">&times;</span>
                        <h2>Tu valoración</h2>
                        <form action="guardar_resena.php" method="POST">
                            <input type="hidden" name="pelicula_id" value="<?= $id_pelicula ?>">
                            <div class="rating">
                                <?php for ($i = 10; $i >= 1; $i--): ?>
                                    <input 
                                        type="radio" 
                                        id="star<?= $i ?>" 
                                        name="puntuacion" 
                                        value="<?= $i ?>"
                                        <?= ($notaUsuario == $i) ? 'checked' : '' ?>
                                        required
                                    >
                                    <label for="star<?= $i ?>"><i class="fas fa-star"></i></label>
                                <?php endfor; ?>
                            </div>

                            <button type="submit" class="btn-rojo">Guardar</button>
                        </form>
                    </div>
                </div>

                <div id="modalComentario" class="modal">
                    <div class="modal-contenido">
                        <span class="cerrar" onclick="cerrarModal('modalComentario')">&times;</span>
                        <h2>Tu reseña</h2>
                        <form action="guardar_resena.php" method="POST">
                            <input type="hidden" name="pelicula_id" value="<?= $id_pelicula ?>">
                            <!-- RATING DE TU RESEÑA -->
                            <div class="rating">
                                <?php for ($i = 10; $i >= 1; $i--): ?>
                                    <input 
                                        type="radio" 
                                        id="coment-star<?= $i ?>" 
                                        name="puntuacion" 
                                        value="<?= $i ?>" 
                                        required
                                        <?= ($notaUsuario == $i) ? 'checked' : '' ?>
                                    >
                                    <label for="coment-star<?= $i ?>"><i class="fas fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                            <!-- TITULO Y TEXTO DE LA RESEÑA -->
                            <input 
                                type="text" 
                                name="titulo_resena" 
                                placeholder="Título de tu reseña..." 
                                maxlength="200"
                                required
                                class="titulo-resena"
                            >

                            <textarea name="texto" rows="4" placeholder="Escribe tu reseña..." required class="textarea-general"></textarea>

                            <button type="submit" class="btn-rojo">Publicar</button>
                        </form>
                    </div>
                </div>
                
                <!-- LISTA DE COMENTARIOS -->
                <div class="comentarios-lista" style="margin-top: 20px;">
                    <?php if (!empty($comentarios)): ?>
                        <?php foreach ($comentarios as $coment): ?>
                            <div class="caja-comentario" id="resena-<?= $coment['id_resena'] ?>">
                                <div class="comentario-header">
                                    <strong class="usuario"><?= htmlspecialchars($coment['nombre_usuario']) ?></strong>
                                    <span class="estrellas-comentario">
                                        <?= mostrarEstrellas($coment['puntuacion']) ?>
                                        <span class="numero"><?= $coment['puntuacion'] ?>/10</span>
                                    </span>
                                </div>
                                <strong class="titulo-comentario"><?= htmlspecialchars($coment['titulo_resena']) ?></strong>
                                <p><?= nl2br(htmlspecialchars($coment['contenido'])) ?></p>
                                <p class="fecha-comentario"><?= date('d-m-Y', strtotime($coment['fecha'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?> 
                        <p class="nada"> Aún no hay reseñas para esta película. ¡Sé el primero en opinar! </p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
    <div id="toast" class="toast"></div>
    <script src="js/modales.js"></script>
    <script src="js/listas.js"></script>
    <script src="js/favoritos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>