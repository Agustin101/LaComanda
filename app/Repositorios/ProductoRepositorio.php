<?php

require_once "./db/AccesoDatos.php";
require_once './models/Producto.php';

class ProductoRepositorio
{
    public static function AgregarProducto(Producto $producto)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("INSERT INTO ARTICULOS (ART_CODIGO, ART_DESC, ART_SEC) VALUES (?, ?, ?)");
            $query->bindParam(1, $producto->codigo);
            $query->bindParam(2, $producto->descripcion);
            $query->bindParam(3, $producto->sector);
            $query->execute();
            
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
    
    public static function ModificarProducto(int $id, Producto $producto)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("UPDATE ARTICULOS SET ART_CODIGO = :codigo, ART_DESC = :desc, ART_SEC = :sector, ART_FH_MODIF = CURRENT_TIMESTAMP() WHERE ART_ID = :id");

            $query->bindValue(":codigo", $producto->codigo);
            $query->bindValue(":desc", $producto->descripcion);
            $query->bindValue(":sector", $producto->sector);
            $query->bindValue(":id", $id);

            $query->execute();
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public static function ObtenerProductos()
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT ART_ID AS id, ART_CODIGO AS codigo, ART_DESC AS descripcion, ART_SEC AS sector, ART_FH_ALTA AS fechaAlta, ART_FH_MODIF AS fechaModificacion, ART_ACTIVO AS activo, ART_FH_BAJA AS fechaBaja FROM ARTICULOS");
        $query->execute();

        return $query->fetchAll(pdo::FETCH_CLASS, "Producto");
    }

    public static function ObtenerProductoPorId($id)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT ART_ID AS id, ART_CODIGO AS codigo, ART_DESC AS descripcion, ART_SEC AS sector, ART_FH_ALTA AS fechaAlta, ART_FH_MODIF AS fechaModificacion, ART_ACTIVO AS activo, ART_FH_BAJA AS fechaBaja FROM ARTICULOS WHERE ART_ID = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        
        return $query->fetchObject("Producto");
    }


    public static function BorrarProducto($id)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("UPDATE ARTICULOS SET ART_ACTIVO = 0, ART_FH_BAJA = CURRENT_TIMESTAMP() WHERE ART_ID = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
    }

    public static function ExisteProducto(string $user)
    {
        $sql = AccesoDatos::obtenerInstancia();
        $query = $sql->prepararConsulta("SELECT * FROM USUARIOS WHERE USU_USUARIO = ?");
        $query->bindParam(1, $user);
        $query->execute();
        return $query->rowCount() > 0 ? true : false;
    }
}
