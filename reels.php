<?php
session_start();
require_once 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reels | Palomitas</title>
    <link rel="stylesheet" href="css/estilos.css?v=6">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <div class="contenedor-reels">

    <div class="reel">
        <iframe src="https://www.youtube.com/embed/Nhb_-DzF550?autoplay=1&mute=1&loop=1&playlist=Nhb_-DzF550&controls=1&enablejsapi=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        <div class="info-reel">
            <h2>🎬 Culpa Mía</h2>
            <p>Cuando el pique se llama deseo.</p>
        </div>
    </div>

    <div class="reel">
        <iframe src="https://www.youtube.com/embed/pgR48h-4JPQ?autoplay=1&mute=1&loop=1&playlist=pgR48h-4JPQ&controls=1&enablejsapi=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        <div class="info-reel">
            <h2>🎬 Culpa Tuya</h2>
            <p>Un regalo irrepetible.</p>
        </div>
    </div>

    
    <div class="reel">
        <iframe src="https://www.youtube.com/embed/1AAIrhdFk0E?autoplay=1&mute=1&loop=1&playlist=1AAIrhdFk0E&controls=1&enablejsapi=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        <div class="info-reel">
            <h2>🎬 Culpa Nuestra</h2>
            <p>Meter la pata después de meterla.</p>
        </div>
    </div>

</div>
<script>
        document.addEventListener("DOMContentLoaded", function() {

        const iframes = document.querySelectorAll('.reel iframe');

      
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    // Si el vídeo sale de la pantalla
                    if (!entry.isIntersecting) {
                        // Le enviamos un mensaje a YouTube para que le dé al Pause
                        entry.target.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                    }
                });
            }, { threshold: 0.4 }); // Se activa cuando el 60% del vídeo desaparece

            iframes.forEach(iframe => observer.observe(iframe));
        });
    </script>
</body>
</body>
</html>