<?php
// Baja de la propia cuenta. Permitida para clientes y editores, NO para el administrador.
// Por GET muestra la confirmación; por POST borra el usuario y cierra la sesión.
require_once __DIR__ . "/../seguridad.php";
exigirRol(["cliente", "editor"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    borrarUsuario(dniActual());
    cerrarSesion();
    header("Location: ../Autenticacion/login.php?baja=1");
    exit;
}

$titulo = "Darse de baja";
require __DIR__ . "/../includes/cabecera.php";
?>
<section class="tarjeta formulario">
    <h1>Dar de baja mi cuenta</h1>
    <p>
        ¿Seguro que quieres borrar tu cuenta (DNI <strong><?= textoProtegido(dniActual()) ?></strong>)?
        Se eliminarán todos tus datos y se cerrará la sesión. Esta acción no se puede deshacer.
    </p>

    <form method="post">
        <div class="botones">
            <a href="perfil.php" class="boton secundario">Cancelar</a>
            <button type="submit" class="boton peligro">Sí, darme de baja</button>
        </div>
    </form>
</section>
<?php require __DIR__ . "/../includes/pie.php"; ?>
