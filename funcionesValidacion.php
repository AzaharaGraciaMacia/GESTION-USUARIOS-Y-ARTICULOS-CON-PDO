<?php

// Constante para validación de DNI
const LETRAS_CONTROL = "TRWAGMYFPDXBNJZSQVHLCKE";

// Función para validar el DNI, pone la letra en mayúscula y comprueba que tiene patrón de 8 dígitos + 1 letra
// Después separa en dos cadenas el número y la letra, divide el número por 23 y busca con el resto la posición dentro de las letras de control
// Si la letra del DNI coincide con la letra en la posición del resto, el DNI es válido.
function validarDNI($DNI): bool
{
    if ($DNI === "") return false;

        if (!preg_match("/\A[0-9]{8}[A-Z]\z/",$DNI)) return false;

        $letra = substr($DNI, -1);
        $numeroDNI = (int) substr($DNI,0, -1);
        $letraEsperada = substr(LETRAS_CONTROL,$numeroDNI % 23,1);
        return $letra === $letraEsperada;
}

// Valida que el email sea correcto (algo@algo.algo)
function validarEmail($email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Función para proteger el texto al MOSTRARLO en el HTML (anti-XSS)
// Acepta null y números para poder usarla directamente con los datos de la BD
function textoProtegido($texto): string
{
    return htmlspecialchars((string) ($texto ?? ""), ENT_QUOTES, "UTF-8");
}

// Función para recibir datos de formularios enviados por POST (sin escapar: se escapa al mostrar)
function obtenerDato($dato): string
{
    $valor = $_POST[$dato] ?? "";
    return is_string($valor) ? trim($valor) : "";
}

// Igual que obtenerDato pero para los parámetros de la URL (GET): búsquedas, orden, página...
function obtenerDatoGet($dato): string
{
    $valor = $_GET[$dato] ?? "";
    return is_string($valor) ? trim($valor) : "";
}

// Valida que el teléfono tenga 9 dígitos
function validarTlf($telefono): bool
{
    if (!preg_match("/\A[0-9]{9}\z/",$telefono)) return false;
    return true;
}

// Valida el código de artículo: 3 letras mayúsculas seguidas de 1 a 5 números (ej: PRO10001)
function validarCodigoArticulo($codigo): bool
{
    return preg_match("/\A[A-Z]{3}[0-9]{1,5}\z/", $codigo) === 1;
}

// Valida el precio: número mayor o igual que 0 y que quepa en DECIMAL(8,2) (máximo 999999.99)
// Se admite la coma como separador decimal (12,50)
function validarPrecio($precio): bool
{
    $precio = str_replace(",", ".", $precio);
    if (!is_numeric($precio)) return false;
    return $precio >= 0 && $precio <= 999999.99;
}
?>