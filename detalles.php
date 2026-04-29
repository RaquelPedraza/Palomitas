<?php
//Iniciar sesión y cargar configuración
session_start();
require_once 'config/secrets.php';
require_once 'includes/functions.php';

// Ficha técnica de la película

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

    <a href="javascript:history.back()" class="boton-volver">
    ⬅ Volver al catálogo </a>

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
                    <?= mostrarEstrellas($notaMedia) ?>
                </span>
                <b class="numero-nota">
                    <?= number_format($notaMedia, 1) ?>
                </b>
            </div>

            <h2>Sinopsis</h2>
            <p class="sinopsis">
                <?= $peli['sinopsis'] ?>
            </p>
            
                        
            <!-- CALIFICACIONES Y COMENTARIOS -->
            <div class="seccion-interactiva">
                <?php if (isset($_SESSION['usuario_nombre'])): ?>
                    <div class="contenedor-formulario-resena">
                        
                        <h2>Tu valoración</h2>

                        <!-- Puntuación/Calificación -->
                        <form action="guardar_resena.php" method="POST">
                            <input type="hidden" name="pelicula_id" value="<?= $id_pelicula ?>">
                            <div class="rating">
                                <input type="radio" id="star5" name="puntuacion" value="5" required><label for="star5"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star4" name="puntuacion" value="4"><label for="star4"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star3" name="puntuacion" value="3"><label for="star3"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star2" name="puntuacion" value="2"><label for="star2"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star1" name="puntuacion" value="1"><label for="star1"><i class="fas fa-star"></i></label>
                            </div>
                            <button type="submit" class="btn-rojo">Calificar</button>
                        </form>
                        
                        <hr class="separador-resena">

                        <!-- Comentario -->
                        <form action="guardar_resena.php" method="POST">
                            <input type="hidden" name="pelicula_id" value="<?= $id_pelicula ?>">
                            <div class="grupo-input">
                                <textarea name="texto" rows="3" placeholder="Escribe tu reseña..." required class="textarea-resena"></textarea>
                            </div>
                            <button type="submit" class="btn-rojo">Publicar</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="comentarios-lista" style="margin-top: 20px;">
                    <?php if (!empty($comentarios)): ?>
                        <?php foreach ($comentarios as $coment): ?>
                            <div class="caja-comentario">
                                <strong><?= htmlspecialchars($coment['id_usuario']) ?></strong>
                                <p><?= nl2br(htmlspecialchars($coment['contenido'])) ?></p>
                                <p class="fecha-comentario"><?= $coment['fecha'] ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?> 
                        <p class="sin-resenas"> Aún no hay reseñas para esta película. ¡Sé el primero en opinar! </p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

</body>
</html>