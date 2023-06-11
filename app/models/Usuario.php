<?php

require_once "./Enums/Rol.php";
require_once "./Enums/Sector.php";

class Usuario
{
    public $id;
    public $nombre;
    public $apellido;
    public $usuario;
    public $clave;
    public Rol $rol;
    public $activo;
    public Sector $sector;
    public $suspendido;

    public function __construct($nombre, $apellido, $usuario, $clave, $rol, $sector, $suspendido = 0)
    {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->usuario = $usuario;
        $this->clave = $clave;
        $this->rol = $rol;
        $this->sector = $sector;
        $this->suspendido = $suspendido;
    }
}
