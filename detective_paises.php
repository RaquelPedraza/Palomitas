<?php
// 1. Conexión (usa tus datos de siempre)
require_once 'config/secrets.php';
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass);

$stmt = $pdo->query("SELECT id_produccion, titulo, sinopsis FROM producciones 
                     WHERE pais IS NULL 
                     OR TRIM(pais) = '' 
                     OR pais = 'XX' 
                     OR pais = 'Desconocido'");

$peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Diccionario con variaciones (puedes ampliarlo)
$diccionario = [
    'méxico' => 'MX', 'mexic' => 'MX', 'df' => 'MX', 'guadalajara' => 'MX',
    'españa' => 'ES', 'madrid' => 'ES', 'barcelona' => 'ES', 'valencia' => 'ES',
    'argentina' => 'AR', 'buenos aires' => 'AR', 'rosario' => 'AR',
    'colombia' => 'CO', 'bogotá' => 'CO', 'medellín' => 'CO', 'cali' => 'CO',
    'chile' => 'CL', 'santiago' => 'CL', 'valparaíso' => 'CL',
    'perú' => 'PE', 'lima' => 'PE', 'cuzco' => 'PE'
];

echo "<h2>🕵️‍♀️ Detective activado: Analizando " . count($peliculas) . " casos...</h2>";

foreach ($peliculas as $p) {
    // Aseguramos que si la sinopsis es null, sea un string vacío para no dar error
    $titulo = $p['titulo'] ?? '';
    $sinopsis = $p['sinopsis'] ?? '';
    
    // Juntamos y pasamos a minúsculas (Case Insensitive)
    $texto_total = mb_strtolower($titulo . " " . $sinopsis, 'UTF-8');
    
    $pais_encontrado = null;

    foreach ($diccionario as $palabra => $codigo) {
        if (str_contains($texto_total, $palabra)) {
            $pais_encontrado = $codigo;
            break; 
        }
    }

    if ($pais_encontrado) {
        $update = $pdo->prepare("UPDATE producciones SET pais = ? WHERE id_produccion = ?");
        $update->execute([$pais_encontrado, $p['id_produccion']]);
        echo "✨ <b>{$titulo}</b> -> Detectado como <b>$pais_encontrado</b><br>";
    }
}
echo "<h3>✅ Proceso completado.</h3>";
?>
