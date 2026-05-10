<?php
session_start();
require_once 'includes/functions.php';
require_once 'config/secrets.php';

$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);

$id_usuario = $_GET['id'] ?? $_SESSION['usuario_id'] ?? null;

if (!$id_usuario) {
    die("Usuario no encontrado");
}

/* USUARIO */
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

/* SEGUIDORES */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM seguidores WHERE id_seguido = ?");
$stmt->execute([$id_usuario]);
$seguidores = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM seguidores WHERE id_seguidor = ?");
$stmt->execute([$id_usuario]);
$siguiendo = $stmt->fetchColumn();

/* RESEÑAS */
$stmt = $pdo->prepare("
SELECT r.*, p.titulo, p.portada, p.anio, p.pais
FROM resenas r
JOIN producciones p ON p.id_produccion = r.id_produccion
WHERE r.id_usuario = ?
ORDER BY r.fecha DESC
");
$stmt->execute([$id_usuario]);
$resenas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* LISTAS */
$stmt = $pdo->prepare("SELECT * FROM listas WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$listas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* CLIPS */
$stmt = $pdo->prepare("
SELECT c.*, p.titulo
FROM clips c
JOIN producciones p ON p.id_produccion = c.id_produccion
WHERE c.id_usuario = ?
ORDER BY c.fecha_subida DESC
");
$stmt->execute([$id_usuario]);
$clips = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* LÓGICA DE SEGUIMIENTO */
$es_mi_perfil = (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $id_usuario);
$ya_le_sigo = false;

if (isset($_SESSION['usuario_id']) && !$es_mi_perfil) {
    $check = $pdo->prepare("SELECT 1 FROM seguidores WHERE id_seguidor = ? AND id_seguido = ?");
    $check->execute([$_SESSION['usuario_id'], $id_usuario]);
    $ya_le_sigo = $check->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>

    <!-- ESTILOS -->
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="perfil-layout">

        <!-- SIDEBAR -->
        <aside class="perfil-sidebar">

        <?php if (isset($_SESSION['usuario_id']) && !$es_mi_perfil): ?>
                <div style="margin-top: 20px; text-align: center;">
                    <a href="seguir.php?id=<?= $id_usuario ?>&accion=<?= $ya_le_sigo ? 'unfollow' : 'follow' ?>" 
                    style="display: block; padding: 10px; border-radius: 5px; text-decoration: none; font-weight: bold; 
                            <?= $ya_le_sigo ? 'background-color: transparent; border: 1px solid white; color: white;' : 'background-color: #e50914; color: white;' ?>">
                        <?= $ya_le_sigo ? 'Dejar de seguir' : 'Seguir' ?>
                    </a>
                </div>
            <?php endif; ?>

            <div class="perfil-avatar">
                <img src="<?= $usuario['avatar'] ?? 'img/default-avatar.png' ?>">
            </div>

            <h2><?= htmlspecialchars($usuario['nombre']) ?></h2>

            <?php if ($es_mi_perfil): ?>
                <div style="margin-top: 10px; margin-bottom: 20px;">
                    <a href="editar_usuario.php?id=<?= $_SESSION['usuario_id'] ?>" 
                    style="color: #aaa; text-decoration: underline; font-size: 0.9em;">
                    <i class="fa-solid fa-pen"></i> Editar mis datos
                    </a>
                </div>
            <?php endif; ?>

            <div class="perfil-stats">
                <div><strong><?= $seguidores ?></strong> Seguidores</div>
                <div><strong><?= $siguiendo ?></strong> Siguiendo</div>
                <div><strong><?= count($resenas) ?></strong> Reseñas</div>
                <div><strong><?= count($listas) ?></strong> Listas</div>
            </div>

        </aside>

        <!-- MAIN -->
        <main class="perfil-main">

            <!-- TABS -->
            <div class="perfil-tabs">

                <button class="tab active" onclick="showTab('resenas')">
                    <i class="fa-solid fa-star"></i> Reseñas
                </button>

                <button class="tab" onclick="showTab('listas')">
                    <i class="fa-solid fa-list"></i> Listas
                </button>

                <button class="tab" onclick="showTab('clips')">
                    <i class="fa-solid fa-clapperboard"></i> Clips
                </button>

            </div>

            <!-- RESEÑAS -->
            <div id="resenas" class="tab-content active">

                <?php foreach ($resenas as $r): ?>

                <div class="resena-wrapper clickable"
                    onclick="window.location.href='detalles.php?id=<?= $r['id_produccion'] ?>'">

                    <div class="resena-pelicula">

                        <div class="tarjeta tarjeta-mini">

                            <img src="<?= $r['portada'] ?>"
                                onerror="this.src='img/no-poster.png'">

                            <div class="info">
                                <div class="titulo"><?= htmlspecialchars($r['titulo']) ?></div>

                                <div class="meta-datos">
                                    <span><?= $r['anio'] ?></span>

                                    <?php if (!empty($r['pais'])): ?>
                                        <span class="etiqueta-pais"><?= $r['pais'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="resena-detalle">

                        <div class="resena-acciones" onclick="event.stopPropagation()">

                            <a href="editar_resena.php?id=<?= $r['id_resena'] ?>">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <form method="POST" action="eliminar_resena.php">
                                <input type="hidden" name="id_resena" value="<?= $r['id_resena'] ?>">
                                <button type="submit">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>

                        </div>
                        
                        <div class="resena-rating">
                            <?= mostrarEstrellas($r['puntuacion'] ?? 0) ?>
                            <span><?= $r['puntuacion'] ?>/10</span>

                        </div>
                        <h4><?= htmlspecialchars($r['titulo_resena']) ?></h4>
                        <p><?= nl2br(htmlspecialchars($r['contenido'])) ?></p>
                        <p class="fecha-comentario"><?= date('d-m-Y', strtotime($r['fecha'])) ?></p>

                    </div>

                </div>

                <?php endforeach; ?>

            </div>

            <!-- LISTAS -->
            <div id="listas" class="tab-content">
                <?php foreach ($listas as $l): ?>
                    <div class="card-lista">
                        <h3><?= htmlspecialchars($l['nombre_lista']) ?></h3>
                        <p><?= htmlspecialchars($l['descripcion']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- CLIPS -->
            <div id="clips" class="tab-content">
                <?php foreach ($clips as $c): ?>
                    <div class="card-clip">
                        <iframe src="<?= $c['enlace_video'] ?>" allowfullscreen></iframe>
                        <h3><?= htmlspecialchars($c['titulo']) ?></h3>
                    </div>
                <?php endforeach; ?>
            </div>

        </main>

    </div>

    <script>
        function showTab(tab){
            document.querySelectorAll('.tab-content').forEach(e => e.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(e => e.classList.remove('active'));

            document.getElementById(tab).classList.add('active');
            event.target.classList.add('active');
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>