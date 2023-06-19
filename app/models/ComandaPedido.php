<?php

class ComandaPedido{
    public $id;
    public $comandaId;
    public $articuloCodigo;
    public $pedidoEstado; // 0 En espera  - 1 En preparacion - 2 Listo para servir
    public $articuloCantidad;
    public $pedidoSector;
    public $pedidoTiempoEstimado;
    public $pedidoFechaAlta;
    public $pedidoFechaFin;
}