<?php
require_once './models/Producto.php';
require_once './Repositorios/ProductoRepositorio.php';
use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Slim\Psr7\Response;

class ProductosController
{
    public function AgregarProducto(IRequest $request, IResponse $response, $args)
    {
        $parametros = $request->getParsedBody();
        $producto = new Producto();
        $producto->codigo = $parametros["codigo"];
        $producto->descripcion = $parametros["descripcion"];
        $producto->sector = $parametros["sector"];

        ProductoRepositorio::AgregarProducto($producto);

        $payload = json_encode(array("mensaje" => "Producto creado con exito."));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function ModificarProducto(IRequest $req, IResponse $res, array $args)
    {
        parse_str(file_get_contents('php://input'), $parametros);
        $id = $args['id'];
        $producto = new Producto();
        $producto->codigo = $parametros["codigo"];
        $producto->descripcion = $parametros["descripcion"];
        $producto->sector = $parametros["sector"];

        ProductoRepositorio::ModificarProducto($id, $producto);

        $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

        $res->getBody()->write($payload);
        return $res
            ->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerProducto(IRequest $req, IResponse $response, array $args)
    {
        $id = $args['id'];
        $producto = ProductoRepositorio::ObtenerProductoPorId($id);

        if ($producto === false) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "El producto indicado no existe.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $payload = json_encode($producto);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerProductos(IRequest $req, IResponse $res, $args)
    {
        $lista = ProductoRepositorio::ObtenerProductos();
        $payload = json_encode(array("Productos" => $lista));

        $res->getBody()->write($payload);
        return $res
            ->withHeader('Content-Type', 'application/json');
    }

    public function EliminarProducto(IRequest $req, IResponse $res, array $args)
    {
        $id = $args['id'];
        ProductoRepositorio::BorrarProducto($id);
        $payload = json_encode(array("mensaje" => "Producto borrado con exito"));

        $res->getBody()->write($payload);
        return $res
            ->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}
