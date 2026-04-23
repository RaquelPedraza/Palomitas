<?php
// index.php - Catálogo con Paginación
session_start();

// 1. CONFIGURACIÓN
$host = '127.0.0.1';
$db   = 'palomitas';
$user = 'raquel';
$pass = 'cine';
$port = '3307'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
} catch (\PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Diccionario de traducción de códigos ISO a nombres reales
$nombres_paises = [
    'ES' => 'España',
    'MX' => 'México',
    'AR' => 'Argentina',
    'CO' => 'Colombia',
    'CL' => 'Chile',
    'PE' => 'Perú',
    'VE' => 'Venezuela',
    'EC' => 'Ecuador',
    'GT' => 'Guatemala',
    'CU' => 'Cuba',
    'BO' => 'Bolivia',
    'DO' => 'República Dominicana',
    'HN' => 'Honduras',
    'PY' => 'Paraguay',
    'SV' => 'El Salvador',
    'NI' => 'Nicaragua',
    'CR' => 'Costa Rica',
    'PR' => 'Puerto Rico',
    'UY' => 'Uruguay',
    'PA' => 'Panamá',
    // Coproducciones comunes
    'US' => 'Estados Unidos', 
    'FR' => 'Francia',
    'IT' => 'Italia'
];

// 2. Convertimos las claves del diccionario en formato SQL: 'ES','MX','AR'...
$codigos_permitidos = array_keys($nombres_paises);
$lista_sql = "'" . implode("', '", $codigos_permitidos) . "'";

// 3. Consultamos SOLO los países que coincidan con nuestra lista permitida
$query_paises = "SELECT DISTINCT pais FROM producciones WHERE pais IN ($lista_sql) ORDER BY pais ASC";
$stmt_paises = $pdo->query($query_paises);
$paises_en_db = $stmt_paises->fetchAll(PDO::FETCH_COLUMN);

// 2. LÓGICA DE PAGINACIÓN
$pelis_por_pagina = 14; 

// ¿En qué página estamos? (Si no hay número, es la 1)
$pagina_actual = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;

// ¿Desde qué película empezamos a contar? (OFFSET)
// Pag 1: empieza en 0. Pag 2: empieza en 12...
$inicio = ($pagina_actual - 1) * $pelis_por_pagina;

// --- 1. CAPTURAR LO QUE EL USUARIO BUSCA ---
$busqueda = $_GET['q'] ?? '';
$pais_filtro = $_GET['pais'] ?? '';

// --- 2. PREPARAR LA CONSULTA SQL BASE ---
$sql = "SELECT * FROM producciones WHERE 1=1"; // El 1=1 es un truco para poder añadir "ANDs" después
$parametros = [];
$url_filtros = ""; // Para no perder los filtros al cambiar de página

// Si escribió algo en el buscador...
if ($busqueda !== '') {
    $sql .= " AND titulo LIKE ?";
    $parametros[] = "%$busqueda%";
    $url_filtros .= "&q=" . urlencode($busqueda);
}

// Si seleccionó un país...
if ($pais_filtro !== '') {
    $sql .= " AND pais = ?";
    $parametros[] = $pais_filtro;
    $url_filtros .= "&pais=" . urlencode($pais_filtro);
}

// --- 3. APLICAR LÍMITE Y PAGINACIÓN A LA CONSULTA ---
$sql .= " LIMIT $inicio, $pelis_por_pagina";

// Ejecutamos la consulta principal con los filtros
$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 4. CALCULAR TOTAL DE PÁGINAS (Escuchando a los filtros) ---
$sql_total = "SELECT COUNT(*) FROM producciones WHERE 1=1";
$parametros_total = [];

if ($busqueda !== '') {
    $sql_total .= " AND titulo LIKE ?";
    $parametros_total[] = "%$busqueda%";
}

if ($pais_filtro !== '') {
    $sql_total .= " AND pais = ?";
    $parametros_total[] = $pais_filtro;
}

$stmt_total = $pdo->prepare($sql_total);
$stmt_total->execute($parametros_total);
$total_pelis = $stmt_total->fetchColumn();

$total_paginas = ceil($total_pelis / $pelis_por_pagina);
if ($total_paginas == 0) $total_paginas = 1; // Para que no haya página 0
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Palomitas | Pág <?= $pagina_actual ?></title>
    <link rel="stylesheet" href= "css/estilos.css?v=3"></link>
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
           
    <section class="barra-filtros">
    <form action="index.php" method="GET" class="formulario-busqueda">
        <div class="controles-principales">
            <input type="text" name="busqueda" placeholder="Buscar película..." value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>" class="input-filtro">
            
            <select name="pais" class="input-filtro">
                <option value="">🌍 Todos los países</option>
                <?php foreach ($paises_en_db as $p): ?>
                    <option value="<?= $p ?>" <?= ($pais_filtro == $p) ? 'selected' : '' ?>>
                        <?= $nombres_paises[$p] ?? $p ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn-filtrar">Filtrar</button>
        </div>

        <a href="index.php" class="btn-limpiar">❌ Limpiar filtros</a>
    </form>
</section>

    <?php if (count($peliculas) == 0): ?>
        <h2 style="text-align: center; color: #888; margin-top: 50px;">No se encontraron películas con esos filtros. 🎬🤷‍♀️</h2>
    <?php endif; ?>
    <div class="galeria">

        <?php foreach ($peliculas as $peli): ?>
            
            <a href="detalles.php?id=<?= $peli['id_produccion'] ?>" style="text-decoration: none; color: inherit;">
                
                <div class="tarjeta">
               
<?php 
// 1. PHP comprueba si el campo de la base de datos está vacío
$enlace_portada = !empty($peli['portada']) ? $peli['portada'] : 'img/no-poster.png'; 
?>

<img 
    src="<?= htmlspecialchars($enlace_portada) ?>" 
    alt="<?= htmlspecialchars($peli['titulo']) ?>" 
    onerror="this.onerror=null; this.src='img/no-poster.png';"
>                    
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
<div class="contenedor-navegacion">
    <div class="paginacion">
        <?php if ($pagina_actual > 1): ?>
            <a href="?pag=<?= $pagina_actual - 1 ?><?= $url_filtros ?>" class="btn">⬅ Anterior</a>        <?php else: ?>
            <span class="btn desactivado">⬅ Anterior</span>
        <?php endif; ?>

        <span class="info-pag">Página <?= $pagina_actual ?> de <?= $total_paginas ?></span>

        <?php if ($pagina_actual < $total_paginas): ?>
            <a href="?pag=<?= $pagina_actual + 1 ?><?= $url_filtros ?>" class="btn">Siguiente ➡</a>        <?php else: ?>
            <span class="btn desactivado">Siguiente ➡</span>
        <?php endif; ?>
    </div>

    <div class="salto-pagina">
        <form action="index.php" method="GET" style="display: inline-flex; align-items: center; gap: 8px;">
            <?php if (!empty($_GET['busqueda'])): ?>
                <input type="hidden" name="busqueda" value="<?= htmlspecialchars($_GET['busqueda']) ?>">
            <?php endif; ?>
            <?php if (!empty($_GET['pais'])): ?>
                <input type="hidden" name="pais" value="<?= htmlspecialchars($_GET['pais']) ?>">
            <?php endif; ?>

            <label for="input-pag" style="font-size: 1em; color: #ccc;">Ir a:</label>
            <input type="number" 
                name="pag" 
                id="input-pag" 
                min="1" 
                max="<?= $total_paginas ?>" 
                value="<?= $pagina_actual ?>" 
                style="width: 50px; background: #222; color: white; border: 1px solid #444; border-radius: 4px; padding: 4px;">
            
            <button type="submit" class="btn-ir">Ir</button>
        </form>
    </div>
</div>

</body>
</html>