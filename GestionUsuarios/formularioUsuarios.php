<?php
// Alta y edición de usuarios por parte del administrador.
//   formularioUsuarios.php           -> alta de un usuario nuevo (con cualquier rol)
//   formularioUsuarios.php?dni=XXXX  -> edición (el DNI no se puede cambiar)
require_once __DIR__ . "/../seguridad.php";
exigirRol(["admin"]);

const ROLES = ["cliente", "editor", "admin"];

$modoEdicion = isset($_GET["dni"]);
$errores = [];

if ($modoEdicion) {
    $usuarioEditado = buscarUsuarioDni(obtenerDatoGet("dni"));
    if ($usuarioEditado === null) {
        guardarMensaje("El usuario que intentas editar no existe.", "error");
        header("Location: listadoUsuarios.php");
        exit;
    }
    $DNI = $usuarioEditado->getDni();
    $nombre = $usuarioEditado->getNombre();
    $email = $usuarioEditado->getEmail();
    $direccion = $usuarioEditado->getDireccion();
    $localidad = $usuarioEditado->getLocalidad();
    $provincia = $usuarioEditado->getProvincia();
    $telefono = $usuarioEditado->getTelefono();
    $rol = $usuarioEditado->getRol();
} else {
    $DNI = "";
    $nombre = "";
    $email = "";
    $direccion = "";
    $localidad = "";
    $provincia = "";
    $telefono = "";
    $rol = "cliente";
}

$dniBloqueado = $modoEdicion;
// El administrador no puede quitarse a sí mismo el rol de admin (se quedaría sin acceso)
$editandoseASiMismo = $modoEdicion && $DNI === dniActual();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!$modoEdicion) {
        $DNI = strtoupper(obtenerDato("DNI"));
    }
    $nombre = obtenerDato("nombre");
    $email = obtenerDato("email");
    $direccion = obtenerDato("direccion");
    $localidad = obtenerDato("localidad");
    $provincia = obtenerDato("provincia");
    $telefono = obtenerDato("telefono");
    if (!$editandoseASiMismo) {
        $rol = obtenerDato("rol");
    }
    $clave = $_POST["clave"] ?? "";

    $errores = validarDatosUsuario($DNI, $nombre, $email, $telefono, !$modoEdicion);

    if (!in_array($rol, ROLES, true)) {
        $errores[] = "Rol no válido.";
    }
    // Al crear, la contraseña es obligatoria; al editar, solo se cambia si se escribe una
    if (!$modoEdicion && $clave === "") {
        $errores[] = "Debe introducir una contraseña.";
    }

    if (empty($errores)) {
        if ($modoEdicion) {
            actualizarUsuario($DNI, $nombre, $email, $direccion, $localidad, $provincia, $telefono, $rol);
            if ($clave !== "") {
                actualizarClave($DNI, password_hash($clave, PASSWORD_DEFAULT));
            }
            guardarMensaje("Usuario $DNI modificado correctamente.");
        } else {
            insertarUsuario($DNI, $nombre, $email, $direccion, $localidad, $provincia, $telefono,
                password_hash($clave, PASSWORD_DEFAULT), $rol);
            guardarMensaje("Usuario $DNI creado correctamente.");
        }
        header("Location: listadoUsuarios.php");
        exit;
    }
}

$titulo = $modoEdicion ? "Editar usuario" : "Nuevo usuario";
require __DIR__ . "/../includes/cabecera.php";
?>
<section class="tarjeta formulario">
    <h1><?= $modoEdicion ? "Editar usuario" : "Nuevo usuario" ?></h1>
    <p class="nota">Los campos marcados con (*) son obligatorios.</p>

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
                <label for="rol">Rol (*):</label>
                <select name="rol" id="rol" <?= $editandoseASiMismo ? "disabled" : "" ?>>
                    <?php foreach (ROLES as $opcion) : ?>
                        <option value="<?= $opcion ?>" <?= $opcion === $rol ? "selected" : "" ?>><?= $opcion ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="campo">
                <label for="clave">Contraseña<?= $modoEdicion ? "" : " (*)" ?>:</label>
                <input type="password" name="clave" id="clave">
            </div>
        </div>

        <?php if ($editandoseASiMismo) : ?>
            <p class="nota">No puedes cambiar tu propio rol de administrador.</p>
        <?php endif ?>
        <?php if ($modoEdicion) : ?>
            <p class="nota">Deja la contraseña en blanco para mantener la actual.</p>
        <?php endif ?>

        <div class="botones">
            <a href="listadoUsuarios.php" class="boton secundario">Cancelar</a>
            <button type="submit" class="boton"><?= $modoEdicion ? "Guardar cambios" : "Crear usuario" ?></button>
        </div>
    </form>
</section>
<?php require __DIR__ . "/../includes/pie.php"; ?>
