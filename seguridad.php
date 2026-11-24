<?php
// Control de sesión y de acceso por roles (RBAC).
// Todas las páginas lo incluyen al principio, antes de escribir nada de HTML.

require_once __DIR__ . "/modelo/usuariosBD.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Página a la que se va tras iniciar sesión (y cuando no se tiene permiso para algo)
const PAGINA_INICIO = "../GestionArticulos/listadoArticulos.php";
const PAGINA_LOGIN = "../Autenticacion/login.php";

// Guarda en la sesión los datos del usuario que acaba de iniciar sesión
function iniciarSesionUsuario(Usuario $usuario): void
{
    // Se genera un identificador de sesión nuevo para evitar el robo de sesión
    session_regenerate_id(true);
    $_SESSION["dni"] = $usuario->getDni();
    $_SESSION["nombre"] = $usuario->getNombre();
    $_SESSION["rol"] = $usuario->getRol();
}

function usuarioLogueado(): bool
{
    return isset($_SESSION["dni"]);
}

function dniActual(): string
{
    return $_SESSION["dni"] ?? "";
}

function rolActual(): string
{
    return $_SESSION["rol"] ?? "";
}

/* Para páginas que necesitan sesión iniciada, vuelve a leer el usuario de la BD:
 * si un admin lo ha borrado o le ha cambiado el rol mientras estaba conectado,
 * el cambio se aplica en la siguiente página que cargue.
 */
function exigirLogin(): void
{
    if (!usuarioLogueado()) {
        header("Location: " . PAGINA_LOGIN);
        exit;
    }

    $usuario = buscarUsuarioDni(dniActual());
    if ($usuario === null) {
        cerrarSesion();
        header("Location: " . PAGINA_LOGIN);
        exit;
    }
    $_SESSION["nombre"] = $usuario->getNombre();
    $_SESSION["rol"] = $usuario->getRol();
}

// Para páginas restringidas a ciertos roles, por ejemplo exigirRol(["admin"])
function exigirRol(array $roles): void
{
    exigirLogin();
    if (!in_array(rolActual(), $roles, true)) {
        guardarMensaje("No tienes permiso para acceder a esa página.", "error");
        header("Location: " . PAGINA_INICIO);
        exit;
    }
}

/* Mensajes "flash": se guardan antes de redirigir y se muestran una sola vez en la página siguiente.
 * $tipo puede ser "exito" o "error" (coincide con las clases .exito y .errores del CSS)
 */
function guardarMensaje(string $texto, string $tipo = "exito"): void
{
    $_SESSION["mensaje"] = $texto;
    $_SESSION["tipo_mensaje"] = $tipo;
}

// Devuelve el mensaje pendiente (o null) y lo borra de la sesión
function leerMensaje(): ?array
{
    if (!isset($_SESSION["mensaje"])) {
        return null;
    }
    $mensaje = ["texto" => $_SESSION["mensaje"], "tipo" => $_SESSION["tipo_mensaje"] ?? "exito"];
    unset($_SESSION["mensaje"], $_SESSION["tipo_mensaje"]);
    return $mensaje;
}

// Cierre de sesión seguro: vacía $_SESSION, borra la cookie de sesión y elimina la sesión
function cerrarSesion(): void
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $parametros = session_get_cookie_params();
        setcookie(session_name(), "", time() - 42000,
            $parametros["path"], $parametros["domain"],
            $parametros["secure"], $parametros["httponly"]);
    }

    session_destroy();
}
