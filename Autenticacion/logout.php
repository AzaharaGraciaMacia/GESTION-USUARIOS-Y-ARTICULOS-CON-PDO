<?php
// Cierre de sesión seguro
require_once __DIR__ . "/../seguridad.php";

cerrarSesion();

// La sesión ya no existe, así que el aviso se pasa por la URL
header("Location: login.php?salir=1");
exit;
