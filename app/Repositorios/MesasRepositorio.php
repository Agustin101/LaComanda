<?php

require_once "./db/AccesoDatos.php";
require_once './models/Mesa.php';

class MesaRepositorio
{
    public static function AgregarMesa(Mesa $mesa)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("INSERT INTO MESAS (MES_CODIGO, MES_ESTADO) VALUES (?, ?)");
            $query->bindParam(1, $mesa->codigo);
            $query->bindParam(2, $mesa->estado);

            $query->execute();

            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    public static function ModificarMesa(int $id, Mesa $mesa)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("UPDATE MESAS SET MES_CODIGO = :codigo, MES_ESTADO = :estado, MES_ACTIVA = :activa, MES_FH_MODIF = CURRENT_TIMESTAMP() WHERE MES_ID = :id");

            $query->bindValue(":codigo", $mesa->codigo);
            $query->bindValue(":id", $id);
            $query->bindValue(":estado", $mesa->estado);

            $query->execute();
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public static function ObtenerMesas()
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT MES_ID AS id, MES_CODIGO AS codigo, MES_ESTADO AS estado, MES_ACTIVA AS activa, MES_FH_ALTA AS fechaAlta, MES_FH_MODIF AS fechaModificacion, MES_FH_BAJA AS fechaBaja FROM MESAS");
        $query->execute();

        return $query->fetchAll(pdo::FETCH_CLASS, "Mesa");
    }

    public static function ObtenerMesaPorId($id)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT MES_ID AS id, MES_CODIGO AS codigo, MES_ESTADO AS estado, MES_ACTIVA AS activa, MES_FH_ALTA AS fechaAlta, MES_FH_MODIF AS fechaModificacion, MES_FH_BAJA AS fechaBaja FROM MESAS WHERE MES_ID = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchObject("Mesa");
    }

    public static function BorrarMesa($id)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("UPDATE MESAS SET MES_ACTIVA = 0, MES_FH_BAJA = CURRENT_TIMESTAMP() WHERE MES_ID = :id");
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
