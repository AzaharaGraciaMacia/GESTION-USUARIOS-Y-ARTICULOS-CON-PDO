<?php
// Borrado de un usuario por parte del administrador (llega por POST desde listadoUsuarios.php)
require_once __DIR__ . "/../seguridad.php";
exigirRol(["admin"]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: listadoUsuarios.php");
    exit;
}

$DNI = obtenerDato("dni");

if ($DNI === dniActual()) {
    // El administrador no puede borrarse a sí mismo
    guardarMensaje("No puedes borrar tu propia cuenta de administrador.", "error");
} elseif (!comprobarUsuario($DNI)) {
    guardarMensaje("El usuario que intentas borrar no existe.", "error");
} else {
    borrarUsuario($DNI);
    guardarMensaje("Usuario $DNI borrado correctamente.");
}

header("Location: listadoUsuarios.php");
exit;
