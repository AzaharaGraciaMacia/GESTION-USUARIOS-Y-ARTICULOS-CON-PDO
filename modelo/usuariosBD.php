<?php
// Funciones de acceso a la tabla usuarios.
// Todas usan consultas preparadas y las que leen usuarios devuelven objetos de la clase Usuario.

require_once __DIR__ . "/../conectar_db.php";
require_once __DIR__ . "/../funcionesValidacion.php";
require_once __DIR__ . "/../clases/Usuario.php";

// Columnas por las que se permite ordenar el listado.
// ORDER BY no admite parámetros en una consulta preparada, así que solo se aceptan
// los nombres de esta lista (lista blanca) para evitar inyección SQL.
const ORDEN_USUARIOS = ["dni", "nombre", "email", "localidad", "rol"];

// Registra el error real en el log y muestra un mensaje genérico al usuario.
// El tipo "never" indica que la función nunca vuelve (termina con die).
function errorBD(PDOException $e, string $mensaje): never
{
    error_log("Error PDO: " . $e->getMessage());
    die($mensaje);
}

// Devuelve el usuario con ese DNI o null si no existe
function buscarUsuarioDni($DNI): ?Usuario
{
    try {
        $con = conectar();
        $stmt = $con->prepare("SELECT * FROM usuarios WHERE dni = :dni");
        $stmt->execute([":dni" => $DNI]);
        $fila = $stmt->fetch();
        return $fila ? new Usuario($fila) : null;
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al recuperar datos de la base de datos.");
    }
}

// Función para comprobar si ya existe un usuario con ese DNI
function comprobarUsuario($DNI): bool
{
    return buscarUsuarioDni($DNI) !== null;
}

