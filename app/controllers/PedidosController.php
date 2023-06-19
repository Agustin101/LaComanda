<?php

require_once './Repositorios/PedidosRepositorio.php';
require_once './Repositorios/UsuarioRepositorio.php';
require_once "./Repositorios/MesasRepositorio.php";
require_once './Util/Jwt.php';
require_once './models/ProductoPedido.php';

use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Slim\Psr7\Response;

class PedidosController
{

    public function ListarPedidos(IRequest $req, IResponse $res)
    {
        $listaPedidos = PedidosRepositorio::ObtenerTodosLosPedidos();

        if (count($listaPedidos) === 0) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("Mensaje" => "No hay pedidos pendientes.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
        }

        $res->getBody()->write(json_encode(array("Pedidos:" => $listaPedidos)));

        return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function PedidosSector(IRequest $req, IResponse $res, array $args)
    {
        $header = $req->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $datos = Token::ObtenerData($token);
        $sector = UsuarioRepositorio::ObtenerSectorUsuario($datos->id);
        if ($sector == "AC") {
            $listaPedidos = PedidosRepositorio::ObtenerPedidosPreparados();
        } else {
            $listaPedidos = PedidosRepositorio::ObtenerPedidosPorSector($sector);
        }

        if (count($listaPedidos) === 0) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("Mensaje" => "No hay pedidos pendientes.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
        }

        $res->getBody()->write(json_encode($listaPedidos));

        return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function CrearPedido(IRequest $req, IResponse $res)
    {
        $parametros = $req->getParsedBody();
        $header = $req->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $datos = Token::ObtenerData($token);
        $mesaCodigo = $parametros["mesaCodigo"];

        if (!MesaRepositorio::ExisteMesa($mesaCodigo)) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "La mesa indicada no existe.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $mesa = MesaRepositorio::ObtenerMesaPorCodigo($mesaCodigo);

        if ($mesa->estado > 0 && $mesa->estado < 4) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "La mesa indicada se encuentra en uso.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);

        } else if ($mesa->estado == 4) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "La mesa indicada se encuentra cerrada.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (!$mesa->activa) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "La mesa indicada no esta activa.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $cliente = $parametros["cliente"];

        $productos = array();
        $productoUno = new ProductoPedido();
        $productoUno->productoId = $parametros["bebidaUnoId"];
        $productoUno->productoCantidad = $parametros["bebidaUnoCantidad"];

        $productoDos = new ProductoPedido();
        $productoDos->productoId = $parametros["bebidaDosId"];
        $productoDos->productoCantidad = $parametros["bebidaDosCantidad"];

        $productoTres = new ProductoPedido();
        $productoTres->productoId = $parametros["comidaUnoId"];
        $productoTres->productoCantidad = $parametros["comidaUnoCantidad"];

        $productoCuatro = new ProductoPedido();
        $productoCuatro->productoId = $parametros["comidaDosId"];
        $productoCuatro->productoCantidad = $parametros["comidaDosCantidad"];
        array_push($productos, $productoUno, $productoDos, $productoTres, $productoCuatro);

        foreach ($productos as $producto) {
            if (!ProductoRepositorio::ExisteProductoPorId($producto->productoId)) {
                $res = new Response();
                $res->getBody()->write(json_encode(array("mensaje" => "El producto con ID " . $producto->productoId . " no existe.")));
                return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        try {
            $pedidoId = PedidosRepositorio::CrearPedido($mesa->id, $cliente, $productos, $datos->id);
            MesaRepositorio::ActualizarEstadoMesa($mesa->id, 1);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $res = new Response();
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $res->getBody()->write(json_encode(array("mensaje" => "Se inicio la preparacion del pedido.", "pedidoId" => $pedidoId, "mesaId" => $mesa->id)));
        return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function ActualizarPedido(IRequest $req, IResponse $res, array $args)
    {
        $parametros = $req->getParsedBody();
        $pedidoId = $args["id"];
        $estado = $parametros["estado"];
        $tiempoEstimado = $parametros["tiempoEstimado"];
        $pedido = PedidosRepositorio::ObtenerProductoPedido($pedidoId);

        if ($estado != 1 && $estado != 2) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("Mensaje" => "Este rol solo se encuentra autorizado para poner pedidos en preparacion o marcarlos como listos para ser servidos.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (($pedido->estado == 0 || $pedido->estado == 1) && $estado == 1) {
            $header = $req->getHeaderLine('Authorization');
            $token = trim(explode("Bearer", $header)[1]);
            $datos = Token::ObtenerData($token);
            PedidosRepositorio::AsociarEmpleado($pedidoId, $datos->id);
            PedidosRepositorio::PrepararPedido($pedidoId, $tiempoEstimado);
            $res->getBody()->write(json_encode(array("mensaje" => "Pedido en preparacion.")));
        } else if ($estado == 2 && $pedido->estado == 1) {
            $header = $req->getHeaderLine('Authorization');
            $token = trim(explode("Bearer", $header)[1]);
            $datos = Token::ObtenerData($token);
            PedidosRepositorio::AsociarEmpleado($pedidoId, $datos->id);
            PedidosRepositorio::FinalizarPedido($pedidoId);
            $res->getBody()->write(json_encode(array("mensaje" => "Pedido listo para ser servido.")));
        } else if ($pedido->estado == 2) {
            $res->getBody()->write(json_encode(array("mensaje" => "El pedido ya se encuentra finalizado.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        } else if ($pedido->estado == 0 && $estado == 2) {
            $res->getBody()->write(json_encode(array("mensaje" => "El pedido aun no se ha marcado en preparacion.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function ServirPedido(IRequest $req, IResponse $res, array $args)
    {
        $parametros = $req->getParsedBody();
        $codigoMesa = $parametros["mesaCodigo"];
        $mesa = MesaRepositorio::ObtenerMesaPorCodigo($codigoMesa);
        $pedidoId = $args["id"];
        $pedido = PedidosRepositorio::ObtenerProductoPedido($pedidoId);
        if ($pedido->estado == 3) {
            $res->getBody()->write(json_encode(array("mensaje" => "El pedido ya fue servido.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        } else if ($pedido->estado != 2) {
            $res->getBody()->write(json_encode(array("mensaje" => "El pedido aun no se encuentra preparado.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            if (!PedidosRepositorio::VerificarMesaPedido($mesa->codigo, $pedidoId)) {
                $res->getBody()->write(json_encode(array("mensaje" => "La mesa no corresponde al pedido.")));
                return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            PedidosRepositorio::EntregarPedido($pedidoId);
            MesaRepositorio::ActualizarEstadoMesa($mesa->id, 2);
        } catch (Exception $ex) {
            echo $ex;
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $res->getBody()->write(json_encode(array("mensaje" => "El pedido fue servido con exito.")));
        return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function CobrarPedido(IRequest $req, IResponse $res, array $args)
    {
        $pedidoId = $args["id"];
        $pedido = PedidosRepositorio::ObtenerPedido($pedidoId);

        if ($pedido == false) {
            $res->getBody()->write(json_encode(array("mensaje" => "El pedido no existe.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if ($pedido->estado == 2) {
            $res->getBody()->write(json_encode(array("mensaje" => "El pedido ya fue cobrado.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $importeFinal = 0;
        foreach ($pedido->productosPedido as $producto) {
            $importeFinal += $producto->importeParcial;
            if ($producto->estado != 3) {
                var_dump($producto);
                $res->getBody()->write(json_encode(array("mensaje" => "El pedido no pude cobrarse ya que quedan productos sin entregar.")));
                return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        try {
            PedidosRepositorio::CerrarPedido($importeFinal, $pedidoId);
            MesaRepositorio::ActualizarEstadoMesa($pedido->mesaCodigo, 3);
        } catch (Exception $ex) {
            echo $ex;
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);

        }

        $res->getBody()->write(json_encode(array("mensaje" => "El pedido fue cobrado con exito.")));
        return $res->withHeader('Content-Type', 'application/json')->withStatus(200);

    }

    public function AsociarFoto(IRequest $req, IResponse $res, array $args)
    {
        $pedidoId = $args["id"];
        $archivos = $req->getUploadedFiles();
        $imagen = $archivos["foto"];

        if ($imagen->getError() !== UPLOAD_ERR_OK) {
            $res->getBody()->write(json_encode(array("mensaje" => "Hubo un error al obtener la imagen.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $pedido = PedidosRepositorio::ObtenerPedido($pedidoId);

        if ($pedido == false) {
            $res->getBody()->write(json_encode(array("mensaje" => "El pedido no existe.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if ($pedido->estado == 2) {
            $res->getBody()->write(json_encode(array("mensaje" => "El pedido ya fue cobrado.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $this->GuardarFoto("C:\\xampp\htdocs\LaComanda\app\FotosPedidos", $imagen, $pedidoId);
        $res->getBody()->write(json_encode(array("mensaje" => "La foto fue guardada con exito.")));
        return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function GuardarFoto(string $ubicacion, $foto, $pedidoId)
    {
        $extension = pathinfo($foto->getClientFilename(), PATHINFO_EXTENSION);
        $nombreArchivo = "foto_pedido_" . $pedidoId . "." . $extension;
        $foto->moveTo($ubicacion . DIRECTORY_SEPARATOR . $nombreArchivo);
        return $nombreArchivo;
    }

}
