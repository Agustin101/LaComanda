<?php

require_once './Repositorios/PedidosRepositorio.php';
require_once './Repositorios/UsuarioRepositorio.php';
require_once "./Repositorios/MesasRepositorio.php";
require_once './Util/Jwt.php';
require_once './models/ProductoPedido.php';

use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Slim\Psr7\Response;

class ClientesController
{
    public function ConsultarTiempoEstimado(IRequest $req, IResponse $res, array $args)
    {
        $params = $req->getQueryParams();
        $mesaId = $params["mesa"];
        $pedidoId = $params["pedido"];

        $pedido = PedidosRepositorio::ObtenerPedido($pedidoId);
        $mesa = MesaRepositorio::ObtenerMesaPorId($mesaId);
        if ($pedido === false) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "El pedido consultado no existe.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if ($mesa === false) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "La mesa consultada no existe.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if ($pedido->estado == 0) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "El pedido se encuentra cancelado.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        } else if ($pedido->estado == 2) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "El pedido ya fue finalizado.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $tiempo = PedidosRepositorio::ObtenerTiempoEstimadoPedido($pedidoId, $mesaId);
        if ($tiempo == false) {
            $res = new Response();
            $res->getBody()->write(json_encode(array("mensaje" => "Aun no se ha comenzado a preparar sus pedidos.")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
        }

        $res->getBody()->write(json_encode(array("mensaje" => "El tiempo estimado de demora es: " . $tiempo )));
        return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
