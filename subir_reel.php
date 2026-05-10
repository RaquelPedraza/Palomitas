<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado");
}

require_once 'config/secrets.php';
$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);

// Verificamos con la variable correcta del main
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$mensaje = "";

// 1. Obtener la lista de películas para el desplegable
$sql_prod = "SELECT id_produccion, titulo FROM producciones ORDER BY titulo ASC";
$stmt_prod = $pdo->query($sql_prod);
$producciones = $stmt_prod->fetchAll();

// 2. Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enlace_video = $_POST['enlace_video'];
    $titulo_clip = $_POST['titulo_clip'];
    $id_produccion = $_POST['id_produccion']; 
    $categoria = $_POST['categoria']; 
    $id_usuario = $_SESSION['usuario_id']; 

    // Guardamos el enlace tal cual en la base de datos
    if (!empty($enlace_video) && !empty($titulo_clip) && !empty($id_produccion)) {
        $sql = "INSERT INTO clips (id_usuario, id_produccion, titulo_clip, enlace_video, categoria) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$id_usuario, $id_produccion, $titulo_clip, $enlace_video, $categoria])) {
            $mensaje = "<div class='alert alert-success rounded-0'>¡Clip publicado! <a href='reels.php' class='alert-link'>Ir a ver Reels</a></div>";
        } else {
            $mensaje = "<div class='alert alert-danger rounded-0'>Error al guardar en la base de datos.</div>";
        }
    } else {
        $mensaje = "<div class='alert alert-warning' rounded-0>Por favor, rellena los campos obligatorios.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Clip | Palomitas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="bg-dark text-white">
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                <div class="card bg-warning text-dark shadow-lg rounded-2 border-0">
                    <div class="card-body p-3">
                        <h2 class="text-center mt-0 mb-3 fs-4 rounded-2 font-monospace text-uppercase" style="letter-spacing: 3px;"><img src="img/video_camera.svg" alt="Cámara" class="me-2" style="width: 35px;"> Compartir Clip</h2>
                        <?= $mensaje ?>
                        <form action="subir_reel.php" method="POST">
                            
                            <div class="mb-1">
                                <label class="form-label mb-0 small">Película/Corto relacionado *</label>
                                <select name="id_produccion" class="form-select bg-dark text-white border-secondary rounded-0" required>
                                    <option value="">Selecciona una producción...</option>
                                    <?php foreach ($producciones as $prod): ?>
                                        <option value="<?= $prod['id_produccion'] ?>"><?= htmlspecialchars($prod['titulo']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-1">
                                <label class="form-label mb-0 small">Enlace de YouTube *</label>
                                <input type="url" name="enlace_video" class="form-control bg-dark text-white border-secondary rounded-0" data-bs-theme="dark" placeholder="Ej: https://www.youtube.com/watch?v=..." required>
                            </div>
                            
                            <div class="mb-1">
                                <label class="form-label mb-0 small">Título del Clip *</label>
                                <input type="text" name="titulo_clip" class="form-control bg-dark text-white border-secondary rounded-0" data-bs-theme="dark" maxlength="200" required>
                            </div>

                            <div class="mb-1">
                                <label class="form-label mb-0 small">Categoría (Opcional)</label>
                                <input type="text" name="categoria" class="form-control bg-dark text-white border-secondary rounded-0" data-bs-theme="dark" placeholder="Ej: Tráiler, Escena épica, Entrevista..." maxlength="100">
                            </div>

                            <div class="d-grid mt-2">
                                <button type="submit" class="btn btn-danger btn-lg fw-bold">¡Publicar!</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>