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

    /* ELIMINAR RESEÑA */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? '';
        if ($accion === 'eliminar') {
            $id_resena = $_POST['id_resena'] ?? null;
            $stmt = $pdo->prepare("
                DELETE FROM resenas
                WHERE id_resena = ?
                AND id_usuario = ?
            ");

            $stmt->execute([$id_resena, $id_usuario]);

            header("Location: perfil.php");
            exit;
        }
    }

    /* EDITAR RESEÑA */
    $id_resena = $_GET['id'] ?? null;

    if (!$id_resena) {
        die("Reseña no encontrada");
    }

    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            p.titulo
        FROM resenas r
        JOIN producciones p 
            ON p.id_produccion = r.id_produccion
        WHERE r.id_resena = ?
        AND r.id_usuario = ?
    ");

    $stmt->execute([$id_resena, $id_usuario]);

    $resena = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resena) {
        die("Reseña no encontrada");
    }


?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Editar reseña</title>
        <link rel="stylesheet" href="css/estilos.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>

    <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModal('modalEditarResena')">
                &times;
            </span>

        <h2 class="titulo-modal">
            Editar reseña de <strong><?= htmlspecialchars($resena['titulo']) ?></strong>
        </h2>

        <!-- FORM EDITAR -->
        <form action="guardar_resena.php" method="POST">
            <input 
                type="hidden"
                name="pelicula_id"
                value="<?= $resena['id_produccion'] ?>"
            >

            <!-- RATING -->
            <div class="rating">
                <?php for ($i = 10; $i >= 1; $i--): ?>
                    <input 
                        type="radio"
                        id="star<?= $i ?>"
                        name="puntuacion"
                        value="<?= $i ?>"
                        required
                        <?= ($resena['puntuacion'] == $i) ? 'checked' : '' ?>
                    >
                    <label for="star<?= $i ?>">
                        <i class="fas fa-star"></i>
                    </label>
                <?php endfor; ?>
            </div>

            <!-- TITULO -->
            <input
                type="text"
                name="titulo_resena"
                maxlength="200"
                required
                class="titulo-resena"
                value="<?= htmlspecialchars($resena['titulo_resena']) ?>"
            >

            <!-- CONTENIDO -->
            <textarea
                name="texto"
                rows="6"
                required
                class="textarea-general"
            ><?= htmlspecialchars($resena['contenido']) ?></textarea>

            <button type="submit" class="btn-rojo">
                Guardar cambios
            </button>

        </form>

        <!-- ELIMINAR -->
        <form method="POST" 
        onsubmit="return 
        confirm('¿Eliminar esta reseña?')" 
        style="margin-top:20px;"
        >
            <button type="submit" class="btn-rojo">
                Eliminar reseña
            </button>
        </form>

    </div>

    </body>
</html>