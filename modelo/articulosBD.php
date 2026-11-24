<?php
// Funciones de acceso a la tabla articulos.
// Todas usan consultas preparadas y las que leen artículos devuelven objetos de la clase Articulo.

require_once __DIR__ . "/usuariosBD.php";   // trae conectar(), errorBD() y las validaciones
require_once __DIR__ . "/../clases/Articulo.php";

// Columnas por las que se permite ordenar el catálogo (lista blanca, ver ORDEN_USUARIOS)
const ORDEN_ARTICULOS = ["codigo", "nombre", "categoria", "precio"];

// Devuelve el artículo con ese código o null si no existe
function buscarArticuloPorCodigo($codigo): ?Articulo
{
    try {
        $con = conectar();
        $stmt = $con->prepare("SELECT * FROM articulos WHERE codigo = :codigo");
        $stmt->execute([":codigo" => $codigo]);
        $fila = $stmt->fetch();
        return $fila ? new Articulo($fila) : null;
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al recuperar datos de la base de datos.");
    }
}

// Inserta un artículo nuevo. $imagen es la ruta relativa o null si no tiene
function insertarArticulo($codigo, $nombre, $descripcion, $categoria, $precio, $imagen): void
{
    try {
        $con = conectar();
        $sql = "INSERT INTO articulos (codigo, nombre, descripcion, categoria, precio, imagen)
                VALUES (:codigo, :nombre, :descripcion, :categoria, :precio, :imagen)";
        $stmt = $con->prepare($sql);
        $stmt->execute([":codigo" => $codigo, ":nombre" => $nombre, ":descripcion" => $descripcion,
            ":categoria" => $categoria, ":precio" => $precio, ":imagen" => $imagen]);
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al insertar en la base de datos.");
    }
}

// Actualiza un artículo (el código no se modifica nunca)
function actualizarArticulo($codigo, $nombre, $descripcion, $categoria, $precio, $imagen): void
{
    try {
        $con = conectar();
        $sql = "UPDATE articulos
                SET nombre = :nombre, descripcion = :descripcion, categoria = :categoria,
                    precio = :precio, imagen = :imagen
                WHERE codigo = :codigo";
        $stmt = $con->prepare($sql);
        $stmt->execute([":codigo" => $codigo, ":nombre" => $nombre, ":descripcion" => $descripcion,
            ":categoria" => $categoria, ":precio" => $precio, ":imagen" => $imagen]);
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al actualizar la base de datos.");
    }
}

// Borra un artículo por su código
function borrarArticulo($codigo): void
{
    try {
        $con = conectar();
        $stmt = $con->prepare("DELETE FROM articulos WHERE codigo = :codigo");
        $stmt->execute([":codigo" => $codigo]);
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al borrar en la base de datos.");
    }
}

// Devuelve la lista de categorías distintas (para sugerirlas en el formulario)
function listarCategorias(): array
{
    try {
        $con = conectar();
        $stmt = $con->query("SELECT DISTINCT categoria FROM articulos ORDER BY categoria");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al recuperar datos de la base de datos.");
    }
}

// Cuenta los artículos que coinciden con la búsqueda (para calcular el número de páginas)
function contarArticulos($busqueda): int
{
    try {
        $con = conectar();
        $stmt = $con->prepare("SELECT COUNT(*) FROM articulos
                               WHERE CONCAT_WS(' ', codigo, nombre, descripcion, categoria) LIKE :busqueda");
        $stmt->execute([":busqueda" => "%" . $busqueda . "%"]);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al recuperar datos de la base de datos.");
    }
}

// Devuelve una página de artículos como array de objetos Articulo.
// $busqueda: texto a buscar en código, nombre, descripción o categoría.
// $orden y $direccion ya deben venir validados contra ORDEN_ARTICULOS y ASC/DESC.
function listarArticulos($busqueda, $orden, $direccion, $limite, $desplazamiento): array
{
    try {
        $con = conectar();
        $sql = "SELECT * FROM articulos
                WHERE CONCAT_WS(' ', codigo, nombre, descripcion, categoria) LIKE :busqueda
                ORDER BY $orden $direccion
                LIMIT :limite OFFSET :desplazamiento";
        $stmt = $con->prepare($sql);
        $stmt->bindValue(":busqueda", "%" . $busqueda . "%");
        $stmt->bindValue(":limite", $limite, PDO::PARAM_INT);
        $stmt->bindValue(":desplazamiento", $desplazamiento, PDO::PARAM_INT);
        $stmt->execute();

        // Mapeo a objetos: cada fila se convierte en un objeto Articulo
        $articulos = [];
        foreach ($stmt->fetchAll() as $fila) {
            $articulos[] = new Articulo($fila);
        }
        return $articulos;
    } catch (PDOException $e) {
        errorBD($e, "Ocurrió un error al recuperar datos de la base de datos.");
    }
}
