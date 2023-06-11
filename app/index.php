<?php
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Server\RequestHandlerInterface as IRequestHandler;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
require_once './middlewares/UsuarioMw.php';
require_once './controllers/UsuarioController.php';
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

// Routes
// $app->group('/usuarios', function (RouteCollectorProxy $group) {
//   $group->get('[/]', \UsuarioController::class . ':TraerTodos');
//   $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
//   $group->post('[/]', \UsuarioController::class . ':CargarUno');
// });

// JWT test routes
// $app->group('/jwt', function (RouteCollectorProxy $group) {

//   $group->post('/crearToken', function (IRequest $request, IResponse $response) {    
//     $parametros = $request->getParsedBody();

//     $usuario = $parametros['usuario'];
//     $perfil = $parametros['perfil'];
//     $alias = $parametros['alias'];

//     $datos = array('usuario' => $usuario, 'perfil' => $perfil, 'alias' => $alias);

//     $token = AutentificadorJWT::CrearToken($datos);
//     $payload = json_encode(array('jwt' => $token));

//     $response->getBody()->write($payload);
//     return $response
//       ->withHeader('Content-Type', 'application/json');
//   });

//   $group->get('/devolverPayLoad', function (IRequest $request, IResponse $response) {
//     $header = $request->getHeaderLine('Authorization');
//     $token = trim(explode("Bearer", $header)[1]);

//     try {
//       $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
//     } catch (Exception $e) {
//       $payload = json_encode(array('error' => $e->getMessage()));
//     }

//     $response->getBody()->write($payload);
//     return $response
//       ->withHeader('Content-Type', 'application/json');
//   });

//   $group->get('/devolverDatos', function (IRequest $request, IResponse $response) {
//     $header = $request->getHeaderLine('Authorization');
//     $token = trim(explode("Bearer", $header)[1]);

//     try {
//       $payload = json_encode(array('datos' => AutentificadorJWT::ObtenerData($token)));
//     } catch (Exception $e) {
//       $payload = json_encode(array('error' => $e->getMessage()));
//     }

//     $response->getBody()->write($payload);
//     return $response
//       ->withHeader('Content-Type', 'application/json');
//   });

//   $group->get('/verificarToken', function (IRequest $request, IResponse $response) {
//     $header = $request->getHeaderLine('Authorization');
//     $token = trim(explode("Bearer", $header)[1]);
//     $esValido = false;

//     try {
//       AutentificadorJWT::verificarToken($token);
//       $esValido = true;
//     } catch (Exception $e) {
//       $payload = json_encode(array('error' => $e->getMessage()));
//     }

//     if ($esValido) {
//       $payload = json_encode(array('valid' => $esValido));
//     }

//     $response->getBody()->write($payload);
//     return $response
//       ->withHeader('Content-Type', 'application/json');
//   });
// });


$app->get('[/]', function (IRequest $request, IResponse $response) {
    $payload = json_encode(array("mensaje" => "Slim Framework 4 PHP"));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

//-- TP --

$app->post("/login", \AutorizacionController::class . ":GenerarToken")
    ->add(\UsuarioMw::class . ":VerificarUsuarioExistente")
    ->add(\UsuarioMw::class . ":ValidarCampos");


//ABM USUARIOS

$app->group("/usuarios", function(RouteCollectorProxy $group){
    $group->post("[/]",\UsuarioController::class . ":AgregarUsuario");
    $group->put("/{id}", \UsuarioController::class . ":ModificarUsuario");
    $group->get("[/]", \UsuarioController::class . ":ObtenerUsuarios");
    $group->delete("/{id}[/]", \UsuarioController::class . ":EliminarUsuario");
    $group->get("/{id}[/]", \UsuarioController::class . ":ObtenerUsuario");
})
->add(\SocioMw::class . ":ValidarSocio")
->add(\JwtMw::class . ":ValidarToken");

$app->run();
