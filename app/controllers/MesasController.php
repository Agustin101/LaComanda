<?php
require_once './models/Mesa.php';
require_once './Repositorios/MesasRepositorio.php';
use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Slim\Psr7\Response;

class MesasController
{
    public function AgregarMesa(IRequest $request, IResponse $response, $args)
    {
        $parametros = $request->getParsedBody();
        $mesa = new Mesa();
        $mesa->descripcion = $parametros["descripcion"];
        $mesa->codigo = $parametros["codigo"];
        if (MesaRepositorio::ExisteMesa($mesa->codigo)) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "Ya existe una mesa con ese codigo.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            MesaRepositorio::AgregarMesa($mesa);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $res = new Response();
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $payload = json_encode(array("mensaje" => "Mesa creada con exito."));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function ModificarMesa(IRequest $req, IResponse $res, array $args)
    {
        $parametros = $req->getParsedBody();
        $id = $args['id'];
        $mesa = new Mesa();
        if (MesaRepositorio::ObtenerMesaPorId($id) === false) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "La mesa no existe.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $mesa->estado = $parametros["estado"];
        $mesa->descripcion = $parametros["descripcion"];
        $mesa->codigo = $parametros["codigo"];

        try {
            MesaRepositorio::ModificarMesa($id, $mesa);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $res = new Response();
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $payload = json_encode(array("mensaje" => "Mesa modificado con exito"));

        $res->getBody()->write($payload);
        return $res
            ->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerMesa(IRequest $req, IResponse $response, array $args)
    {
        $id = $args['id'];
        $producto = MesaRepositorio::ObtenerMesaPorId($id);

        if ($producto === false) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "La mesa indicada no existe.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $payload = json_encode($producto);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerMesas(IRequest $req, IResponse $res, $args)
    {
        $lista = MesaRepositorio::ObtenerMesas();
        $payload = json_encode(array("Mesas" => $lista));

        $res->getBody()->write($payload);
        return $res
            ->withHeader('Content-Type', 'application/json');
    }

    public function EliminarMesa(IRequest $req, IResponse $res, array $args)
    {
        $id = $args['id'];
        if (MesaRepositorio::ObtenerMesaPorId($id) === false) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "La mesa no existe.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        MesaRepositorio::BorrarMesa($id);
        $payload = json_encode(array("mensaje" => "Mesa borrada con exito"));

        $res->getBody()->write($payload);
        return $res
            ->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}
