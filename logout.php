<?php
session_start();
session_destroy(); 
header("Location: index.php"); // Devuelve al catálogo
exit;
?>