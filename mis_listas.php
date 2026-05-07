<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/secrets.php';

$pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
    $user,
    $pass
);

$id_usuario = $_SESSION['usuario_id'];

//LISTA DE PELÍCULAS FAVORITAS
$stmt = $pdo->prepare("
    SELECT p.*
    FROM producciones p
    INNER JOIN favoritos f 
        ON p.id_produccion = f.id_produccion
    WHERE f.id_usuario = ?
    ORDER BY f.id_favorito DESC
");

$stmt->execute([$id_usuario]);
$favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

//LISTAS DEL USUARIO
$stmt = $pdo->prepare("
    SELECT *
    FROM listas
    WHERE id_usuario = ?
    ORDER BY id_lista DESC
");

$stmt->execute([$id_usuario]);
$listas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Listas</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AJAX -->
    <script src="js/favoritos.js" defer></script>
    <script src="js/listas.js" defer></script>
</head>

<body>
    <!-- NAVABAR -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- FAVORITOS -->
    <h1>Favoritos</h1>

    <?php if (count($favoritos) === 0): ?>
        <p class="nada">
            Aún no has añadido nada a favoritos.
        </p>
    <?php endif; ?>

    <?php if (count($favoritos) > 0): ?>
        <div class="carrusel-wrapper">
            <button class="flecha izquierda" onclick="scrollCarrusel('carrusel', -300)">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <div class="carrusel" id="carrusel">
                <?php foreach ($favoritos as $peli): ?>
                    <div class="tarjeta">
                        <a
                            href="detalles.php?id=<?= $peli['id_produccion'] ?>"
                            style="text-decoration:none; color:inherit;"
                        >
                            <img
                                src="<?= htmlspecialchars($peli['portada'] ?: 'img/no-poster.png') ?>"
                                alt="<?= htmlspecialchars($peli['titulo']) ?>"
                            >
                            <div class="info">
                                <div class="titulo">
                                    <?= $peli['titulo'] ?>
                                </div>
                                <div class="meta-datos">
                                    <span class="anio">
                                        <?= $peli['anio'] ?>
                                    </span>
                                    <?php if (!empty($peli['pais']) && $peli['pais'] !== '??'): ?>
                                        <span class="etiqueta-pais">
                                            <?= $peli['pais'] ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="flecha derecha" onclick="scrollCarrusel('carrusel', 300)">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- LISTAS -->
    <h1>Mis listas</h1>

    <!-- Crear lista -->
    <form id="formCrearLista" class="crear-lista">
        <input type="text" name="nombre_lista" placeholder="Nombre de la lista" required>

        <!-- visibilidad -->
        <label><input type="radio" name="visibilidad" value="publica" checked> Pública</label>
        <label><input type="radio" name="visibilidad" value="privada"> Privada</label>

        <button type="submit">Crear lista</button>
    </form>

    <?php foreach ($listas as $lista): ?>

        <div class="lista">

            <!-- indicador de visibilidad -->
            <h2>
                <?= htmlspecialchars($lista['nombre_lista']) ?>
                <?= $lista['visibilidad'] === 'publica' ? '🌍' : '🔒' ?>
            </h2>

            <!--  descripción opcional -->
            <p><?= htmlspecialchars($lista['descripcion']) ?></p>

            <?php
            // películas dentro de la lista
            $stmt = $pdo->prepare("
                SELECT p.*
                FROM producciones p
                INNER JOIN lista_produccion lp
                    ON p.id_produccion = lp.id_produccion
                WHERE lp.id_lista = ?
            ");

            $stmt->execute([$lista['id_lista']]);
            $pelis_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div class="galeria">

            <?php foreach ($pelis_lista as $peli): ?>
                <div class="tarjeta">

                    <img 
                        src="<?= htmlspecialchars($peli['portada'] ?: 'img/no-poster.png') ?>"
                        alt="<?= htmlspecialchars($peli['titulo']) ?>"
                    >

                    <div class="titulo"><?= $peli['titulo'] ?></div>

                </div>
            <?php endforeach; ?>

            </div>

            <!-- acciones CRUD futuras -->
            <?php if ($lista['id_usuario'] == $id_usuario): ?>
                <button>Editar</button>
                <button>Eliminar</button>
            <?php endif; ?>

        </div>

    <?php endforeach; ?>

    <script>
        function scrollCarrusel(id, valor) {
            const carrusel = document.getElementById(id);
            if (!carrusel) return;
            carrusel.scrollBy({
                left: valor,
                behavior: 'smooth'
            });
        }
    </script>

</body>
</html>
