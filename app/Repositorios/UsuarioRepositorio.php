<?php

require_once "./db/AccesoDatos.php";
require_once './Dtos/UsuarioDto.php';

class UsuarioRepositorio
{
    public static function AgregarUsuario(Usuario $usuario)
    {
        try {
            $conn = AccesoDatos::obtenerInstancia();
            $consulta = $conn->prepararConsulta("INSERT INTO USUARIOS (USU_NOMBRE, USU_APELLIDO, USU_USUARIO, USU_CLAVE, USU_ROL, USU_SECTOR) VALUES (?, ?, ?, ?, ?, ?)");
            $claveHash = password_hash($usuario->clave, PASSWORD_DEFAULT);
            $consulta->bindParam(1, $usuario->nombre);
            $consulta->bindParam(2, $usuario->apellido);
            $consulta->bindParam(3, $usuario->usuario);
            $consulta->bindParam(4, $claveHash);
            $consulta->bindParam(5, $usuario->rol->valor());
            $consulta->bindParam(6, $usuario->sector->valor());
            $consulta->execute();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    public static function ObtenerUsuarios()
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT USU_ID AS id, USU_NOMBRE AS nombre, USU_APELLIDO AS apellido, USU_ROL AS rol, USU_ACTIVO AS activo, USU_SECTOR AS sector, USU_SUSPENDIDO AS suspendido FROM USUARIOS");
        $query->execute();

        return $query->fetchAll(pdo::FETCH_CLASS, "UsuarioDto");
    }

    public static function ObtenerUsuario($usuario)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT USU_ID, USU_USUARIO, USU_CLAVE, USU_ROL FROM USUARIOS WHERE USU_USUARIO = :usuario");
        $query->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public static function ObtenerUsuarioPorId($id)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("SELECT USU_ID AS id, USU_NOMBRE AS nombre, USU_APELLIDO AS apellido, USU_ROL AS rol, USU_ACTIVO AS activo, USU_SECTOR AS sector, USU_SUSPENDIDO AS suspendido FROM USUARIOS WHERE USU_ID = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchObject("UsuarioDto");
    }

    public static function ModificarUsuario(int $id, Usuario $usuario)
    {
        try{
            $conn = AccesoDatos::obtenerInstancia();
            $query = $conn->prepararConsulta("UPDATE USUARIOS SET USU_NOMBRE = :nombre, USU_APELLIDO = :apellido, USU_USUARIO = :usuario, USU_CLAVE = :clave, USU_ROL = :rol, USU_SECTOR = :sector, USU_SUSPENDIDO = :suspendido, USU_FH_MODIF = CURRENT_TIMESTAMP() WHERE USU_ID = :id");
    
            $hashPsw = password_hash($usuario->clave, PASSWORD_DEFAULT);
            $query->bindValue(":nombre", $usuario->nombre);
            $query->bindValue(":apellido", $usuario->apellido);
            $query->bindValue(":usuario", $usuario->usuario);
            $query->bindValue(":clave", $hashPsw);
            $query->bindValue(":rol", $usuario->rol->valor());
            $query->bindValue(":sector", $usuario->sector->valor());
            $query->bindValue(":suspendido", $usuario->suspendido);
            $query->bindValue(":id", $id);
    
            $query->execute();
        }
        catch(Exception $ex){
            echo $ex->getMessage();
        }
    }

    public static function BorrarUsuario($id)
    {
        $conn = AccesoDatos::obtenerInstancia();
        $query = $conn->prepararConsulta("UPDATE USUARIOS SET USU_ACTIVO = 0, USU_FH_BAJA = CURRENT_TIMESTAMP() WHERE USU_ID = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
    }

    public static function ExisteUsuario(string $user)
    {
        $sql = AccesoDatos::obtenerInstancia();
        $query = $sql->prepararConsulta("SELECT * FROM USUARIOS WHERE USU_USUARIO = ?");
        $query->bindParam(1, $user);
        $query->execute();
        return $query->rowCount() > 0 ? true : false;
    }
}
