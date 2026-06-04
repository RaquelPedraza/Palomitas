<nav class="navbar">
    <div class="nav-izq" style="display: flex; align-items: center; gap: 20px;">
        <a href="index.php" class="logo" style="display: flex; align-items: center; text-decoration: none;">
            <img src="img/icono2.png" alt="Icono Palomitas" style="height: 50px; margin-right: 10px;">
        </a>

        <div id="btnHamburguesa" class="hamburguesa-custom" onclick="toggleMenu()">
            <span class="material-symbols-outlined">
                <i class="fa-solid fa-bars"></i>
            </span>
        </div>
        
        <div id="navLinks" class="menu-colapsable">

        <div class="enlaces">
            <?php $archivo_actual = basename($_SERVER['PHP_SELF']); 
            $esta_logueado = isset($_SESSION['usuario_id']);
            ?>

            <?php if (isset($_SESSION['usuario_id'])): ?> 
                <a href="perfil.php">Mi Perfil</a>
                <a href="mis_listas.php">Mis listas</a>
                <a href="mensajes.php">Mensajes</a>
            <?php endif; ?>

            <a href="index.php">Catálogo</a>
            <a href="reels.php">Reels</a>
            
        </div> 

        <div class="nav-centro" style="flex: 1; text-align: center;">
            <?php if ($archivo_actual === 'reels.php' && $esta_logueado): ?>
                <a href="subir_reel.php" class="btn-subir-reels-centro">
                    <i class="fa-solid fa-plus-circle"></i>
                    Subir Reel
                </a>
            <?php endif; ?>
        </div>
        

    <div class="enlaces-usuario">    
        <?php if (isset($_SESSION['usuario_nombre'])): ?>
            <span >
                Hola, 
                <a href="perfil.php" style="color: white; text-decoration: none; font-size: 1.1em; margin-left: 1px; font-weight: bold;">
                    <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
                </a>
            </span>
            <a href="logout.php" style="color: #e50914; font-size: 1.1em; margin-left: 15px;">Cerrar Sesión</a>            
            <?php else: ?>
            <a href="login.php">Iniciar Sesión</a>
            <a href="registro.php">Crear Cuenta</a>
        <?php endif; ?>
        <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
            <a href="admin_usuarios.php">Panel Admin</a>
        <?php endif; ?>
    </div>
    </div>
</nav>

<script>
  const btnHamburguesa = document.getElementById("btnHamburguesa");
  const navLinks = document.getElementById("navLinks");

  // Función que abre y cierra
  function toggleMenu() {
    navLinks.classList.toggle("active");
  }

  // Cerrar al hacer clic fuera
  document.addEventListener("click", (e) => {
    if (navLinks.classList.contains("active")) {
      if (!navLinks.contains(e.target) && !btnHamburguesa.contains(e.target)) {
        navLinks.classList.remove("active");
      }
    }
  });
</script>