<?php

require_once "./db/AccesoDatos.php";
require_once './models/Producto.php';

class ProductoRepositorio
{
    public static function AgregarProducto(Producto $producto)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("INSERT INTO PRODUCTOS (CODIGO_PRODUCTO, DESCRIPCION, CODIGO_SECTOR, PRECIO) VALUES (?, ?, ?, ?)");
            $query->bindParam(1, $producto->productoCodigo);
            $query->bindParam(2, $producto->descripcion);
            $query->bindParam(3, $producto->sectorCodigo);
            $query->bindParam(4, $producto->precio);

            $query->execute();

            return true;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function AgregarProductoBackup(Producto $producto)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("INSERT INTO PRODUCTOS (CODIGO_PRODUCTO, DESCRIPCION, CODIGO_SECTOR, PRECIO, FH_ALTA, FH_MODIF, ACTIVO, FH_BAJA) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $query->bindParam(1, $producto->productoCodigo);
            $query->bindParam(2, $producto->descripcion);
            $query->bindParam(3, $producto->sectorCodigo);
            $query->bindParam(4, $producto->precio);
            $query->bindParam(5, $producto->fechaAlta);
            $query->bindParam(6, $producto->fechaModificacion);
            $query->bindParam(7, $producto->activo);
            $query->bindParam(8, $producto->fechaBaja);

            $query->execute();

            return true;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function ModificarProducto(int $id, Producto $producto)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("UPDATE PRODUCTOS SET CODIGO_PRODUCTO = :codigo, DESCRIPCION = :desc, CODIGO_SECTOR = :sector, PRECIO = :precio, FH_MODIF = CURRENT_TIMESTAMP() WHERE ID = :id");

            $query->bindValue(":codigo", $producto->productoCodigo);
            $query->bindValue(":desc", $producto->descripcion);
            $query->bindValue(":sector", $producto->sectorCodigo);
            $query->bindValue(":precio", $producto->precio);
            $query->bindValue(":id", $id);

            $query->execute();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function ObtenerProductos()
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("SELECT ID AS id, CODIGO_PRODUCTO AS productoCodigo, DESCRIPCION AS descripcion, CODIGO_SECTOR AS sectorCodigo, FH_ALTA AS fechaAlta, FH_MODIF AS fechaModificacion, ACTIVO AS activo, FH_BAJA AS fechaBaja, PRECIO as precio FROM PRODUCTOS");
            $query->execute();

            return $query->fetchAll(pdo::FETCH_CLASS, "Producto");
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function ObtenerProductoPorId($id)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("SELECT ID AS id, CODIGO_PRODUCTO AS productoCodigo, DESCRIPCION AS descripcion, CODIGO_SECTOR AS sectorCodigo, FH_ALTA AS fechaAlta, FH_MODIF AS fechaModificacion, ACTIVO AS activo, FH_BAJA AS fechaBaja, PRECIO as precio FROM PRODUCTOS WHERE ID = :id");
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();

            return $query->fetchObject("Producto");
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function BorrarProducto($id)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("UPDATE PRODUCTOS SET ACTIVO = 0, FH_BAJA = CURRENT_TIMESTAMP() WHERE ID = :id");
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function ExisteProducto(string $productoCodigo)
    {
        $sql = AccesoDatos::obtenerInstancia();
        $query = $sql->prepararConsulta("SELECT * FROM PRODUCTOS WHERE CODIGO_PRODUCTO = ?");
        $query->bindParam(1, $productoCodigo);
        $query->execute();
        return $query->rowCount() > 0 ? true : false;
    }

    public static function ExisteSector(string $sectorCodigo)
    {
        $sql = AccesoDatos::obtenerInstancia();
        $query = $sql->prepararConsulta("SELECT * FROM SECTORES WHERE SEC_CODIGO = ?");
        $query->bindParam(1, $sectorCodigo);
        $query->execute();
        return $query->rowCount() > 0 ? true : false;
    }

    public static function ExisteProductoPorId(string $productoId)
    {
        $sql = AccesoDatos::obtenerInstancia();
        $query = $sql->prepararConsulta("SELECT * FROM PRODUCTOS WHERE ID = ?");
        $query->bindParam(1, $productoId);
        $query->execute();
        return $query->rowCount() > 0 ? true : false;
    }
}
