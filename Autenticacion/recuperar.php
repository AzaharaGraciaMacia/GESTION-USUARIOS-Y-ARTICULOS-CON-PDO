<?php
/* Recuperar contraseña en dos pasos:
 *   1) El usuario escribe su DNI y su email. Si coinciden con un usuario de la BD,
 *      se guarda el DNI en la sesión ("recuperar_dni").
 *   2) Con ese DNI en la sesión se muestra el formulario para escribir la nueva contraseña.
 */
require_once __DIR__ . "/../seguridad.php";

if (usuarioLogueado()) {
    header("Location: " . PAGINA_INICIO);
    exit;
}

$DNI = "";
$email = "";
$errores = [];

// Botón "Cancelar" del paso 2: se olvida el DNI y se vuelve al paso 1
if (isset($_GET["cancelar"])) {
    unset($_SESSION["recuperar_dni"]);
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $paso = obtenerDato("paso");

    if ($paso === "comprobar") {
        // Paso 1: comprobar DNI + email
        $DNI = strtoupper(obtenerDato("dni"));
        $email = obtenerDato("email");

        if ($DNI === "" || $email === "") {
            $errores[] = "Debe introducir el DNI y el email.";
        } elseif (!comprobarDniEmail($DNI, $email)) {
            $errores[] = "El DNI y el email no coinciden con ningún usuario registrado.";
        } else {
            $_SESSION["recuperar_dni"] = $DNI;
        }
    } elseif ($paso === "cambiar" && isset($_SESSION["recuperar_dni"])) {
        // Paso 2: guardar la nueva contraseña
        $clave = $_POST["clave"] ?? "";
        $clave2 = $_POST["clave2"] ?? "";

        if ($clave === "") {
            $errores[] = "Debe introducir la nueva contraseña.";
        } elseif ($clave !== $clave2) {
            $errores[] = "Las contraseñas no coinciden.";
        } else {
            actualizarClave($_SESSION["recuperar_dni"], password_hash($clave, PASSWORD_DEFAULT));
            unset($_SESSION["recuperar_dni"]);
            guardarMensaje("Contraseña cambiada correctamente. Ya puedes iniciar sesión.");
            header("Location: login.php");
            exit;
        }
    }
}

// Si ya se ha comprobado el DNI + email, se muestra el paso 2
$mostrarPaso2 = isset($_SESSION["recuperar_dni"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Azahara Gracia Macia">
    <title>Recuperar contraseña</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<main class="contenedor">
    <h1>Recuperar contraseña</h1>

    <?php if (!empty($errores)) : ?>
        <div class="errores">
            <ul>
                <?php foreach ($errores as $error) : ?>
                    <li><?= textoProtegido($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <?php if (!$mostrarPaso2) : ?>
        <p class="subtitulo">Introduce tu DNI y el email con el que te registraste</p>

        <form method="post">
            <input type="hidden" name="paso" value="comprobar">

            <div class="campo">
                <label for="dni">DNI:</label>
                <input type="text" name="dni" id="dni" value="<?= textoProtegido($DNI) ?>" placeholder="Ej: 12345678Z">
            </div>

            <div class="campo">
                <label for="email">Correo electrónico:</label>
                <input type="email" name="email" id="email" value="<?= textoProtegido($email) ?>">
            </div>

            <div class="botones">
                <a href="login.php" class="boton secundario">Volver</a>
                <button type="submit" class="boton">Continuar</button>
            </div>
        </form>
    <?php else : ?>
        <p class="subtitulo">
            Escribe la nueva contraseña para el DNI <?= textoProtegido($_SESSION["recuperar_dni"]) ?>
        </p>

        <form method="post">
            <input type="hidden" name="paso" value="cambiar">

            <div class="campo">
                <label for="clave">Nueva contraseña:</label>
                <input type="password" name="clave" id="clave">
            </div>

            <div class="campo">
                <label for="clave2">Repite la contraseña:</label>
                <input type="password" name="clave2" id="clave2">
            </div>

            <div class="botones">
                <a href="recuperar.php?cancelar=1" class="boton secundario">Cancelar</a>
                <button type="submit" class="boton">Guardar contraseña</button>
            </div>
        </form>
    <?php endif ?>
</main>
</body>
</html>
