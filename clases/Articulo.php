<?php
// Clase que representa una fila de la tabla articulos.
// Las consultas de articulosBD.php devuelven objetos de esta clase en lugar de arrays.

class Articulo
{
    private string $codigo;
    private string $nombre;
    private string $descripcion;
    private string $categoria;
    private float $precio;
    private string $imagen;

    // Recibe una fila de la BD (array asociativo) y rellena las propiedades
    public function __construct(array $fila)
    {
        $this->codigo = $fila["codigo"];
        $this->nombre = $fila["nombre"];
        $this->descripcion = $fila["descripcion"] ?? "";
        $this->categoria = $fila["categoria"];
        $this->precio = (float) $fila["precio"];
        $this->imagen = $fila["imagen"] ?? "";
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function getCategoria(): string
    {
        return $this->categoria;
    }

    public function getPrecio(): float
    {
        return $this->precio;
    }

    // Precio con formato español: 1.234,50 €
    public function getPrecioFormateado(): string
    {
        return number_format($this->precio, 2, ",", ".") . " €";
    }

    // Ruta relativa de la imagen (ej: uploads/articulos/a1b2c3.png) o "" si no tiene
    public function getImagen(): string
    {
        return $this->imagen;
    }

    public function tieneImagen(): bool
    {
        return $this->imagen !== "";
    }
}
