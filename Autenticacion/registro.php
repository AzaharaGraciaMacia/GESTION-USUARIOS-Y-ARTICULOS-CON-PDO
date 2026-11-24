<?php
require_once __DIR__ . "/../seguridad.php";

// Si ya hay sesión iniciada no tiene sentido registrarse
if (usuarioLogueado()) {
    header("Location: " . PAGINA_INICIO);
    exit;
}

// Declaración de variables que se usarán
$DNI = "";
$nombre = "";
$email = "";
$direccion = "";
$localidad = "";
$provincia = "";
$telefono = "";
$dniBloqueado = false;   // en el registro el DNI sí se puede escribir
$errores = [];

// Recibe los datos del formulario, comprueba que no hay errores e inserta los datos. Después devuelve al login.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $DNI = strtoupper(obtenerDato("DNI"));
    $nombre = obtenerDato("nombre");
    $email = obtenerDato("email");
    $direccion = obtenerDato("direccion");
    $localidad = obtenerDato("localidad");
    $provincia = obtenerDato("provincia");
    $telefono = obtenerDato("telefono");
    $clave = $_POST["clave"] ?? "";   // la contraseña no se modifica (ni trim ni htmlspecialchars)
    $rol = "cliente";                 // quien se registra desde aquí siempre es cliente

    // Validaciones comunes (DNI, email, teléfono, duplicados) en modelo/usuariosBD.php
    $errores = validarDatosUsuario($DNI, $nombre, $email, $telefono, true);
    if ($clave === "") {
        $errores[] = "Debe introducir una contraseña.";
    }

    if (empty($errores)) {
        $clave = password_hash($clave, PASSWORD_DEFAULT);
        insertarUsuario($DNI, $nombre, $email, $direccion, $localidad, $provincia, $telefono, $clave, $rol);
        guardarMensaje("Usuario registrado correctamente. Ya puedes iniciar sesión.");
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Azahara Gracia Macia">
    <title>Formulario de Registro</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<main class="contenedor ancho">
    <h1>Registro de cliente</h1>
    <p class="subtitulo">Rellena tus datos para crear una cuenta</p>

    <?php if (!empty($errores)) : ?>
        <div class="errores">
            <h4>Se han encontrado los siguientes errores:</h4>
            <ul>
                <?php foreach ($errores as $error) : ?>
                    <li><?= textoProtegido($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <form method="post">
        <p class="nota">Los campos marcados con (*) son obligatorios.</p>

        <div class="rejilla">
            <?php require __DIR__ . "/../includes/camposUsuario.php"; ?>

            <div class="campo completo">
                <label for="clave">Contraseña (*):</label>
                <input type="password" name="clave" id="clave">
            </div>
        </div>

        <div class="botones">
            <a href="login.php" class="boton secundario">Cancelar y volver</a>
            <button type="submit" class="boton">Registrarse</button>
        </div>
    </form>
</main>
</body>
</html>
