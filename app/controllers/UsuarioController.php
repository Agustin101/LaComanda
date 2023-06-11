<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
require_once './Repositorios/UsuarioRepositorio.php';
use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Slim\Psr7\Response;

class UsuarioController implements IApiUsable
{
    public function AgregarUsuario(IRequest $request, IResponse $response, $args)
    {
        $parametros = $request->getParsedBody();
        $usuario = new Usuario($parametros["nombre"], $parametros["apellido"], $parametros["usuario"],
            $parametros["clave"], Rol::tryFrom($parametros["rol"]), Sector::tryFrom($parametros["sector"]));
        UsuarioRepositorio::AgregarUsuario($usuario);

        $payload = json_encode(array("mensaje" => "Usuario creado con exito."));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function ObtenerUsuario(IRequest $req, IResponse $response, array $args)
    {
        $id = $args['id'];
        $usuario = UsuarioRepositorio::ObtenerUsuarioPorId($id);

        if ($usuario === false) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "El usuario indicado no existe.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerUsuarios(IRequest $req, IResponse $res, $args)
    {
        $lista = UsuarioRepositorio::ObtenerUsuarios();
        $payload = json_encode(array("Usuarios" => $lista));

        $res->getBody()->write($payload);
        return $res
            ->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUsuario(IRequest $req, IResponse $res, array $args)
    {
        parse_str(file_get_contents('php://input'), $parametros);
        $id = $args['id'];
        $usuario = new Usuario($parametros['nombre'], $parametros['apellido'], $parametros['usuario'], $parametros['clave'], Rol::tryFrom($parametros['rol']), Sector::tryFrom($parametros['sector']), $parametros['suspendido']
        );

        UsuarioRepositorio::ModificarUsuario($id, $usuario);

        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

        $res->getBody()->write($payload);
        return $res
            ->withHeader('Content-Type', 'application/json');

    }

    public function EliminarUsuario(IRequest $req, IResponse $res, array $args)
    {
        $id = $args['id'];
        UsuarioRepositorio::BorrarUsuario($id);
        $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

        $res->getBody()->write($payload);
        return $res
            ->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}
