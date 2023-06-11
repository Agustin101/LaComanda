<?php

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Server\RequestHandlerInterface as IRequestHandler;
use Slim\Psr7\Response as Response;

require_once './Repositorios/UsuarioRepositorio.php';

class SocioMw
{

    public function ValidarSocio(IRequest $req, IRequestHandler $handler)
    {
        try {
            $header = $req->getHeaderLine('Authorization');
            $token = trim(explode("Bearer", $header)[1]);
            $datos = Token::ObtenerData($token);

            if ($this->VerificarCredenciales($datos) === false) {
                throw new Exception();
            }

            return $handler->handle($req);
        } catch (Exception $ex) {
            $res = new Response();
            $res->getBody()->write("El token enviado no es valido");
            return $res->withStatus(401);
        }
    }

    private function VerificarCredenciales($datos)
    {
        //preguntar si es socio, en ese caso seguir adelante, si no retornar unhautorized indicando
        $usuario = UsuarioRepositorio::ObtenerUsuario($datos->usuario);

        if ($usuario === false) {
            return false;
        }

        if ($usuario["USU_ROL"] !== "SC") {
            return false;
        }

        return true;
    }
}
