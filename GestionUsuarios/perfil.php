<?php
// Ver / editar los datos propios. Disponible para todos los roles. El DNI no se puede cambiar.
require_once __DIR__ . "/../seguridad.php";
exigirLogin();

$usuario = buscarUsuarioDni(dniActual());

// Valores iniciales: los de la BD
$DNI = $usuario->getDni();
$nombre = $usuario->getNombre();
$email = $usuario->getEmail();
$direccion = $usuario->getDireccion();
$localidad = $usuario->getLocalidad();
$provincia = $usuario->getProvincia();
$telefono = $usuario->getTelefono();
$dniBloqueado = true;
$errores = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // El DNI NO se lee del formulario: siempre es el del usuario de la sesión
    $nombre = obtenerDato("nombre");
    $email = obtenerDato("email");
    $direccion = obtenerDato("direccion");
    $localidad = obtenerDato("localidad");
    $provincia = obtenerDato("provincia");
    $telefono = obtenerDato("telefono");
    $clave = $_POST["clave"] ?? "";
    $clave2 = $_POST["clave2"] ?? "";

    $errores = validarDatosUsuario($DNI, $nombre, $email, $telefono, false);

    // La contraseña es opcional: solo se cambia si se escribe una nueva
    if ($clave !== "" && $clave !== $clave2) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    if (empty($errores)) {
        // El rol no se cambia desde aquí: se mantiene el que tiene en la BD
        actualizarUsuario($DNI, $nombre, $email, $direccion, $localidad, $provincia, $telefono, $usuario->getRol());
        if ($clave !== "") {
            actualizarClave($DNI, password_hash($clave, PASSWORD_DEFAULT));
        }
        $_SESSION["nombre"] = $nombre;
        guardarMensaje("Tus datos se han guardado correctamente.");
        header("Location: perfil.php");
        exit;
    }
}

$titulo = "Mi perfil";
require __DIR__ . "/../includes/cabecera.php";
?>
<section class="tarjeta formulario">
    <h1>Mi perfil</h1>
    <p class="subtitulo">Puedes modificar tus datos. El DNI no se puede cambiar.</p>

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
        <div class="rejilla">
            <?php require __DIR__ . "/../includes/camposUsuario.php"; ?>

            <div class="campo">
                <label for="clave">Nueva contraseña:</label>
                <input type="password" name="clave" id="clave">
            </div>

            <div class="campo">
                <label for="clave2">Repite la contraseña:</label>
                <input type="password" name="clave2" id="clave2">
            </div>
        </div>
        <p class="nota">Deja la contraseña en blanco si no quieres cambiarla.</p>

        <div class="botones">
            <a href="../GestionArticulos/listadoArticulos.php" class="boton secundario">Volver</a>
            <button type="submit" class="boton">Guardar cambios</button>
        </div>
    </form>

    <?php if (!$usuario->esAdmin()) : ?>
        <div class="zona-peligro">
            <h4>Dar de baja mi cuenta</h4>
            <p>Se borrarán tus datos y se cerrará la sesión. Esta acción no se puede deshacer.</p>
            <a href="baja.php" class="boton peligro">Darme de baja</a>
        </div>
    <?php endif ?>
</section>
<?php require __DIR__ . "/../includes/pie.php"; ?>
