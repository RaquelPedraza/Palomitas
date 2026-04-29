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

$stmt = $pdo->prepare("
    SELECT p.*
    FROM producciones p
    INNER JOIN favoritos f 
        ON p.id_produccion = f.id_produccion
    WHERE f.id_usuario = ?
    ORDER BY f.id_favorito DESC
");

$stmt->execute([$id_usuario]);
$peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Listas</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

<nav class="navbar">
    <a href="index.php" class="logo">🍿 Palomitas</a>

    <div class="enlaces">
        <a href="index.php">Catálogo</a>
        <a href="mis_listas.php">Mis listas</a>

        <span style="color:#ccc; margin-left:20px;">
            Hola, <strong style="color:white;">
                <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?>
            </strong>
        </span>

        <a href="logout.php" style="color:#e50914; margin-left:15px;">
            Cerrar Sesión
        </a>
    </div>
</nav>

<h1>Favoritos</h1>

<?php if (count($peliculas) === 0): ?>
    <p class="nada"> 
        Aún no has añadido nada a favoritos.
    </p>
<?php endif; ?>

<div class="galeria">

<?php foreach ($peliculas as $peli): ?>
    <div class="tarjeta">

        <a href="detalles.php?id=<?= $peli['id_produccion'] ?>" style="text-decoration:none; color:inherit;">

            <img 
                src="<?= htmlspecialchars($peli['portada'] ?: 'img/no-poster.png') ?>" 
                alt="<?= htmlspecialchars($peli['titulo']) ?>"
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

</body>
</html>
