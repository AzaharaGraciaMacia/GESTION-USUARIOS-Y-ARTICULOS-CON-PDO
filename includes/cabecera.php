<?php
// Cabecera común de las páginas con sesión iniciada: <head>, menú de navegación y mensajes flash.
// Antes de incluirla, la página debe definir $titulo y haber llamado a exigirLogin() o exigirRol().

$mensajeFlash = leerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Azahara Gracia Macia">
    <title><?= textoProtegido($titulo) ?> - Tienda de Azahara</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body class="con-menu">
<header class="barra">
    <div class="barra-interior">
        <a href="../GestionArticulos/listadoArticulos.php" class="marca">Tienda de Azahara</a>
        <nav class="menu">
            <a href="../GestionArticulos/listadoArticulos.php">Catálogo</a>
            <?php if (rolActual() === "admin") : ?>
                <a href="../GestionUsuarios/listadoUsuarios.php">Usuarios</a>
            <?php endif ?>
            <a href="../GestionUsuarios/perfil.php">Mi perfil</a>
            <span class="usuario">
                <?= textoProtegido($_SESSION["nombre"] ?? "") ?>
                <span class="etiqueta rol-<?= textoProtegido(rolActual()) ?>"><?= textoProtegido(rolActual()) ?></span>
            </span>
            <a href="../Autenticacion/logout.php" class="salir">Cerrar sesión</a>
        </nav>
    </div>
</header>
<main class="pagina">
    <?php if ($mensajeFlash !== null) : ?>
        <div class="<?= $mensajeFlash["tipo"] === "error" ? "errores" : "exito" ?>">
            <?= textoProtegido($mensajeFlash["texto"]) ?>
        </div>
    <?php endif ?>
