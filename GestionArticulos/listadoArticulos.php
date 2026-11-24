<?php
// Catálogo de artículos. Todos los roles pueden ver, buscar, ordenar y paginar.
// Editor y administrador además pueden crear, editar y borrar.
require_once __DIR__ . "/../seguridad.php";
require_once __DIR__ . "/../modelo/articulosBD.php";
require_once __DIR__ . "/../includes/paginacion.php";
exigirLogin();

const ARTICULOS_POR_PAGINA = 6;

$puedeGestionar = in_array(rolActual(), ["editor", "admin"], true);

// Parámetros de la URL: búsqueda, orden y página
$busqueda = obtenerDatoGet("busqueda");
[$orden, $direccion] = leerOrden(ORDEN_ARTICULOS, "codigo");

$total = contarArticulos($busqueda);
[$pagina, $totalPaginas, $desplazamiento] = calcularPaginacion($total, ARTICULOS_POR_PAGINA);

// Array de objetos Articulo
$articulos = listarArticulos($busqueda, $orden, $direccion, ARTICULOS_POR_PAGINA, $desplazamiento);

$titulo = "Catálogo";
require __DIR__ . "/../includes/cabecera.php";
?>
<div class="titulo-seccion">
    <h1>Catálogo de artículos</h1>
    <?php if ($puedeGestionar) : ?>
        <a href="formularioArticulos.php" class="boton">+ Nuevo artículo</a>
    <?php endif ?>
</div>

<form method="get" class="buscador">
    <input type="search" name="busqueda" value="<?= textoProtegido($busqueda) ?>"
           placeholder="Buscar por código, nombre, descripción o categoría">
    <input type="hidden" name="orden" value="<?= textoProtegido($orden) ?>">
    <input type="hidden" name="dir" value="<?= textoProtegido($direccion) ?>">
    <button type="submit" class="boton">Buscar</button>
    <?php if ($busqueda !== "") : ?>
        <a href="listadoArticulos.php" class="boton secundario">Quitar filtro</a>
    <?php endif ?>
</form>

<p class="nota"><?= $total ?> artículo(s) encontrado(s). Pulsa en una columna para ordenar.</p>

<?php if (empty($articulos)) : ?>
    <div class="tarjeta vacio">No hay artículos que coincidan con la búsqueda.</div>
<?php else : ?>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
            <tr>
                <th>Imagen</th>
                <th><?php enlaceOrden("codigo", "Código", $busqueda, $orden, $direccion) ?></th>
                <th><?php enlaceOrden("nombre", "Nombre", $busqueda, $orden, $direccion) ?></th>
                <th><?php enlaceOrden("categoria", "Categoría", $busqueda, $orden, $direccion) ?></th>
                <th class="numero"><?php enlaceOrden("precio", "Precio", $busqueda, $orden, $direccion) ?></th>
                <?php if ($puedeGestionar) : ?>
                    <th>Acciones</th>
                <?php endif ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($articulos as $articulo) : ?>
                <tr>
                    <td>
                        <?php if ($articulo->tieneImagen()) : ?>
                            <img src="../<?= textoProtegido($articulo->getImagen()) ?>"
                                 alt="<?= textoProtegido($articulo->getNombre()) ?>" class="miniatura">
                        <?php else : ?>
                            <span class="miniatura sin-imagen">Sin imagen</span>
                        <?php endif ?>
                    </td>
                    <td><?= textoProtegido($articulo->getCodigo()) ?></td>
                    <td>
                        <strong><?= textoProtegido($articulo->getNombre()) ?></strong>
                        <div class="descripcion"><?= textoProtegido($articulo->getDescripcion()) ?></div>
                    </td>
                    <td><?= textoProtegido($articulo->getCategoria()) ?></td>
                    <td class="numero"><?= textoProtegido($articulo->getPrecioFormateado()) ?></td>
                    <?php if ($puedeGestionar) : ?>
                        <td class="acciones">
                            <a href="formularioArticulos.php?codigo=<?= urlencode($articulo->getCodigo()) ?>"
                               class="boton pequeno secundario">Editar</a>
                            <!-- Borrar va por POST para que no se pueda borrar solo con abrir una URL -->
                            <form method="post" action="borrarArticulo.php"
                                  onsubmit="return confirm('¿Seguro que quieres borrar este artículo?');">
                                <input type="hidden" name="codigo" value="<?= textoProtegido($articulo->getCodigo()) ?>">
                                <button type="submit" class="boton pequeno peligro">Borrar</button>
                            </form>
                        </td>
                    <?php endif ?>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <?php pintarPaginacion($pagina, $totalPaginas, $busqueda, $orden, $direccion) ?>
<?php endif ?>
<?php require __DIR__ . "/../includes/pie.php"; ?>
