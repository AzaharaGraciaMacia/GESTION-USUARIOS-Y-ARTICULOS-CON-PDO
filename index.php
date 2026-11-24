<?php
// Página principal: lleva al login (o al catálogo si ya hay sesión iniciada)
require_once __DIR__ . "/seguridad.php";

if (usuarioLogueado()) {
    header("Location: GestionArticulos/listadoArticulos.php");
} else {
    header("Location: Autenticacion/login.php");
}
exit;
