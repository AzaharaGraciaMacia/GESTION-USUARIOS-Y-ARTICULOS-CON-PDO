<?php
// Clase que representa una fila de la tabla usuarios.
// Las consultas de usuariosBD.php devuelven objetos de esta clase en lugar de arrays.

class Usuario
{
    private string $dni;
    private string $nombre;
    private string $direccion;
    private string $localidad;
    private string $provincia;
    private string $telefono;
    private string $email;
    private string $clave;
    private string $rol;

    // Recibe una fila de la BD (array asociativo) y rellena las propiedades.
    // Los campos opcionales pueden venir como NULL, por eso se usa ?? "".
    public function __construct(array $fila)
    {
        $this->dni = $fila["dni"];
        $this->nombre = $fila["nombre"];
        $this->direccion = $fila["direccion"] ?? "";
        $this->localidad = $fila["localidad"] ?? "";
        $this->provincia = $fila["provincia"] ?? "";
        $this->telefono = $fila["telefono"] ?? "";
        $this->email = $fila["email"];
        $this->clave = $fila["clave"] ?? "";
        $this->rol = $fila["rol"];
    }

    public function getDni(): string
    {
        return $this->dni;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDireccion(): string
    {
        return $this->direccion;
    }

    public function getLocalidad(): string
    {
        return $this->localidad;
    }

    public function getProvincia(): string
    {
        return $this->provincia;
    }

    public function getTelefono(): string
    {
        return $this->telefono;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRol(): string
    {
        return $this->rol;
    }

    // Comprueba la contraseña escrita en el login contra el hash guardado en la BD
    public function comprobarClave(string $clave): bool
    {
        return password_verify($clave, $this->clave);
    }

    public function esAdmin(): bool
    {
        return $this->rol === "admin";
    }
}
