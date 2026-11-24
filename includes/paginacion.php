<?php
// Funciones comunes para los listados: búsqueda, ordenación y paginación.
// Los listados reciben por GET: búsqueda, orden, ASC/DESC y página.

require_once __DIR__ . "/../funcionesValidacion.php";

// Lee la columna y la dirección de orden de la URL.
// Si la columna no está en la lista de permitidas se usa la de por defecto (evita inyección SQL en ORDER BY)
function leerOrden(array $columnasPermitidas, string $columnaPorDefecto): array
{
    $orden = obtenerDatoGet("orden");
    if (!in_array($orden, $columnasPermitidas, true)) {
        $orden = $columnaPorDefecto;
    }
    $direccion = strtoupper(obtenerDatoGet("dir")) === "DESC" ? "DESC" : "ASC";
    return [$orden, $direccion];
}

// Calcula la página actual, el total de páginas y el desplazamiento (OFFSET) para la consulta
function calcularPaginacion(int $totalRegistros, int $porPagina): array
{
    $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));

    $pagina = (int) obtenerDatoGet("pagina");
    if ($pagina < 1) {
        $pagina = 1;
    }
    if ($pagina > $totalPaginas) {
        $pagina = $totalPaginas;
    }

    $desplazamiento = ($pagina - 1) * $porPagina;
    return [$pagina, $totalPaginas, $desplazamiento];
}

// Construye la URL del listado manteniendo la búsqueda y el orden
function urlListado(string $busqueda, string $orden, string $direccion, int $pagina = 1): string
{
    return "?" . http_build_query([
        "busqueda" => $busqueda,
        "orden" => $orden,
        "dir" => $direccion,
        "pagina" => $pagina,
    ]);
}

// Pinta la cabecera de una columna como enlace para ordenar.
// Si ya se está ordenando por esa columna, al pulsar se invierte la dirección.
function enlaceOrden(string $columna, string $texto, string $busqueda, string $ordenActual, string $direccionActual): void
{
    $nuevaDireccion = ($ordenActual === $columna && $direccionActual === "ASC") ? "DESC" : "ASC";
    $flecha = "";
    if ($ordenActual === $columna) {
        $flecha = $direccionActual === "ASC" ? " ▲" : " ▼";
    }
    $url = urlListado($busqueda, $columna, $nuevaDireccion);
    echo '<a href="' . textoProtegido($url) . '">' . textoProtegido($texto) . $flecha . '</a>';
}

// Pinta los enlaces de paginación: « Anterior  1 2 3  Siguiente »
function pintarPaginacion(int $pagina, int $totalPaginas, string $busqueda, string $orden, string $direccion): void
{
    if ($totalPaginas <= 1) {
        return;
    }

    echo '<nav class="paginacion">';

    if ($pagina > 1) {
        echo '<a href="' . textoProtegido(urlListado($busqueda, $orden, $direccion, $pagina - 1)) . '">« Anterior</a>';
    }

    for ($i = 1; $i <= $totalPaginas; $i++) {
        if ($i === $pagina) {
            echo '<span class="actual">' . $i . '</span>';
        } else {
            echo '<a href="' . textoProtegido(urlListado($busqueda, $orden, $direccion, $i)) . '">' . $i . '</a>';
        }
    }

    if ($pagina < $totalPaginas) {
        echo '<a href="' . textoProtegido(urlListado($busqueda, $orden, $direccion, $pagina + 1)) . '">Siguiente »</a>';
    }

    echo '</nav>';
}
