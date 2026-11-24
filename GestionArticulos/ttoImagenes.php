<?php
// Tratamiento de las imágenes de los artículos (subida con $_FILES).
// Requisitos: formatos JPG, JPEG, GIF y PNG; tamaño máximo 300 KB; dimensiones máximas 200x200 px;
// se guardan en uploads/articulos/ con un nombre único para evitar colisiones.

const CARPETA_IMAGENES = "uploads/articulos/";        // ruta relativa a la raíz del proyecto (se guarda en la BD)
const TAMANO_MAXIMO_IMAGEN = 300 * 1024;               // 300 KB en bytes
const ANCHO_MAXIMO_IMAGEN = 200;
const ALTO_MAXIMO_IMAGEN = 200;
const EXTENSIONES_PERMITIDAS = ["jpg", "jpeg", "gif", "png"];
// Tipos reales de imagen que devuelve getimagesize()
const TIPOS_IMAGEN_PERMITIDOS = [IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_PNG];

// Indica si en el formulario se ha elegido un archivo
function hayImagenSubida(string $campo): bool
{
    return isset($_FILES[$campo]) && $_FILES[$campo]["error"] !== UPLOAD_ERR_NO_FILE;
}

// Comprueba la imagen subida y devuelve un array con los errores (vacío si es válida).
// No mueve el archivo: así, si hay errores en otros campos del formulario, no quedan imágenes sueltas.
function validarImagen(string $campo): array
{
    $errores = [];
    $archivo = $_FILES[$campo];

    // Errores de la propia subida
    if ($archivo["error"] === UPLOAD_ERR_INI_SIZE || $archivo["error"] === UPLOAD_ERR_FORM_SIZE) {
        return ["La imagen supera el tamaño máximo permitido (300 KB)."];
    }
    if ($archivo["error"] !== UPLOAD_ERR_OK) {
        return ["Error al subir la imagen. Inténtalo de nuevo."];
    }

    // Extensión del nombre del archivo
    $extension = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));
    if (!in_array($extension, EXTENSIONES_PERMITIDAS, true)) {
        $errores[] = "Formato de imagen no permitido. Solo JPG, JPEG, GIF o PNG.";
    }

    // Tamaño en bytes
    if ($archivo["size"] > TAMANO_MAXIMO_IMAGEN) {
        $errores[] = "La imagen supera el tamaño máximo permitido (300 KB).";
    }

    // getimagesize() lee el contenido del archivo: comprueba que de verdad es una imagen
    // (no basta con la extensión, se podría renombrar cualquier archivo a .jpg) y da sus dimensiones
    $datosImagen = @getimagesize($archivo["tmp_name"]);
    if ($datosImagen === false || !in_array($datosImagen[2], TIPOS_IMAGEN_PERMITIDOS, true)) {
        $errores[] = "El archivo no es una imagen válida.";
    } else {
        [$ancho, $alto] = $datosImagen;
        if ($ancho > ANCHO_MAXIMO_IMAGEN || $alto > ALTO_MAXIMO_IMAGEN) {
            $errores[] = "La imagen mide {$ancho}x{$alto} px. El máximo es "
                . ANCHO_MAXIMO_IMAGEN . "x" . ALTO_MAXIMO_IMAGEN . " px.";
        }
    }

    return $errores;
}

// Mueve la imagen (ya validada) a la carpeta de destino con un nombre único.
// Devuelve la ruta relativa que se guarda en la BD (ej: uploads/articulos/PRO10001_5f3a9c1b2d4e6f70.png)
function guardarImagen(string $campo, string $codigoArticulo): ?string
{
    $archivo = $_FILES[$campo];

    // La extensión se saca del tipo REAL de la imagen (no del nombre que trae el archivo),
    // así un PNG renombrado a .gif se guarda como .png
    $tipoReal = getimagesize($archivo["tmp_name"])[2];
    $extension = image_type_to_extension($tipoReal, false);   // "jpeg", "png" o "gif"

    // Nombre único: código del artículo + 16 caracteres aleatorios
    $nombreArchivo = $codigoArticulo . "_" . bin2hex(random_bytes(8)) . "." . $extension;

    $carpetaDestino = __DIR__ . "/../" . CARPETA_IMAGENES;
    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0755, true);
    }

    if (!move_uploaded_file($archivo["tmp_name"], $carpetaDestino . $nombreArchivo)) {
        return null;
    }
    return CARPETA_IMAGENES . $nombreArchivo;
}

// Borra del disco una imagen guardada (al reemplazarla o al borrar el artículo).
// Solo borra archivos de la carpeta de imágenes, por seguridad.
function borrarImagen(string $ruta): void
{
    if ($ruta === "" || !str_starts_with($ruta, CARPETA_IMAGENES)) {
        return;
    }
    $rutaCompleta = __DIR__ . "/../" . $ruta;
    if (is_file($rutaCompleta)) {
        unlink($rutaCompleta);
    }
}
