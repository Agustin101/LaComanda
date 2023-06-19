<?php

require_once "./db/AccesoDatos.php";
require_once './models/Mesa.php';

class MesaRepositorio
{
    public static function AgregarMesa(Mesa $mesa)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("INSERT INTO MESAS (DESCRIPCION, CODIGO) VALUES (?, ?)");
            $query->bindParam(1, $mesa->descripcion);
            $query->bindParam(2, $mesa->codigo);

            $query->execute();

            return true;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function ModificarMesa(int $id, Mesa $mesa)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("UPDATE MESAS SET ESTADO = :estado, CODIGO = :codigo, DESCRIPCION = :descripcion, FH_MODIF = CURRENT_TIMESTAMP() WHERE ID = :id");

            $query->bindValue(":id", $id);
            $query->bindValue(":descripcion", $mesa->descripcion);
            $query->bindValue(":estado", $mesa->estado);
            $query->bindValue(":codigo", $mesa->codigo);

            $query->execute();
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public static function ActualizarEstadoMesa(int $mesaId, int $estado)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("UPDATE MESAS SET ESTADO = :estado, FH_MODIF = CURRENT_TIMESTAMP() WHERE ID = :id");

            $query->bindValue(":id", $mesaId);
            $query->bindValue(":estado", $estado);

            $query->execute();
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public static function ObtenerMesas()
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT ID AS id, ESTADO AS estado, ACTIVA AS activa, FH_ALTA AS fechaAlta, FH_MODIF AS fechaModificacion, FH_BAJA AS fechaBaja, DESCRIPCION as descripcion, CODIGO as codigo FROM MESAS");
        $query->execute();

        return $query->fetchAll(pdo::FETCH_CLASS, "Mesa");
    }

    public static function ObtenerMesaPorId($id)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT ID AS id, ESTADO AS estado, ACTIVA AS activa, FH_ALTA AS fechaAlta, FH_MODIF AS fechaModificacion, FH_BAJA AS fechaBaja, DESCRIPCION as descripcion, CODIGO as codigo FROM MESAS WHERE ID = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchObject("Mesa");
    }

    public static function ObtenerMesaPorCodigo($codigo)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT ID AS id, ESTADO AS estado, ACTIVA AS activa, FH_ALTA AS fechaAlta, FH_MODIF AS fechaModificacion, FH_BAJA AS fechaBaja, DESCRIPCION as descripcion, CODIGO as codigo FROM MESAS WHERE CODIGO = :codigo");
        $query->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $query->execute();

        return $query->fetchObject("Mesa");
    }

    public static function BorrarMesa($id)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("UPDATE MESAS SET ACTIVA = 0, FH_BAJA = CURRENT_TIMESTAMP() WHERE ID = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
    }

    public static function ExisteMesa(string $codigo)
    {
        $sql = AccesoDatos::obtenerInstancia();
        $query = $sql->prepararConsulta("SELECT * FROM MESAS WHERE CODIGO = ?");
        $query->bindParam(1, $codigo);
        $query->execute();
        return $query->rowCount() > 0 ? true : false;
    }
}
