<nav class="navbar">
    <div class="nav-izq" style="display: flex; align-items: center; gap: 20px;">
        <a href="index.php" class="logo" style="display: flex; align-items: center; text-decoration: none;">
            <img src="img/icono2.png" alt="Icono Palomitas" style="height: 50px; margin-right: 10px;">
        </a>
        <div class="enlaces">
            <?php $archivo_actual = basename($_SERVER['PHP_SELF']); ?>

            <a href="index.php">Catálogo</a>
                
                <?php if ($archivo_actual !== 'reels.php'): ?>
                    <a href="reels.php">Reels</a>
                <?php endif; ?>

                <?php if (isset($_SESSION['usuario_id'])): ?> 
                    <a href="mis_listas.php">Mis listas</a>
                    <a href="perfil.php">Mi Perfil</a>
                <?php endif; ?>
            </div>
        </div> 

        

    <div class="enlaces-usuario">    
        <?php if (isset($_SESSION['usuario_nombre'])): ?>
            <span style="color: #ccc; font-size: 1.1em; margin-left: 20px;">
                Hola, 
                <a href="perfil.php" style="color: white; text-decoration: none; font-size: 1.1em; margin-left: 1px; font-weight: bold;">
                    <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
                </a>
            </span>
            <a href="logout.php" style="color: #e50914; font-size: 1.1em; margin-left: 15px;">Cerrar Sesión</a>            <?php else: ?>
            <a href="login.php">Iniciar Sesión</a>
            <a href="registro.php">Crear Cuenta</a>
        <?php endif; ?>
        <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
            <a href="admin_usuarios.php">Panel Admin</a>
        <?php endif; ?>
    </div>
</nav>
    