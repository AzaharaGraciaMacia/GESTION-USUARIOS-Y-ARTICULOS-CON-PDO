<?php
// Alta y modificación de artículos (editor y administrador).
//   formularioArticulos.php                 -> alta
//   formularioArticulos.php?codigo=PRO10001 -> modificación (el código no se puede cambiar)
require_once __DIR__ . "/../seguridad.php";
require_once __DIR__ . "/../modelo/articulosBD.php";
require_once __DIR__ . "/ttoImagenes.php";
exigirRol(["editor", "admin"]);

$modoEdicion = isset($_GET["codigo"]);
$errores = [];

if ($modoEdicion) {
    $articulo = buscarArticuloPorCodigo(obtenerDatoGet("codigo"));
    if ($articulo === null) {
        guardarMensaje("El artículo que intentas editar no existe.", "error");
        header("Location: listadoArticulos.php");
        exit;
    }
    $codigo = $articulo->getCodigo();
    $nombre = $articulo->getNombre();
    $descripcion = $articulo->getDescripcion();
    $categoria = $articulo->getCategoria();
    $precio = number_format($articulo->getPrecio(), 2, ".", "");
    $imagenActual = $articulo->getImagen();
} else {
    $codigo = "";
    $nombre = "";
    $descripcion = "";
    $categoria = "";
    $precio = "";
    $imagenActual = "";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // En edición el código NO se lee del formulario: es el del artículo que se está editando
    if (!$modoEdicion) {
        $codigo = strtoupper(obtenerDato("codigo"));
    }
    $nombre = obtenerDato("nombre");
    $descripcion = obtenerDato("descripcion");
    $categoria = obtenerDato("categoria");
    $precio = obtenerDato("precio");
    $quitarImagen = isset($_POST["quitar_imagen"]);

    // Validaciones
    if ($codigo === "" || $nombre === "" || $categoria === "" || $precio === "") {
        $errores[] = "Debe rellenar todos los campos obligatorios (código, nombre, categoría y precio).";
    } else {
        if (!$modoEdicion) {
            if (!validarCodigoArticulo($codigo)) {
                $errores[] = "Código no válido: deben ser 3 letras mayúsculas y hasta 5 números (ej: PRO10001).";
            } elseif (buscarArticuloPorCodigo($codigo) !== null) {
                $errores[] = "Ya existe un artículo con el código $codigo.";
            }
        }
        if (!validarPrecio($precio)) {
            $errores[] = "Precio no válido: debe ser un número entre 0 y 999999,99.";
        }
    }

    $hayImagenNueva = hayImagenSubida("imagen");
    if ($hayImagenNueva) {
        $errores = array_merge($errores, validarImagen("imagen"));
    }

    if (empty($errores)) {
        $precioBD = str_replace(",", ".", $precio);

        // Imagen que se guardará en la BD
        $imagenFinal = $imagenActual;
        if ($hayImagenNueva) {
            $imagenFinal = guardarImagen("imagen", $codigo);
            if ($imagenFinal === null) {
                $errores[] = "No se ha podido guardar la imagen en el servidor.";
            }
        } elseif ($quitarImagen) {
            $imagenFinal = "";
        }

        if (empty($errores)) {
            // Si la imagen ha cambiado, se borra el archivo antiguo del disco
            if ($imagenActual !== "" && $imagenFinal !== $imagenActual) {
                borrarImagen($imagenActual);
            }
            // En la BD se guarda NULL si no hay imagen
            $imagenBD = $imagenFinal === "" ? null : $imagenFinal;

            if ($modoEdicion) {
                actualizarArticulo($codigo, $nombre, $descripcion, $categoria, $precioBD, $imagenBD);
                guardarMensaje("Artículo $codigo modificado correctamente.");
            } else {
                insertarArticulo($codigo, $nombre, $descripcion, $categoria, $precioBD, $imagenBD);
                guardarMensaje("Artículo $codigo creado correctamente.");
            }
            header("Location: listadoArticulos.php");
            exit;
        }
    }
}

$categorias = listarCategorias();
$titulo = $modoEdicion ? "Editar artículo" : "Nuevo artículo";
require __DIR__ . "/../includes/cabecera.php";
?>
<section class="tarjeta formulario">
    <h1><?= $modoEdicion ? "Editar artículo" : "Nuevo artículo" ?></h1>
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

    <!-- enctype multipart/form-data es necesario para poder enviar archivos ($_FILES) -->
    <form method="post" enctype="multipart/form-data">
        <div class="rejilla">
            <div class="campo">
                <label for="codigo">Código (*):</label>
                <?php if ($modoEdicion) : ?>
                    <input type="text" id="codigo" value="<?= textoProtegido($codigo) ?>" disabled>
                <?php else : ?>
                    <input type="text" name="codigo" id="codigo" value="<?= textoProtegido($codigo) ?>"
                           placeholder="Ej: PRO10001" maxlength="8">
                <?php endif ?>
            </div>

            <div class="campo">
                <label for="precio">Precio (€) (*):</label>
                <input type="text" name="precio" id="precio" value="<?= textoProtegido($precio) ?>" placeholder="Ej: 19.95">
            </div>

            <div class="campo completo">
                <label for="nombre">Nombre (*):</label>
                <input type="text" name="nombre" id="nombre" value="<?= textoProtegido($nombre) ?>">
            </div>

            <div class="campo completo">
                <label for="categoria">Categoría (*):</label>
                <!-- datalist: sugiere las categorías que ya existen, pero permite escribir una nueva -->
                <input type="text" name="categoria" id="categoria" list="lista-categorias"
                       value="<?= textoProtegido($categoria) ?>">
                <datalist id="lista-categorias">
                    <?php foreach ($categorias as $opcion) : ?>
                        <option value="<?= textoProtegido($opcion) ?>">
                    <?php endforeach ?>
                </datalist>
            </div>

            <div class="campo completo">
                <label for="descripcion">Descripción:</label>
                <textarea name="descripcion" id="descripcion" rows="3"><?= textoProtegido($descripcion) ?></textarea>
            </div>

            <div class="campo completo">
                <label for="imagen">Imagen:</label>
                <?php if ($imagenActual !== "") : ?>
                    <div class="imagen-actual">
                        <img src="../<?= textoProtegido($imagenActual) ?>" alt="Imagen actual" class="miniatura">
                        <label class="casilla">
                            <input type="checkbox" name="quitar_imagen"> Quitar la imagen actual
                        </label>
                    </div>
                <?php endif ?>
                <input type="file" name="imagen" id="imagen" accept=".jpg,.jpeg,.gif,.png">
                <p class="nota">JPG, JPEG, GIF o PNG. Máximo 300 KB y 200x200 píxeles.</p>
            </div>
        </div>

        <div class="botones">
            <a href="listadoArticulos.php" class="boton secundario">Cancelar</a>
            <button type="submit" class="boton"><?= $modoEdicion ? "Guardar cambios" : "Crear artículo" ?></button>
        </div>
    </form>
</section>
<?php require __DIR__ . "/../includes/pie.php"; ?>