// Función para comprobar si ya existe un usuario con ese email.
// $dniExcluido sirve al editar: el email del propio usuario no cuenta como repetido.
function comprobarEmail($email, $dniExcluido = ""): bool
{
    try {
        $con = conectar();
        $stmt = $con->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email AND dni <> :dni");
        $stmt->execute([":email" => $email, ":dni" => $dniExcluido]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al recuperar datos de la base de datos.");
    }
}

// Comprueba que el DNI y el email pertenecen al mismo usuario (para recuperar la contraseña)
function comprobarDniEmail($DNI, $email): bool
{
    try {
        $con = conectar();
        $stmt = $con->prepare("SELECT COUNT(*) FROM usuarios WHERE dni = :dni AND email = :email");
        $stmt->execute([":dni" => $DNI, ":email" => $email]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al recuperar datos de la base de datos.");
    }
}

// Validaciones comunes de los formularios de usuario (registro, perfil y gestión del admin).
// $esNuevo = true en las altas: entonces también se valida el DNI y que no esté repetido.
// Devuelve un array con los mensajes de error (vacío si todo es correcto).
function validarDatosUsuario($DNI, $nombre, $email, $telefono, bool $esNuevo): array
{
    $errores = [];

    if ($DNI === "" || $nombre === "" || $email === "") {
        $errores[] = "Debe rellenar todos los campos obligatorios (DNI, nombre, email).";
        return $errores;
    }

    if ($esNuevo) {
        if (!validarDNI($DNI)) {
            $errores[] = "DNI no válido.";
        } elseif (comprobarUsuario($DNI)) {
            $errores[] = "El DNI ya ha sido registrado.";
        }
    }

    if (!validarEmail($email)) {
        $errores[] = "Email no válido.";
    } elseif (comprobarEmail($email, $esNuevo ? "" : $DNI)) {
        $errores[] = "El email ya se ha registrado.";
    }

    // El teléfono es opcional: solo se valida si se ha escrito algo
    if ($telefono !== "" && !validarTlf($telefono)) {
        $errores[] = "Teléfono no válido (deben ser 9 dígitos).";
    }

    return $errores;
}

// Función con la query para insertar los datos en la BD. $clave ya debe venir cifrada con password_hash
function insertarUsuario($DNI, $nombre, $email, $direccion, $localidad, $provincia, $telefono, $clave, $rol): void
{
    try {
        $con = conectar();
        $sql = "INSERT INTO usuarios (dni, nombre, direccion, localidad, provincia, telefono, email, clave, rol)
                VALUES (:dni, :nombre, :direccion, :localidad, :provincia, :telefono, :email, :clave, :rol)";
        $stmt = $con->prepare($sql);
        $stmt->execute([":dni" => $DNI, ":nombre" => $nombre, ":direccion" => $direccion,
            ":localidad" => $localidad, ":provincia" => $provincia, ":telefono" => $telefono,
            ":email" => $email, ":clave" => $clave, ":rol" => $rol]);
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al insertar en la base de datos.");
    }
}

// Actualiza los datos de un usuario (el DNI no se modifica nunca, es la clave primaria)
function actualizarUsuario($DNI, $nombre, $email, $direccion, $localidad, $provincia, $telefono, $rol): void
{
    try {
        $con = conectar();
        $sql = "UPDATE usuarios
                SET nombre = :nombre, direccion = :direccion, localidad = :localidad, provincia = :provincia,
                    telefono = :telefono, email = :email, rol = :rol
                WHERE dni = :dni";
        $stmt = $con->prepare($sql);
        $stmt->execute([":dni" => $DNI, ":nombre" => $nombre, ":direccion" => $direccion,
            ":localidad" => $localidad, ":provincia" => $provincia, ":telefono" => $telefono,
            ":email" => $email, ":rol" => $rol]);
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al actualizar la base de datos.");
    }
}

// Cambia la contraseña de un usuario. $clave ya debe venir cifrada con password_hash
function actualizarClave($DNI, $clave): void
{
    try {
        $con = conectar();
        $stmt = $con->prepare("UPDATE usuarios SET clave = :clave WHERE dni = :dni");
        $stmt->execute([":dni" => $DNI, ":clave" => $clave]);
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al actualizar la base de datos.");
    }
}

// Borra un usuario por su DNI
function borrarUsuario($DNI): void
{
    try {
        $con = conectar();
        $stmt = $con->prepare("DELETE FROM usuarios WHERE dni = :dni");
        $stmt->execute([":dni" => $DNI]);
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al borrar en la base de datos.");
    }
}

// Cuenta los usuarios que coinciden con la búsqueda (para calcular el número de páginas)
function contarUsuarios($busqueda): int
{
    try {
        $con = conectar();
        $stmt = $con->prepare("SELECT COUNT(*) FROM usuarios
                               WHERE CONCAT_WS(' ', dni, nombre, email, localidad, provincia, rol) LIKE :busqueda");
        $stmt->execute([":busqueda" => "%" . $busqueda . "%"]);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al recuperar datos de la base de datos.");
    }
}

// Devuelve una página de usuarios como array de objetos Usuario.
// $busqueda: texto a buscar en DNI, nombre, email, localidad, provincia o rol.
// $orden y $direccion ya deben venir validados contra ORDEN_USUARIOS y ASC/DESC.
function listarUsuarios($busqueda, $orden, $direccion, $limite, $desplazamiento): array
{
    try {
        $con = conectar();
        $sql = "SELECT * FROM usuarios
                WHERE CONCAT_WS(' ', dni, nombre, email, localidad, provincia, rol) LIKE :busqueda
                ORDER BY $orden $direccion
                LIMIT :limite OFFSET :desplazamiento";
        $stmt = $con->prepare($sql);
        $stmt->bindValue(":busqueda", "%" . $busqueda . "%");
        $stmt->bindValue(":limite", $limite, PDO::PARAM_INT);
        $stmt->bindValue(":desplazamiento", $desplazamiento, PDO::PARAM_INT);
        $stmt->execute();

        // Mapeo a objetos: cada fila se convierte en un objeto Usuario
        $usuarios = [];
        foreach ($stmt->fetchAll() as $fila) {
            $usuarios[] = new Usuario($fila);
        }
        return $usuarios;
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al recuperar datos de la base de datos.");
    }
}
