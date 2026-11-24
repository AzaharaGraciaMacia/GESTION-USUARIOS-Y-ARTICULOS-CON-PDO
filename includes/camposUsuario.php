<?php
// Campos comunes de los formularios de usuario (registro, mi perfil y gestión del administrador).
// Usa las variables de la página que lo incluye: $DNI, $nombre, $email, $direccion, $localidad,
// $provincia, $telefono y $dniBloqueado (true al editar: el DNI no se puede cambiar).
// Todos los valores se escapan con textoProtegido() al mostrarlos.
?>
<div class="campo">
    <label for="DNI">DNI (*):</label>
    <?php if ($dniBloqueado) : ?>
        <input type="text" id="DNI" value="<?= textoProtegido($DNI) ?>" disabled>
    <?php else : ?>
        <input type="text" name="DNI" id="DNI" value="<?= textoProtegido($DNI) ?>" placeholder="Ej: 12345678Z">
    <?php endif ?>
</div>

<div class="campo">
    <label for="telefono">Teléfono:</label>
    <input type="text" name="telefono" id="telefono" value="<?= textoProtegido($telefono) ?>" placeholder="Ej: 695439149">
</div>

<div class="campo completo">
    <label for="nombre">Nombre completo (*):</label>
    <input type="text" name="nombre" id="nombre" value="<?= textoProtegido($nombre) ?>" placeholder="Ej: Azahara Gracia Maciá">
</div>

<div class="campo completo">
    <label for="email">Correo electrónico (*):</label>
    <input type="email" name="email" id="email" value="<?= textoProtegido($email) ?>" placeholder="Ej: azagramac@alu.edu.gva.es">
</div>

<div class="campo completo">
    <label for="direccion">Dirección:</label>
    <input type="text" name="direccion" id="direccion" value="<?= textoProtegido($direccion) ?>" placeholder="Ej: C/Concepción Arenal">
</div>

<div class="campo">
    <label for="localidad">Localidad:</label>
    <input type="text" name="localidad" id="localidad" value="<?= textoProtegido($localidad) ?>" placeholder="Ej: Elche">
</div>

<div class="campo">
    <label for="provincia">Provincia:</label>
    <input type="text" name="provincia" id="provincia" value="<?= textoProtegido($provincia) ?>" placeholder="Ej: Alicante">
</div>
