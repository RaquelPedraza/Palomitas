<?php
// index.php - Catálogo con Paginación

// 1. CONFIGURACIÓN
$host = '127.0.0.1';
$db   = 'palomitas_db';
$user = 'root';
$pass = '';
$port = '3306';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
} catch (\PDOException $e) {
    die("Error: " . $e->getMessage());
}

// 2. LÓGICA DE PAGINACIÓN
$pelis_por_pagina = 15; // Mostramos 12 para que quede bonito en rejilla (3x4 o 4x3)

// ¿En qué página estamos? (Si no hay numero, es la 1)
$pagina_actual = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;

// ¿Desde qué película empezamos a contar? (OFFSET)
// Pag 1: empieza en 0. Pag 2: empieza en 12...
$inicio = ($pagina_actual - 1) * $pelis_por_pagina;

// 3. CONSULTA SQL INTELIGENTE
// LIMIT: Cuantas traigo. OFFSET: Cuantas me salto.
$sql = "SELECT * FROM producciones LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $pelis_por_pagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $inicio, PDO::PARAM_INT);
$stmt->execute();
$peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. CALCULAR TOTAL DE PÁGINAS (Para saber cuándo parar el botón "Siguiente")
$sql_total = "SELECT COUNT(*) FROM producciones";
$total_pelis = $pdo->query($sql_total)->fetchColumn();
$total_paginas = ceil($total_pelis / $pelis_por_pagina);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Palomitas | Pág <?= $pagina_actual ?></title>
    <link rel="stylesheet" href= "css/estilos.css"></link>
</head>
<body>

    <h1>🍿 Palomitas - Catálogo Hispano</h1>

    <div class="galeria">
        <?php foreach ($peliculas as $peli): ?>
            <a href="detalles.php?id=<?= $peli['id_produccion'] ?>" style="text-decoration: none; color: inherit;">
                <div class="tarjeta">
                    <img src="<?= $peli['portada'] ?>" alt="<?= $peli['titulo'] ?>">
                    <div class="info">
                        <div class="titulo"><?= $peli['titulo'] ?></div>
                        <div class="anio"><?= $peli['anio'] ?></div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="paginacion">
        <?php if ($pagina_actual > 1): ?>
            <a href="?pag=<?= $pagina_actual - 1 ?>" class="btn">⬅ Anterior</a>
        <?php else: ?>
            <span class="btn desactivado">⬅ Anterior</span>
        <?php endif; ?>

        <span class="info-pag">Página <?= $pagina_actual ?> de <?= $total_paginas ?></span>

        <?php if ($pagina_actual < $total_paginas): ?>
            <a href="?pag=<?= $pagina_actual + 1 ?>" class="btn">Siguiente ➡</a>
        <?php else: ?>
            <span class="btn desactivado">Siguiente ➡</span>
        <?php endif; ?>
    </div>

</body>
</html>