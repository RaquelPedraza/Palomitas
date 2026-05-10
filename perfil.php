<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado");
}
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
    $stmt = $pdo->prepare("
        SELECT l.*,
            COUNT(lp.id_produccion) AS total_peliculas
        FROM listas l
        LEFT JOIN lista_produccion lp 
            ON l.id_lista = lp.id_lista
        WHERE l.id_usuario = ?
        GROUP BY l.id_lista
    ");
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

            <h2 style="text-align: center; margin-bottom: 10px;"><?= htmlspecialchars($usuario['nombre']) ?></h2>

            <?php if ($es_mi_perfil): ?>
                <div style="margin-bottom: 20px;">
                    <a href="editar_usuario.php?id=<?= $_SESSION['usuario_id'] ?>" 
                    style="color: #aaa; text-decoration: none; font-size: 0.9em; text-align: center; display: block;">
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

            </div>

            <!-- RESEÑAS -->
            <div id="resenas" class="tab-content active">

                <?php foreach ($resenas as $r): ?>
                <div class="wrapper clickable"
                    onclick="window.location.href='detalles.php?id=<?= $r['id_produccion'] ?>&from=perfil.php'">
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
                        <div class="detalle-header">
                            <div class="resena-rating">
                                <div class="estrellas">
                                    <?= mostrarEstrellas($r['puntuacion'] ?? 0) ?>
                                </div>
                                <div class="texto-rating">
                                    <?= $r['puntuacion'] ?>/10
                                </div>
                            </div>

                            <div class="perfil-acciones" onclick="event.stopPropagation()">
                                <!-- EDITAR RESEÑA -->
                                <button class="btn-editar" onclick="abrirModalEditar(<?= $r['id_resena'] ?>)" title="Editar reseña">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <!-- ELIMINAR RESEÑA -->
                                <button type="button" onclick="abrirModalEliminar(<?= $r['id_resena'] ?>)" title="Eliminar reseña">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- DATOS RESEÑA -->
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
                    <div class="wrapper clickable"
                        onclick="window.location.href='ver_lista.php?id=<?= $l['id_lista'] ?>&from=perfil.php'">
                    <div class="card-lista" data-lista-id="<?= $l['id_lista'] ?>">
                        <div class="detalle-header">
                            <h3 style="margin: 0;"><?= htmlspecialchars($l['nombre_lista']) ?></h3>
                            <div class="perfil-acciones" onclick="event.stopPropagation()">
                                <button onclick="abrirModalEditarListaPerfil(
                                    <?= $l['id_lista'] ?>,
                                    '<?= htmlspecialchars(addslashes($l['nombre_lista'])) ?>',
                                    '<?= htmlspecialchars(addslashes($l['descripcion'])) ?>',
                                    '<?= $l['visibilidad'] ?>'
                                )">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <button onclick="abrirModalEliminarListaPerfil(<?= $l['id_lista'] ?>)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <p style="line-height: 1.6; color: var(--text-soft); margin-bottom: 12px;"><?= htmlspecialchars($l['descripcion']) ?></p>
                        <div class="lista-meta" style="color: var(--text-muted); font-size: 14px;"> <?= $l['total_peliculas'] ?> películas </div>

                    </div>
                <?php endforeach; ?>
            </div>

        </main>

    </div>

    <!-- MODAL ELIMINAR RESEÑA-->
    <div id="modalEliminarResena" class="modal">
        <div class="modal-contenido">
            <h2 class="titulo-modal">¿Desea eliminar esta reseña?</h2>
            <p style="color: #888; text-align: center;">Esta acción no se puede deshacer.</p>
            <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">
                <button type="button" onclick="cerrarModal('modalEliminarResena')" class="btn-secundario">Cancelar</button>
                    <form method="POST" action="modificar_resena.php">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_resena" id="inputEliminarResena">
                        <button type="submit" class="btn-rojo">Eliminar</button>
                    </form>
            </div>
        </div>
    </div>
                            
    <!-- MODAL EDITAR RESEÑA-->
    <div id="modalEditarResena" class="modal">
        <div id="contenidoEditarResena"></div>
    </div>
    
    <!-- MODAL ELIMINAR LISTA -->
    <div id="modalEliminarListaPerfil" class="modal">
        <div class="modal-contenido">
            <h2 class="titulo-modal">Eliminar lista</h2>
            <p style="color:#888; text-align:center;">
                ¿Seguro que quieres eliminar esta lista? Esta acción no se puede deshacer.
            </p>
            <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">
                <button type="button" class="btn-secundario" onclick="cerrarModal('modalEliminarListaPerfil')">
                    Cancelar
                </button>

                <button type="button" id="confirmarEliminarListaPerfil" class="btn-rojo" style="width: auto;">
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR LISTA -->
    <div id="modalEditarLista" class="modal">
        <div class="modal-contenido">
            <span class="cerrar" onclick="cerrarModal('modalEditarLista')">&times;</span>
            <h2 class="titulo-modal">Editar lista</h2>
            <form id="formEditarListaPerfil" method="POST" action="listas.php">
                <input type="hidden" name="action" value="editar">
                <input type="hidden" name="id_lista" id="editar_id_lista">
                <input type="text"
                    name="nombre_lista"
                    id="editar_nombre"
                    placeholder="Nombre de la lista"
                    required
                    class="input-general">

                <textarea name="descripcion"
                    id="editar_descripcion"
                    rows="4"
                    placeholder="Descripción"
                    class="textarea-general"></textarea>

                <div class="visibilidad">

                    <input type="radio" name="visibilidad" value="publica" id="edit_publica">
                    <label for="edit_publica" class="opcion-visibilidad">
                        <i class="fa-solid fa-earth-americas"></i>
                        Pública
                    </label>

                    <input type="radio" name="visibilidad" value="privada" id="edit_privada">
                    <label for="edit_privada" class="opcion-visibilidad">
                        <i class="fa-solid fa-lock"></i>
                        Privada
                    </label>
                </div>
                <button type="submit" class="btn-rojo">
                    Guardar cambios
                </button>
            </form>
        </div>
    </div>
    
    <script src="js/modales.js"></script>
    <Script src="js/perfil.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>