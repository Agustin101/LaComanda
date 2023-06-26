<?php
error_reporting(-1);
ini_set('display_errors', 1);

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
require_once './middlewares/UsuarioMw.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/ProductosController.php';
require_once './controllers/BackupController.php';
require_once './controllers/MesasController.php';
require_once './controllers/PedidosController.php';
require_once './controllers/AutorizacionController.php';
require_once './controllers/ClientesController.php';

require_once './middlewares/JwtMw.php';
require_once './middlewares/SocioMw.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

//localhost/LaComanda/app
$app->setBasePath('/LaComanda/app');
// Add error middleware
$app->addErrorMiddleware(true, true, true);
//-- TP --
$app->addBodyParsingMiddleware();
//LOGIN
$app->post("/login", \AutorizacionController::class . ":GenerarToken")
    ->add(\UsuarioMw::class . ":VerificarUsuarioExistente")
    ->add(\UsuarioMw::class . ":ValidarCampos");

$app->group("/usuarios", function (RouteCollectorProxy $group) {
    $group->post("[/]", \UsuarioController::class . ":AgregarUsuario");
    $group->put("/{id}", \UsuarioController::class . ":ModificarUsuario");
    $group->get("[/]", \UsuarioController::class . ":ObtenerUsuarios");
    $group->delete("/{id}[/]", \UsuarioController::class . ":EliminarUsuario");
    $group->get("/{id}[/]", \UsuarioController::class . ":ObtenerUsuario");
})
    ->add(\SocioMw::class . ":ValidarSocio")
    ->add(\JwtMw::class . ":ValidarToken");

//TODO: Verificar que el sector asignado a un producto exista.
$app->group("/productos", function (RouteCollectorProxy $group) {
    $group->post("[/]", \ProductosController::class . ":AgregarProducto");
    $group->put("/{id}", \ProductosController::class . ":ModificarProducto");
    $group->get("/{id}[/]", \ProductosController::class . ":ObtenerProducto");
    $group->get("[/]", \ProductosController::class . ":ObtenerProductos");
    $group->delete("/{id}[/]", \ProductosController::class . ":EliminarProducto");
})
    ->add(\SocioMw::class . ":ValidarSocio")
    ->add(\JwtMw::class . ":ValidarToken");

$app->group("/mesas", function (RouteCollectorProxy $group) {
    $group->post("[/]", \MesasController::class . ":AgregarMesa");
    $group->put("/{id}", \MesasController::class . ":ModificarMesa");
    $group->get("/{id}[/]", \MesasController::class . ":ObtenerMesa");
    $group->get("[/]", \MesasController::class . ":ObtenerMesas");
    $group->delete("/{id}[/]", \MesasController::class . ":EliminarMesa");
})
    ->add(\SocioMw::class . ":ValidarSocio")
    ->add(\JwtMw::class . ":ValidarToken");

//estado: 0 - cancelado - 1 activo - 2 finalizado
$app->group("/pedidos", function (RouteCollectorProxy $group) {
    $group->post("[/]", \PedidosController::class . ":CrearPedido")
        ->add(\UsuarioMw::class . ":ValidarMozo");

    $group->get("[/]", \PedidosController::class . ":ListarPedidos")
        ->add(\SocioMw::class . ":ValidarSocio");

    $group->get("/sectores", \PedidosController::class . ":PedidosSector");

    $group->put("/{id}", \PedidosController::class . ":ActualizarPedido")
        ->add(\UsuarioMw::class . ":VerificarSector");

    $group->post("/{id}", \PedidosController::class . ":ServirPedido")
        ->add(\UsuarioMw::class . ":ValidarMozo");

    $group->post("/cobros/{id}", \PedidosController::class . ":CobrarPedido")
        ->add(\UsuarioMw::class . ":ValidarMozo");

    $group->post("/foto/{id}", \PedidosController::class . ":AsociarFoto")
        ->add(\UsuarioMw::class . ":ValidarMozo");

})
    ->add(\JwtMw::class . ":ValidarToken");

$app->group("/clientes", function (RouteCollectorProxy $group) {
    $group->get("/", \ClientesController::class . ":ConsultarTiempoEstimado");
});

$app->group("/csv", function (RouteCollectorProxy $group) {
    $group->get("[/]", \BackupController::class . ":DescargarBackup");
    $group->post("[/]", \BackupController::class . ":CargarBackup");
})->add(\SocioMw::class . ":ValidarSocio")
    ->add(\JwtMw::class . ":ValidarToken");

$app->run();
