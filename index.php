<?php
session_start();

$id_usuario = $_SESSION['usuario_id'] ?? null;

// CONEXIÓN
require_once 'includes/functions.php';
require_once 'config/secrets.php';

$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $password = $_POST['password'] ?? '';
}

// ETIQUETAS
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
    // Coproducciones 
    'US' => 'Estados Unidos', 
    'FR' => 'Francia',
    'IT' => 'Italia'
];

// CONVERSOR DE FORMATOS
$codigos_permitidos = array_keys($nombres_paises);
$lista_sql = "'" . implode("', '", $codigos_permitidos) . "'";

$query_paises = "SELECT DISTINCT pais FROM producciones WHERE pais IN ($lista_sql) ORDER BY pais ASC";
$stmt_paises = $pdo->query($query_paises);
$paises_en_db = $stmt_paises->fetchAll(PDO::FETCH_COLUMN);

// PAGINACIÓN
$pelis_por_pagina = 14; 

$pagina_actual = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;

$inicio = ($pagina_actual - 1) * $pelis_por_pagina;

// BUSQUEDA FILTRADA
$busqueda = $_GET['busqueda'] ?? '';
$pais_filtro = $_GET['pais'] ?? '';

$sql = "SELECT * FROM producciones WHERE 1=1";
$parametros = [];
$url_filtros = ""; 

if ($busqueda !== '') {
    $sql .= " AND titulo LIKE ?";
    $parametros[] = "%$busqueda%";
    $url_filtros .= "&busqueda=" . urlencode($busqueda);
}

if ($pais_filtro !== '') {
    $sql .= " AND pais = ?";
    $parametros[] = $pais_filtro;
    $url_filtros .= "&pais=" . urlencode($pais_filtro);
}

$sql .= " LIMIT $inicio, $pelis_por_pagina";

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PÁGINAS Y FILTROS
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
if ($total_paginas == 0) $total_paginas = 1; 

// TOP 10
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

//FAVORITOS
$favoritos = [];

if ($id_usuario) {
    $stmtFav = $pdo->prepare("
        SELECT id_produccion 
        FROM favoritos 
        WHERE id_usuario = ?
    ");
    $stmtFav->execute([$id_usuario]);
    $favoritos = array_flip($stmtFav->fetchAll(PDO::FETCH_COLUMN));
}

?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Palomitas | Pág <?= $pagina_actual ?></title>
    <link rel="stylesheet" href= "css/estilos.css?v=6"></link>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body>
    <nav class="navbar">
    <a href="index.php" class="logo" style="display: flex; align-items: center; text-decoration: none;">
        <img src="img/logo.png" alt="Logo Palomitas" style="height: 50px; margin-right: 10px;">
    </a>
    
    <div class="enlaces">
            <a href="index.php">Catálogo</a>

            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="mis_listas.php">Mis listas</a>
            <?php endif; ?>
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

   <!-- TÍTULO PRINCIPAL --> 
    <img class="logo-principal" src="img/icono.png" alt="Icono Palomitas">
    <h1 class="titulo-principal">PALOMITAS</h1>
    <div class = "eslogan-sitio">Tu Catálogo Hispano</div>     

    <!-- TOP 10 -->
     <h2>Top 10 mejor valoradas</h2>

    <div class="top10">
        <?php foreach ($top_pelis as $peli): ?>
            <a href="detalles.php?id=<?= $peli['id_produccion'] ?>" style="text-decoration: none; color: inherit;">
                <div class="tarjeta">
                    <img src="<?= $peli['portada'] ?>" alt="<?= $peli['titulo'] ?>">
                    <div class="info">
                        <div class="titulo"><?= $peli['titulo'] ?></div>
                        <div class="estrellas">
                            <i class="fas fa-star"></i>
                            <span class="nota-media">
                                <?= number_format($peli['media'], 1) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>

    </div>

    <!-- CATÁLOGO -->

    <h2> Catálogo</h2>            
    <section class="barra-filtros" id="filtrar">
    <form action="index.php" method="GET" class="formulario-busqueda">
        <div class="controles-principales">
            <input type="text" name="busqueda" placeholder="Buscar película..." value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>" class="input-filtro">
            
            <select name="pais" class="input-filtro select-con-icono">
                <option value="">Todos los países</option>
                <?php foreach ($paises_en_db as $p): ?>
                    <option value="<?= $p ?>" <?= ($pais_filtro == $p) ? 'selected' : '' ?>>
                        <?= $nombres_paises[$p] ?? $p ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn-filtrar">Filtrar</button>
        </div>

        <a href="index.php" class="btn-limpiar">Limpiar filtros</a>
    </form>
</section>

    <?php if (count($peliculas) == 0): ?>
        <h2 style="text-align: center; color: #888; margin-top: 50px;">No se encontraron películas con esos filtros. 🎬🤷‍♀️</h2>
    <?php endif; ?>

    <!-- GALERIA -->
    <div class="galeria">
        <?php foreach ($peliculas as $peli): ?>

            <?php 
                // portada
                $enlace_portada = !empty($peli['portada']) 
                    ? $peli['portada'] 
                    : 'img/no-poster.png'; 

                // favorito
                $esFav = isset($favoritos[$peli['id_produccion']]); 
            ?>

            <div class="tarjeta">
                <!-- BOTÓN FAVORITO -->
                <?php if ($id_usuario): ?>
                    <button 
                        class="corazon" 
                        onclick="toggleFavorito(this, <?= $peli['id_produccion'] ?>)"
                    >
                        <i class="<?= $esFav ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                    </button>
                <?php endif; ?>
                <!-- LINK A DETALLES -->
                <a href="detalles.php?id=<?= $peli['id_produccion'] ?>" style="text-decoration: none; color: inherit;">
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
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- PAGINACIÓN -->
        <div class="contenedor-navegacion">
            <div class="paginacion">
            <?php if ($pagina_actual > 1): ?>
                <a href="?pag=<?= $pagina_actual - 1 ?><?= $url_filtros ?>#filtrar" class="btn-pag">
                    <img src="img/arrow_back.svg" class="icono-pag" alt="atrás"> Anterior
                </a>        
            <?php else: ?>
                <span class="btn desactivado">
                    <img src="img/arrow_back.svg" class="icono-pag" alt="atrás"> Anterior
                </span>
            <?php endif; ?>

            <span class="info-pag">Página <?= $pagina_actual ?> de <?= $total_paginas ?></span>

            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pag=<?= $pagina_actual + 1 ?><?= $url_filtros ?>#filtrar" class="btn-pag">
                    Siguiente <img src="img/arrow_forward.svg" class="icono-pag" alt="adelante">
                </a>        
            <?php else: ?>
                <span class="btn desactivado">
                    Siguiente <img src="img/arrow_forward.svg" class="icono-pag" alt="adelante">
                </span>
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
    <script src="js/favoritos.js"></script>
</body>
</html>