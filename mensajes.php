<?php

session_start();

// Si no hay usuario logueado, no permitimos acceso
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Conexión a la base de datos
require_once 'config/secrets.php';

$pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
    $user,
    $pass
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mensajes | Palomitas</title>

    <!-- Estilos generales -->
    <link rel="stylesheet" href="css/estilos.css">

    <link rel="stylesheet" href="css/mensajes.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body>

    <!-- Navbar existente -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Guardamos el usuario actual para JS -->
    <script>
        window.usuarioActual = <?= $_SESSION['usuario_id'] ?>;

        window.usuarioInicial =
            <?= isset($_GET['usuario']) ? (int)$_GET['usuario'] : 'null' ?>;
    </script>

    <div class="contenedor-chat">

        <!-- LISTA DE USUARIOS -->
        <aside class="lista-usuarios">

            <h2>
                <i class="fa-solid fa-users"></i>
                Usuarios
            </h2>

            <!-- Aquí JS insertará los usuarios -->
            <div id="listaUsuarios"></div>

        </aside>

        <!-- CHAT -->
        <section class="chat">

            <div id="cabeceraChat" class="cabecera-chat">


            </div>

            <!-- Mensajes -->
            <div id="mensajes" class="mensajes">

                <div class="sin-chat">
                    Selecciona un usuario para comenzar una conversación.
                </div>

            </div>

            <!-- Formulario -->
            <form id="formMensaje">

                <textarea
                    id="mensaje"
                    placeholder="Escribe un mensaje..."
                    required></textarea>

                <button type="submit">
                    <i class="fa-solid fa-paper-plane"></i>
                    Enviar
                </button>

            </form>

        </section>

    </div>

    <script src="js/mensajes.js"></script>

    <?php include 'includes/footer.php'; ?>

</body>

</html>