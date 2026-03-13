<?php
// Ficha técnica de la película

// CAPTURAR EL ID DE LA URL
// Si no hay ID, nos devuelve al inicio
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id_pelicula = $_GET['id'];

// CONEXIÓN 
$host = '127.0.0.1';
$db   = 'palomitas';
$user = 'raquel';
$pass = 'cine';
$port = '3307'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    
    // CONSULTA SEGURA (? evita hackeos)
    $stmt = $pdo->prepare("SELECT * FROM producciones WHERE id_produccion = ?");
    $stmt->execute([$id_pelicula]);
    $peli = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si el ID no existe en la BD
    if (!$peli) {
        die("❌ Película no encontrada.");
    }

} catch (\PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $peli['titulo'] ?> | Palomitas</title>
    <link rel="stylesheet" href= "css/estilos.css"></link>
</head>
<body>

    <a href="index.php" class="boton-volver">⬅ Volver al catálogo</a>

    <div class="ficha">
        <div class="poster">
            <img src="<?= $peli['portada'] ?>" alt="Póster">
        </div>

        <div class="datos">
            <h1><?= $peli['titulo'] ?></h1>
            
            <div class="meta">
               <div class="meta">
                <?php if (!empty($peli['pais'])): ?>
                    <span class="etiqueta" style="background-color: #e50914; color: white;">
                        <?= $peli['pais'] ?>
                    </span>
                <?php endif; ?>

                <span class="etiqueta"><?= $peli['anio'] ?></span>
                <span class="etiqueta"><?= strtoupper($peli['idioma_original']) ?></span>
                <span class="etiqueta">PELÍCULA</span>
            </div>
            </div>

            <h3>Sinopsis</h3>
            <p class="sinopsis">
                <?= $peli['sinopsis'] ?>
            </p>
        </div>
    </div>

</body>
</html>