<?php

require_once "./db/AccesoDatos.php";
require_once './models/Comanda.php';
require_once './models/ComandaPedido.php';

class ComandasRepositorio
{
    public static function AgregarComanda(Comanda $comanda)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("INSERT INTO COMANDAS (CMD_MOZO, MES_CODIGO, CMD_CLI_NOMBRE) VALUES (?, ?, ?)");
            $query->bindParam(1, $comanda->mozoId);
            $query->bindParam(2, $comanda->codigoMesa);
            $query->bindParam(3, $comanda->nombreCliente);

            $query->execute();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    public static function AgregarComandaPedido(ComandaPedido $comandaPedido)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("INSERT INTO COMANDAS_PEDIDOS (CMD_ID, CPE_ART_CODIGO, CPE_ART_CANTIDAD, CPE_SECTOR) VALUES (?, ?, ?, ?)");
            $query->bindParam(1, $comandaPedido->comandaId);
            $query->bindParam(2, $comandaPedido->articuloCodigo);
            $query->bindParam(3, $comandaPedido->articuloCantidad);
            $sectorArticulo = self::ObtenerSectorArticulo($comandaPedido->articuloCodigo);
            $query->bindParam(4, $sectorArticulo);

            $query->execute();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

        public static function ObtenerPedidosListos()
    {
        try {

            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("select c.CMD_ID as idComanda, c.MES_CODIGO as codigoMesa from comandas_pedidos cp inner join comandas c on cp.CMD_ID = c.CMD_ID where cp.CPE_ESTADO = 2 and c.CMD_ID = ? LIMIT 1");
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            return false;
        }
    }

    private static function ObtenerSectorArticulo($articuloCodigo){
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT ART_SEC FROM ARTICULOS WHERE ART_CODIGO = ?");
        $query->bindParam(1, $articuloCodigo);
        $query->execute();

        return $query->fetchColumn();
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
