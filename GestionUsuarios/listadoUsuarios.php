<?php
// Listado de usuarios (solo administrador): buscar, ordenar, paginar, crear, editar y borrar.
require_once __DIR__ . "/../seguridad.php";
require_once __DIR__ . "/../includes/paginacion.php";
exigirRol(["admin"]);

const USUARIOS_POR_PAGINA = 5;

// Parámetros de la URL: búsqueda, orden y página
$busqueda = obtenerDatoGet("busqueda");
[$orden, $direccion] = leerOrden(ORDEN_USUARIOS, "nombre");

$total = contarUsuarios($busqueda);
[$pagina, $totalPaginas, $desplazamiento] = calcularPaginacion($total, USUARIOS_POR_PAGINA);

// Array de objetos Usuario
$usuarios = listarUsuarios($busqueda, $orden, $direccion, USUARIOS_POR_PAGINA, $desplazamiento);

$titulo = "Gestión de usuarios";
require __DIR__ . "/../includes/cabecera.php";
?>
<div class="titulo-seccion">
    <h1>Gestión de usuarios</h1>
    <a href="formularioUsuarios.php" class="boton">+ Nuevo usuario</a>
</div>

<form method="get" class="buscador">
    <input type="search" name="busqueda" value="<?= textoProtegido($busqueda) ?>"
           placeholder="Buscar por DNI, nombre, email, localidad o rol">
    <input type="hidden" name="orden" value="<?= textoProtegido($orden) ?>">
    <input type="hidden" name="dir" value="<?= textoProtegido($direccion) ?>">
    <button type="submit" class="boton">Buscar</button>
    <?php if ($busqueda !== "") : ?>
        <a href="listadoUsuarios.php" class="boton secundario">Quitar filtro</a>
    <?php endif ?>
</form>

<p class="nota"><?= $total ?> usuario(s) encontrado(s). Pulsa en una columna para ordenar.</p>

<?php if (empty($usuarios)) : ?>
    <div class="tarjeta vacio">No hay usuarios que coincidan con la búsqueda.</div>
<?php else : ?>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
            <tr>
                <th><?php enlaceOrden("dni", "DNI", $busqueda, $orden, $direccion) ?></th>
                <th><?php enlaceOrden("nombre", "Nombre", $busqueda, $orden, $direccion) ?></th>
                <th><?php enlaceOrden("email", "Email", $busqueda, $orden, $direccion) ?></th>
                <th>Teléfono</th>
                <th><?php enlaceOrden("localidad", "Localidad", $busqueda, $orden, $direccion) ?></th>
                <th><?php enlaceOrden("rol", "Rol", $busqueda, $orden, $direccion) ?></th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $usuario) : ?>
                <tr>
                    <td><?= textoProtegido($usuario->getDni()) ?></td>
                    <td><?= textoProtegido($usuario->getNombre()) ?></td>
                    <td><?= textoProtegido($usuario->getEmail()) ?></td>
                    <td><?= textoProtegido($usuario->getTelefono()) ?></td>
                    <td><?= textoProtegido($usuario->getLocalidad()) ?></td>
                    <td>
                        <span class="etiqueta rol-<?= textoProtegido($usuario->getRol()) ?>">
                            <?= textoProtegido($usuario->getRol()) ?>
                        </span>
                    </td>
                    <td class="acciones">
                        <a href="formularioUsuarios.php?dni=<?= urlencode($usuario->getDni()) ?>"
                           class="boton pequeno secundario">Editar</a>
                        <?php if ($usuario->getDni() !== dniActual()) : ?>
                            <!-- Borrar va por POST (no con un enlace) para que no se pueda borrar solo con abrir una URL -->
                            <form method="post" action="borrarUsuario.php"
                                  onsubmit="return confirm('¿Seguro que quieres borrar este usuario?');">
                                <input type="hidden" name="dni" value="<?= textoProtegido($usuario->getDni()) ?>">
                                <button type="submit" class="boton pequeno peligro">Borrar</button>
                            </form>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <?php pintarPaginacion($pagina, $totalPaginas, $busqueda, $orden, $direccion) ?>
<?php endif ?>
<?php require __DIR__ . "/../includes/pie.php"; ?>
