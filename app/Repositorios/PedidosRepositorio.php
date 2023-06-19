<?php

require_once "./db/AccesoDatos.php";
require_once './models/ProductoPedido.php';
require_once "./Repositorios/ProductoRepositorio.php";
require_once "./Dtos/PedidoDTO.php";
require_once "./Dtos/ProductoPedidoDTO.php";

class PedidosRepositorio
{

    public static function ObtenerPedidosPorSector($sector)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT PEDIDOS_PRODUCTOS.ID AS id, PRODUCTOS.DESCRIPCION as descripcion, PEDIDOS_PRODUCTOS.PRODUCTO_CANTIDAD AS cantidad, PEDIDOS_PRODUCTOS.IMPORTE_PARCIAL as importeParcial, SECTORES.SEC_DESC as sector, PEDIDOS_PRODUCTOS_ESTADOS.DESCRIPCION as estado FROM PEDIDOS_PRODUCTOS INNER JOIN PRODUCTOS ON       PEDIDOS_PRODUCTOS.PRODUCTO_ID = PRODUCTOS.ID
                INNER JOIN SECTORES ON PEDIDOS_PRODUCTOS.SECTOR_CODIGO = SEC_CODIGO
                INNER JOIN PEDIDOS_PRODUCTOS_ESTADOS ON PEDIDOS_PRODUCTOS.ESTADO = PEDIDOS_PRODUCTOS_ESTADOS.ESTADO
                WHERE SEC_CODIGO = ? AND PEDIDOS_PRODUCTOS.ESTADO IN (0,1)");

        $query->bindParam(1, $sector);

