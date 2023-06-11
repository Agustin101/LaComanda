<?php

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Server\RequestHandlerInterface as IRequestHandler;
use Slim\Psr7\Response;

require_once "./Util/Jwt.php";

class JwtMw
{

    public function ValidarToken(IRequest $req, IRequestHandler $handler)
    {
        try {
            $header = $req->getHeaderLine('Authorization');
            $token = trim(explode("Bearer", $header)[1]);
            Token::VerificarToken($token);
            return $handler->handle($req);
        } catch (Exception $ex) {
            $res = new Response();
            $res->getBody()->write("El token enviado no es valido");
            return $res->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}
