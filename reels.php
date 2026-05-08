<?php
session_start();
require_once 'config/secrets.php';
$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);

// Función mágica para sacar el ID de YouTube de cualquier enlace que ponga el usuario
function obtenerYoutubeId(string $url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/|youtube\.com\/shorts\/)([^"&?\/\s]{11})/i';
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    return false;
}

// Súper consulta uniendo 3 tablas: clips, usuarios y producciones
$sql = "SELECT c.*, u.nombre AS autor, p.titulo AS peli_titulo 
        FROM clips c 
        JOIN usuarios u ON c.id_usuario = u.id_usuario 
        JOIN producciones p ON c.id_produccion = p.id_produccion
        ORDER BY c.fecha_subida DESC";
$stmt = $pdo->query($sql);
$lista_clips = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reels y Clips | Palomitas</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="contenedor-reels">
        <?php if (count($lista_clips) > 0): ?>
            <?php foreach ($lista_clips as $clip): ?>
                
                <?php 
                // Extraemos el ID del vídeo para poder usar el iframe de YouTube
                $yt_id = obtenerYoutubeId($clip['enlace_video']); 
                if ($yt_id): 
                ?>
                    <div class="reel">
                        <iframe 
                            src="https://www.youtube.com/embed/<?= $yt_id ?>?autoplay=0&mute=0&loop=1&playlist=<?= $yt_id ?>&controls=1&enablejsapi=1" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                        
                        <div class="info-reel">
                            <div class="autor-reel">@<?= htmlspecialchars($clip['autor']) ?></div>
                            <h3><?= htmlspecialchars($clip['titulo_clip']) ?></h3>
                            <p>
                                🍿 <strong>Película:</strong> <?= htmlspecialchars($clip['peli_titulo']) ?><br>
                                <?php if (!empty($clip['categoria'])): ?>
                                    🏷️ <strong>Categoría:</strong> <?= htmlspecialchars($clip['categoria']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>
        <?php else: ?>
            <div style="color: white; text-align: center; margin-top: 50px;">
                <h2>Aún no hay Clips 🎬</h2>
                <p>¡Sé el primero en compartir la mejor escena de tu película favorita!</p>
                <a href="subir_reel.php" class="btn btn-danger">Subir un Clip</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const iframes = document.querySelectorAll('.reel iframe');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) {
                        entry.target.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                    }
                });
            }, { threshold: 0.4 });
            iframes.forEach(iframe => observer.observe(iframe));
        });
    </script>
</body>
</html>