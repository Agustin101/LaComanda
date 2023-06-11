<?php

require_once './Util/Jwt.php';
require_once './Repositorios/UsuarioRepositorio.php';

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;

class AutorizacionController
{

    public function GenerarToken(IRequest $req, IResponse $res) : IResponse
    {
        $params = $req->getParsedBody();
        $usuario = $params["usuario"];
        $clave = $params["clave"];
        $usuario = $this->ValidarCredenciales($usuario, $clave);

        if ($usuario === false) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "Credenciales invalidas.")));
            return $res
                ->withHeader('Content-Type', 'application/json')->withStatus(403);
        }
        $token = Token::CrearToken($usuario);
        $res->getBody()->write(json_encode(array("jwt" => $token)));
        return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    private function ValidarCredenciales(string $usuario, string $clave)
    {
        $usuario = UsuarioRepositorio::ObtenerUsuario($usuario);

        if (!password_verify($clave, $usuario["USU_CLAVE"])) {
            return false;
        }

        $datos = array("id" => $usuario["USU_ID"], "usuario" => $usuario["USU_USUARIO"]);
        return $datos;
    }
}
