<?php
// Borrado de un artículo (editor y administrador). Llega por POST desde listadoArticulos.php
require_once __DIR__ . "/../seguridad.php";
require_once __DIR__ . "/../modelo/articulosBD.php";
require_once __DIR__ . "/ttoImagenes.php";
exigirRol(["editor", "admin"]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: listadoArticulos.php");
    exit;
}

$codigo = obtenerDato("codigo");
$articulo = buscarArticuloPorCodigo($codigo);

if ($articulo === null) {
    guardarMensaje("El artículo que intentas borrar no existe.", "error");
} else {
    borrarArticulo($codigo);
    // También se borra su imagen del disco
    borrarImagen($articulo->getImagen());
    guardarMensaje("Artículo $codigo borrado correctamente.");
}

header("Location: listadoArticulos.php");
exit;
