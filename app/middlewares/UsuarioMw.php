<?php

use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Server\RequestHandlerInterface as IRequestHandler;
use Slim\Psr7\Response as Response;

require_once "./Repositorios/UsuarioRepositorio.php";
require_once "./Util/Jwt.php";

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
}
