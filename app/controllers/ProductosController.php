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
        $producto->productoCodigo = $parametros["codigo"];
        $producto->descripcion = $parametros["descripcion"];
        $producto->sectorCodigo = $parametros["sector"];
        $producto->precio = $parametros["precio"];

        if (ProductoRepositorio::ExisteProducto($producto->productoCodigo)) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "No se puede crear el producto, ya existe uno con un el mismo codigo.")));
            return $res->withHeader('Content-Type', 'application/json')
                ->withStatus(406);
        }

        if (!ProductoRepositorio::ExisteSector($producto->sectorCodigo)) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "No se puede crear el producto, el sector indicado no existe.")));
            return $res->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        try {
            ProductoRepositorio::AgregarProducto($producto);
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }

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
        $producto->productoCodigo = $parametros["codigo"];
        $producto->descripcion = $parametros["descripcion"];
        $producto->sectorCodigo = $parametros["sector"];
        $producto->precio = $parametros["precio"];

        if (ProductoRepositorio::ExisteProductoPorId($id) !== true) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "El id indicado no pertenece a un producto existente.")));
            return $res->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        try {
            ProductoRepositorio::ModificarProducto($id, $producto);
        } catch (Exception $ex) {
            echo $ex->getMessage();

        }

        $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

        $res->getBody()->write($payload);
        return $res
            ->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerProducto(IRequest $req, IResponse $response, array $args)
    {
        $id = $args['id'];
        try {
            $producto = ProductoRepositorio::ObtenerProductoPorId($id);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $res = new Response();
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

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
        try {
            $lista = ProductoRepositorio::ObtenerProductos();
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $res = new Response();
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $payload = json_encode(array("Productos" => $lista));

        $res->getBody()->write($payload);
        return $res
            ->withHeader('Content-Type', 'application/json');
    }

    public function EliminarProducto(IRequest $req, IResponse $res, array $args)
    {
        $id = $args['id'];

        if (ProductoRepositorio::ExisteProductoPorId($id) !== true) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "El id indicado no pertenece a un producto existente.")));
            return $res->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        try {
            ProductoRepositorio::BorrarProducto($id);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $res = new Response();
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $payload = json_encode(array("mensaje" => "Producto borrado con exito"));

        $res->getBody()->write($payload);
        return $res
            ->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}
