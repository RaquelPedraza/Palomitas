<?php
// 🚀 Descarga 1000 películas en español

// Evitamos que el script se corte si tarda mucho 
set_time_limit(0); 

require_once '../config/secrets.php';

// CONFIGURACIÓN 
$host = '127.0.0.1';
$port = '3307'; // Mi puerto específico
$db   = 'palomitas';
$user = 'raquel';
$pass = 'cine';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<h1>🇪🇸 Cargador Masivo: Cine en Español</h1>";
    
    // Una única sentencia SQL 
    $sql = "INSERT INTO producciones 
            (tmdb_id, titulo, tipo, anio, sinopsis, pais, idioma_original, portada) 
            VALUES 
            (:tmdb_id, :titulo, 'pelicula', :anio, :sinopsis, :pais, :idioma_original, :portada)
            ON DUPLICATE KEY UPDATE 
            titulo = VALUES(titulo), sinopsis = VALUES(sinopsis)";
    $stmt = $pdo->prepare($sql);

    $totalGuardadas = 0;

    // BUCLE PARA 50 PÁGINAS (50 * 20 = 1000 pelis)
    for ($pagina = 1; $pagina <= 50; $pagina++) {
        
        // Usamos 'discover/movie' en lugar de 'popular' para poder filtrar
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
                echo "<h3>📄 Procesando Página $pagina...</h3>";
                
                foreach ($data['results'] as $peli) {
                // ... dentro del foreach ($data['results'] as $peli) ...

                    // 1. EXTRAER DATOS BÁSICOS
                    $tmdb_id = $peli['id'];
                    $titulo  = $peli['title'];
                    $sinopsis = $peli['overview'];
                    $idioma  = $peli['original_language'];
                    $portada = !empty($peli['poster_path']) ? "https://image.tmdb.org/t/p/w500" . $peli['poster_path'] : null;
                    $anio = !empty($peli['release_date']) ? substr($peli['release_date'], 0, 4) : null;

                    // 2. DETECTAR EL PAÍS REAL 
                    // La API nos da un array. Cogemos el primero.
                    $pais_codigo = isset($peli['origin_country'][0]) ? $peli['origin_country'][0] : '??';

                    // 3. EJECUTAR INSERCIÓN
                    $stmt->execute([
                        ':tmdb_id' => $tmdb_id,
                        ':titulo'  => $titulo,
                        ':anio'    => $anio,
                        ':sinopsis'=> $sinopsis,
                        ':pais'    => $pais_codigo, // ¡AQUÍ GUARDAMOS EL PAÍS REAL! (ES, MX, AR...)
                        ':idioma_original' => $idioma,
                        ':portada' => $portada
                    ]);
                    
                    $totalGuardadas++;
                }
                
                // Forzamos que se imprima en pantalla poco a poco
                flush(); 
                ob_flush();
            }
        }

        // 😴 DESCANSITO (Para no enfadar a la API)
        // Dormimos medio segundo entre páginas
        usleep(500000); 
    }

    echo "<hr><h1>🎉 ¡Misión Cumplida!</h1>";
    echo "<h2>Se han procesado $totalGuardadas películas hispanas.</h2>";

} catch (\PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>