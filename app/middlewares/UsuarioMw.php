<?php

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Server\RequestHandlerInterface as IRequestHandler;
use Slim\Psr7\Response as Response;

require_once "./Repositorios/UsuarioRepositorio.php";
require_once "./Util/Jwt.php";
require_once "./Repositorios/PedidosRepositorio.php";

class UsuarioMw
{

    public function ValidarCampos(IRequest $request, IRequestHandler $handler): Response
    {
        $reqBody = $request->getParsedBody();
        $user = $reqBody["usuario"];
        $password = $reqBody["clave"];
        $msg = "";

        if ($user == "" || $user == null) {
            $msg = $msg . "Verifique el campo usuario. ";
        }

        if ($password == "" || $password == null) {
            $msg = $msg . "Verifique el campo de la clave.";
        }

        if ($msg != "") {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => $msg)));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        return $handler->handle($request);
    }

    public function VerificarUsuarioExistente(IRequest $request, IRequestHandler $handler): Response
    {
        $reqBody = $request->getParsedBody();
        $usuario = $reqBody["usuario"];
        $existe = UsuarioRepositorio::ExisteUsuario($usuario);
        if (!$existe) {
            $response = new Response();
            $response->getBody()->write("El usuario indicado no se encuentra registrado.");
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        return $handler->handle($request);
    }

    public function ValidarMozo(IRequest $req, IRequestHandler $handler): Response
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

        if ($usuario["USU_ROL"] !== "MZ") {
            return false;
        }

        return true;
    }

    public function VerificarSector(IRequest $req, IRequestHandler $handler)
    {
        $uri = $req->getUri();
        $path = explode("/", $uri->getPath());
        $pedidoId = $path[4];

        try {
            $pedido = PedidosRepositorio::ObtenerProductoPedido($pedidoId);
            $header = $req->getHeaderLine('Authorization');
            $token = trim(explode("Bearer", $header)[1]);
            $datos = Token::ObtenerData($token);
            $usuario = UsuarioRepositorio::ObtenerUsuarioPorId($datos->id);
            if ($usuario->sector != $pedido->sector) {
                $res = new Response();
                $res->getBody()->write("El pedido no corresponde a su sector.");
                return $res->withStatus(401);
            }

            return $handler->handle($req);

        } catch (Exception $ex) {
            echo $ex->getMessage();
            $res = new Response();
            return $res->withStatus(401);
        }
    }
}
