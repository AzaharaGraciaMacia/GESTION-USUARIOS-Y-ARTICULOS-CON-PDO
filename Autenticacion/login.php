<?php
require_once __DIR__ . "/../seguridad.php";

// Si ya hay sesión iniciada, directamente al catálogo
if (usuarioLogueado()) {
    header("Location: " . PAGINA_INICIO);
    exit;
}

$DNI = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $DNI = strtoupper(obtenerDato("dni"));
    $clave = $_POST["clave"] ?? "";

    if ($DNI === "" || $clave === "") {
        $error = "Debe introducir el DNI y la contraseña.";
    } else {
        $usuario = buscarUsuarioDni($DNI);

        // Mismo mensaje si no existe el DNI o si la contraseña es incorrecta, para no dar pistas sobre qué DNI están registrados
        if ($usuario === null || !$usuario->comprobarClave($clave)) {
            $error = "DNI o contraseña incorrectos.";
        } else {
            iniciarSesionUsuario($usuario);
            header("Location: " . PAGINA_INICIO);
            exit;
        }
    }
}

// Mensaje que deja otra página antes de redirigir aquí (registro, recuperar contraseña...)
$mensaje = leerMensaje();

// Mensajes que llegan por la URL, cuando la sesión ya se ha eliminado (cerrar sesión o darse de baja)
if (isset($_GET["salir"])) {
    $mensaje = ["texto" => "Has cerrado sesión correctamente."];
} elseif (isset($_GET["baja"])) {
    $mensaje = ["texto" => "Tu cuenta se ha dado de baja. ¡Hasta pronto!"];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Azahara Gracia Macia">
    <title>Login en tienda de Azahara</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<main class="contenedor">
    <h1>Tienda de Azahara</h1>
    <p class="subtitulo">Inicia sesión para continuar</p>

    <?php if ($mensaje !== null) : ?>
        <div class="exito">
            <?= textoProtegido($mensaje["texto"]) ?>
        </div>
    <?php endif ?>

    <?php if ($error !== "") : ?>
        <div class="errores"><?= textoProtegido($error) ?></div>
    <?php endif ?>

    <form method="post">
        <div class="campo">
            <label for="dni">DNI:</label>
            <input type="text" name="dni" id="dni" value="<?= textoProtegido($DNI) ?>" placeholder="Ej: 12345678Z">
        </div>

        <div class="campo">
            <label for="clave">Contraseña:</label>
            <input type="password" name="clave" id="clave">
        </div>

        <div class="botones">
            <button type="submit" class="boton">Iniciar sesión</button>
        </div>
    </form>

    <div class="enlaces">
        <p><a href="recuperar.php">He olvidado mi contraseña</a></p>
        <p>¿No tienes cuenta? <a href="registro.php">Registrarse</a></p>
    </div>
</main>
</body>
</html>
