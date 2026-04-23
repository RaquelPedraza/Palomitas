<?php
// index.php - Catálogo con Paginación
session_start();

// FUNCIÓN MOSTRAR ESTRELLAS
require_once 'includes/functions.php';

// 1. CONFIGURACIÓN
$host = '127.0.0.1';
$db   = 'palomitas';
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
$pelis_por_pagina = 15; // Mostramos 15

// ¿En qué página estamos? (Si no hay número, es la 1)
$pagina_actual = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;

// ¿Desde qué película empezamos a contar? (OFFSET)
// Pag 1: empieza en 0. Pag 2: empieza en 12...
$inicio = ($pagina_actual - 1) * $pelis_por_pagina;

// 3. CONSULTA SQL INTELIGENTE
// LIMIT: Cuantas traigo. OFFSET: Cuántas me salto.
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

// 5. MOSTRAR EL TOP 10
$sql_top = "
SELECT 
    p.id_produccion,
    p.titulo,
    p.portada,
    AVG(r.puntuacion) AS media
FROM producciones p
JOIN resenas r ON p.id_produccion = r.id_produccion
GROUP BY p.id_produccion
HAVING media IS NOT NULL
ORDER BY media DESC
LIMIT 10
";

$top_pelis = $pdo->query($sql_top)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Palomitas | Pág <?= $pagina_actual ?></title>
    <link rel="stylesheet" href= "css/estilos.css?v=2"></link>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">🍿 Palomitas</a>
        <div class="enlaces">
            <a href="index.php">Catálogo</a>
            
            <?php if (isset($_SESSION['usuario_nombre'])): ?>
                <span style="color: #ccc; margin-left: 20px;">
                    Hola, <strong style="color: white;"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>
                </span>
                <a href="logout.php" style="color: #e50914; margin-left: 15px;">Cerrar Sesión</a>
            <?php else: ?>
                <a href="login.php">Iniciar Sesión</a>
                <a href="registro.php">Crear Cuenta</a>
            <?php endif; ?>
        </div>
    </nav>
    <h1>🍿 Palomitas - Catálogo Hispano</h1>

    <!-- TOP 10 -->
    <div class="top10">
        <?php foreach ($top_pelis as $peli): ?>
            <div class="top-card">
                <img src="<?= $peli['portada'] ?>" alt="<?= $peli['titulo'] ?>">
                <h3><?= $peli['titulo'] ?></h3>

                <div class="estrellas">
                    <?= mostrarEstrellas($peli['media']) ?>
                </div>

                <span class="nota">
                    <?= number_format($peli['media'], 1) ?>/5
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- CATÁLOGO -->
    <div class="galeria">

        <?php foreach ($peliculas as $peli): ?>
            <a href="detalles.php?id=<?= $peli['id_produccion'] ?>" style="text-decoration: none; color: inherit;">
                <div class="tarjeta">
                    <img src="<?= $peli['portada'] ?>" alt="<?= $peli['titulo'] ?>">
                    <div class="info">
                        <div class="titulo"><?= $peli['titulo'] ?></div>
                        <div class="meta-datos">
                            <span class="anio"><?= $peli['anio'] ?></span>
                            <?php if (!empty($peli['pais']) && $peli['pais'] !== '??'): ?>
                                <span class="etiqueta-pais"><?= $peli['pais'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </a>

        <?php endforeach; ?>

    </div>

    <!-- PAGINACIÓN -->
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