        $query->execute();
        $productosPedido = $query->fetchAll(PDO::FETCH_CLASS, "ProductoPedidoDTO");
        return $productosPedido;
    }

    public static function ObtenerPedidosPreparados()
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT PEDIDOS_PRODUCTOS.ID AS id, PRODUCTOS.DESCRIPCION as descripcion, PEDIDOS_PRODUCTOS.PRODUCTO_CANTIDAD AS cantidad, PEDIDOS_PRODUCTOS.IMPORTE_PARCIAL as importeParcial, SECTORES.SEC_DESC as sector, PEDIDOS_PRODUCTOS_ESTADOS.DESCRIPCION as estado FROM PEDIDOS_PRODUCTOS INNER JOIN PRODUCTOS ON       PEDIDOS_PRODUCTOS.PRODUCTO_ID = PRODUCTOS.ID
                INNER JOIN SECTORES ON PEDIDOS_PRODUCTOS.SECTOR_CODIGO = SEC_CODIGO
                INNER JOIN PEDIDOS_PRODUCTOS_ESTADOS ON PEDIDOS_PRODUCTOS.ESTADO = PEDIDOS_PRODUCTOS_ESTADOS.ESTADO
                WHERE PEDIDOS_PRODUCTOS.ESTADO = 2");

        $query->execute();
        $productosPedido = $query->fetchAll(PDO::FETCH_CLASS, "ProductoPedidoDTO");
        return $productosPedido;
    }

    public static function ObtenerTodosLosPedidos()
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT PEDIDOS.ID AS pedidoId, USUARIOS.USU_NOMBRE as mozo, MESAS.CODIGO as mesaCodigo, PEDIDOS_ESTADOS.DESCRIPCION as estado, IMPORTE_FINAL as importeFinal, CLIENTE_NOMBRE as clienteNombre, PEDIDOS.FH_ALTA AS fechaAlta FROM PEDIDOS INNER JOIN USUARIOS ON PEDIDOS.MOZO_ID = USUARIOS.USU_ID INNER JOIN MESAS ON PEDIDOS.MESA_ID = MESAS.ID INNER JOIN PEDIDOS_ESTADOS ON PEDIDOS.ESTADO = PEDIDOS_ESTADOS.ESTADO");
        $query->execute();
        $pedidos = $query->fetchAll(PDO::FETCH_CLASS, "PedidoDTO");

        $conn = AccesoDatos::obtenerInstancia();
        foreach ($pedidos as $pedido) {
            $query = $conn->prepararConsulta("SELECT PEDIDOS_PRODUCTOS.ID AS id, PRODUCTOS.DESCRIPCION as descripcion, PEDIDOS_PRODUCTOS.PRODUCTO_CANTIDAD AS cantidad, PEDIDOS_PRODUCTOS.IMPORTE_PARCIAL as importeParcial, SECTORES.SEC_DESC as sector, PEDIDOS_PRODUCTOS_ESTADOS.DESCRIPCION as estado FROM PEDIDOS_PRODUCTOS INNER JOIN PRODUCTOS ON       PEDIDOS_PRODUCTOS.PRODUCTO_ID = PRODUCTOS.ID
                INNER JOIN SECTORES ON PEDIDOS_PRODUCTOS.SECTOR_CODIGO = SEC_CODIGO
                INNER JOIN PEDIDOS_PRODUCTOS_ESTADOS ON PEDIDOS_PRODUCTOS.ESTADO = PEDIDOS_PRODUCTOS_ESTADOS.ESTADO
                WHERE PEDIDO_ID = ?");
            $query->bindParam(1, $pedido->pedidoId);
            $query->execute();
            $productosPedido = $query->fetchAll(PDO::FETCH_CLASS, "ProductoPedidoDTO");
            $pedido->productosPedido = $productosPedido;
        }

        return $pedidos;
    }

    public static function CrearPedido($mesaId, $clienteNombre, $productos, $mozoId)
    {
        try {
            $pedidoId = self::AgregarPedido($mesaId, $clienteNombre, $mozoId);
            foreach ($productos as $producto) {
                self::AgregarProducto($producto, $pedidoId);
            }
            return $pedidoId;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    private static function AgregarPedido($mesaId, $clienteNombre, $mozoId)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("INSERT INTO PEDIDOS(MOZO_ID, MESA_ID, CLIENTE_NOMBRE) VALUES(?, ?, ?)");
        $query->bindParam(1, $mozoId);
        $query->bindParam(2, $mesaId);
        $query->bindParam(3, $clienteNombre);
        $query->execute();

        $query = $conn->prepararConsulta("SELECT ID FROM PEDIDOS ORDER BY ID DESC LIMIT 1");
        $query->execute();

        return $query->fetchColumn();
    }

    // 0 - esperando preparacion - 1 en preparacion - 2 listo para servir
    private static function AgregarProducto(ProductoPedido $productoPedido, $pedidoId)
    {
        $producto = ProductoRepositorio::ObtenerProductoPorId($productoPedido->productoId);
        $importeParcial = floatval($producto->precio * $productoPedido->productoCantidad);
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("INSERT INTO PEDIDOS_PRODUCTOS(PEDIDO_ID, PRODUCTO_ID, PRODUCTO_CANTIDAD, IMPORTE_PARCIAL, SECTOR_CODIGO) VALUES(?, ?, ?, ?, ?)");
        $query->bindParam(1, $pedidoId);
        $query->bindParam(2, $productoPedido->productoId);
        $query->bindParam(3, $productoPedido->productoCantidad);
        $query->bindParam(4, $importeParcial);
        $query->bindParam(5, $producto->sectorCodigo);

        $query->execute();
    }

    public static function PrepararPedido($pedidoId, $tiempoEstimado)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("UPDATE PEDIDOS_PRODUCTOS SET ESTADO = 1, TIEMPO_ESTIMADO = :tiempo, FH_INICIO = CURRENT_TIMESTAMP() WHERE ID = :id");
        $query->bindValue(":tiempo", $tiempoEstimado);
        $query->bindParam(":id", $pedidoId);

        $query->execute();
    }

    public static function FinalizarPedido($pedidoId)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("UPDATE PEDIDOS_PRODUCTOS SET ESTADO = 2, FH_FIN = CURRENT_TIMESTAMP() WHERE ID = :id");
        $query->bindParam(":id", $pedidoId);
        $query->execute();
    }

    public static function ObtenerProductoPedido($pedidoId)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("SELECT SECTOR_CODIGO as sector, ESTADO as estado FROM PEDIDOS_PRODUCTOS WHERE ID = :id");
            $query->bindValue(":id", $pedidoId);
            $query->execute();

            return $query->fetchObject("ProductoPedidoDTO");
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function ObtenerPedido($pedidoId)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT PEDIDOS.ID AS pedidoId, USUARIOS.USU_NOMBRE as mozo, MESA_ID as mesaCodigo, ESTADO as estado, IMPORTE_FINAL as importeFinal, CLIENTE_NOMBRE as clienteNombre, PEDIDOS.FH_ALTA AS fechaAlta FROM PEDIDOS INNER JOIN USUARIOS ON PEDIDOS.MOZO_ID = USUARIOS.USU_ID  WHERE PEDIDOS.ID = :pedidoId");
        $query->bindValue(":pedidoId", $pedidoId);
        $query->execute();
        $pedido = $query->fetchObject("PedidoDTO");

        if ($pedido == false) {
            return $pedido;
        }

        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT PEDIDOS_PRODUCTOS.ID AS id, PRODUCTOS.DESCRIPCION as descripcion, PEDIDOS_PRODUCTOS.PRODUCTO_CANTIDAD AS cantidad, PEDIDOS_PRODUCTOS.IMPORTE_PARCIAL as importeParcial, SECTORES.SEC_DESC as sector, PEDIDOS_PRODUCTOS.ESTADO as estado FROM PEDIDOS_PRODUCTOS INNER JOIN PRODUCTOS ON       PEDIDOS_PRODUCTOS.PRODUCTO_ID = PRODUCTOS.ID
                INNER JOIN SECTORES ON PEDIDOS_PRODUCTOS.SECTOR_CODIGO = SEC_CODIGO

                WHERE PEDIDO_ID = ?");
        $query->bindParam(1, $pedido->pedidoId);
        $query->execute();
        $productosPedido = $query->fetchAll(PDO::FETCH_CLASS, "ProductoPedidoDTO");
        $pedido->productosPedido = $productosPedido;

        return $pedido;

    }

    public static function EntregarPedido($pedidoId)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("UPDATE PEDIDOS_PRODUCTOS SET ESTADO = :estado WHERE ID = :id");
            $query->bindValue(":id", $pedidoId);
            $query->bindValue(":estado", 3, PDO::PARAM_INT);

            $query->execute();

        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function VerificarMesaPedido($mesaCodigo, $pedidoId)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("SELECT MESAS.CODIGO FROM PEDIDOS_PRODUCTOS INNER JOIN PEDIDOS ON PEDIDOS.ID = PEDIDOS_PRODUCTOS.PEDIDO_ID
            INNER JOIN MESAS ON MESAS.ID = PEDIDOS.MESA_ID
            WHERE PEDIDOS_PRODUCTOS.ID = :pedidoId");
            $query->bindValue(":pedidoId", $pedidoId);
            $query->execute();
            $codigo = $query->fetchColumn();
            if ($mesaCodigo != $codigo) {
                return false;
            }

            return true;

        } catch (Exception $ex) {
            echo $ex;
            throw $ex;
        }
    }

    public static function CerrarPedido($importeFinal, $pedidoId)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("UPDATE PEDIDOS SET IMPORTE_FINAL = :importe, ESTADO = 2 WHERE ID = :pedidoId");
            $query->bindValue(":importe", $importeFinal);
            $query->bindValue(":pedidoId", $pedidoId);
            $query->execute();

        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function ObtenerTiempoEstimadoPedido($pedidoId, $mesaId)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("SELECT PP.TIEMPO_ESTIMADO FROM PEDIDOS P INNER JOIN PEDIDOS_PRODUCTOS PP ON P.ID = PP.PEDIDO_ID WHERE P.ID = :pedidoId AND P.MESA_ID = :mesaId AND PP.TIEMPO_ESTIMADO IS NOT NULL ORDER BY PP.TIEMPO_ESTIMADO DESC LIMIT 1");
            $query->bindValue(":pedidoId", $pedidoId);
            $query->bindValue(":mesaId", $mesaId);

            $query->execute();
            return $query->fetchColumn(0);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            throw $ex;
        }
    }

    public static function AsociarEmpleado($pedidoId, $empleadoId)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("SELECT * FROM PEDIDOS_EMPLEADOS WHERE EMPLEADO_ID = :empleado");
            $query->bindValue(":empleado", $empleadoId);
            $query->execute();
            $rows = $query->rowCount();
            if ($rows < 1) {
                $query = $conn->prepararConsulta("INSERT INTO PEDIDOS_EMPLEADOS(EMPLEADO_ID, PEDIDO_ID) VALUES(:empleado, :pedido)");
                $query->bindValue(":empleado", $empleadoId);
                $query->bindValue(":pedido", $pedidoId);

                $query->execute();
            }

        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
