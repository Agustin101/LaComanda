<?php
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
require_once './middlewares/UsuarioMw.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/ProductosController.php';
require_once './controllers/MesasController.php';
require_once './controllers/AutorizacionController.php';
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

//LOGIN
$app->post("/login", \AutorizacionController::class . ":GenerarToken")
    ->add(\UsuarioMw::class . ":VerificarUsuarioExistente")
    ->add(\UsuarioMw::class . ":ValidarCampos");

//ABM USUARIOS

//TODO: Verificar que el usuario no existe antes de dar alta
$app->group("/usuarios", function (RouteCollectorProxy $group) {
    $group->post("[/]", \UsuarioController::class . ":AgregarUsuario");
    $group->put("/{id}", \UsuarioController::class . ":ModificarUsuario");
    $group->get("[/]", \UsuarioController::class . ":ObtenerUsuarios");
    $group->delete("/{id}[/]", \UsuarioController::class . ":EliminarUsuario");
    $group->get("/{id}[/]", \UsuarioController::class . ":ObtenerUsuario");
})
    ->add(\SocioMw::class . ":ValidarSocio")
    ->add(\JwtMw::class . ":ValidarToken");

//ABM PRODUCTOS
//TODO: Verificar que el producto no existe antes de dar alta
$app->group("/productos", function (RouteCollectorProxy $group) {
    $group->post("[/]", \ProductosController::class . ":AgregarProducto");
    $group->put("/{id}", \ProductosController::class . ":ModificarProducto");
    $group->get("/{id}[/]", \ProductosController::class . ":ObtenerProducto");
    $group->get("[/]", \ProductosController::class . ":ObtenerProductos");
    $group->delete("/{id}[/]", \ProductosController::class . ":EliminarProducto");
})
    ->add(\SocioMw::class . ":ValidarSocio")
    ->add(\JwtMw::class . ":ValidarToken");

//ABM MESAS
$app->group("/mesas", function (RouteCollectorProxy $group) {
    $group->post("[/]", \MesasController::class . ":AgregarMesa");
    $group->put("/{id}", \MesasController::class . ":ModificarMesa");
    $group->get("/{id}[/]", \MesasController::class . ":ObtenerMesa");
    $group->get("[/]", \MesasController::class . ":ObtenerMesas");
    $group->delete("/{id}[/]", \MesasController::class . ":EliminarMesa");
})
    ->add(\SocioMw::class . ":ValidarSocio")
    ->add(\JwtMw::class . ":ValidarToken");

$app->run();
