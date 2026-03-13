<?php
// scripts/cargar_masiva.php

set_time_limit(0); // Tiempo ilimitado 
require_once '../config/secrets.php';

// --- CONFIGURACIÓN ---
$host = '127.0.0.1';
$db   = 'palomitas';
$user = 'raquel';
$pass = 'cine';
$port = '3307';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ];

// --- FUNCIÓN AUXILIAR: Pide los detalles de UNA película para sacar el país ---
function obtenerPaisReal($tmdb_id) {
    // Llamada individual a la ficha de la película
    $url = "https://api.themoviedb.org/3/movie/$tmdb_id?api_key=" . TMDB_API_KEY . "&language=es-ES";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    
    if ($res) {
        $data = json_decode($res, true);
        // Buscamos dentro de "production_countries" el código ISO (ES, MX, etc)
        if (!empty($data['production_countries'])) {
            return $data['production_countries'][0]['iso_3166_1']; 
        }
    }
    return '??'; // Si falla, ponemos interrogantes
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "<h1>🌍 Cargador V3: Buscando países...</h1>";
    
    // Preparamos la sentencia SQL
    $sql = "INSERT INTO producciones (tmdb_id, titulo, tipo, anio, sinopsis, pais, idioma_original, portada) 
            VALUES (:tmdb_id, :titulo, 'pelicula', :anio, :sinopsis, :pais, :idioma_original, :portada)
            ON DUPLICATE KEY UPDATE 
            pais = VALUES(pais)"; // Si ya existe, actualizamos solo el país
            
    $stmt = $pdo->prepare($sql);
    
    $total = 0;

    // Bucle de 50 páginas (1000 pelis)
    for ($pagina = 1; $pagina <= 50; $pagina++) {
        
        $apiUrl = "https://api.themoviedb.org/3/discover/movie?api_key=" . TMDB_API_KEY . "&language=es-ES&with_original_language=es&sort_by=popularity.desc&page=$pagina";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $data = json_decode($response, true);
            if (!empty($data['results'])) {
                echo "<strong>Procesando Página $pagina...</strong> ";
                
                foreach ($data['results'] as $peli) {
                    // Datos básicos
                    $idStr   = $peli['id'];
                    $titulo  = $peli['title'];
                    $sinopsis= $peli['overview'];
                    $idioma  = $peli['original_language'];
                    $portada = !empty($peli['poster_path']) ? "https://image.tmdb.org/t/p/w500" . $peli['poster_path'] : null;
                    $anio    = !empty($peli['release_date']) ? substr($peli['release_date'], 0, 4) : null;
                    
                    // Pedimos el país individualmente a la función de arriba
                    $pais = obtenerPaisReal($idStr);

                    $stmt->execute([
                        ':tmdb_id' => $idStr,
                        ':titulo'  => $titulo,
                        ':anio'    => $anio,
                        ':sinopsis'=> $sinopsis,
                        ':pais'    => $pais,  
                        ':idioma_original' => $idioma,
                        ':portada' => $portada
                    ]);
                    $total++;
                }
                echo "✅ ($total pelis acumuladas)<br>";
                flush(); ob_flush(); // Forzar mostrar texto en pantalla
            }
        }
        // Descanso
        usleep(100000); 
    }

    echo "<h1>🎉 ¡TERMINADO! $total películas actualizadas con su país.</h1>";
    echo "<a href='../index.php'>Volver al Catálogo</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>