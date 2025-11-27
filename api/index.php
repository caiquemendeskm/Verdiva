<?php
// index.php - Roteador principal

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'services/usuarios.php';
require_once 'services/materiais.php';
require_once 'services/deposito.php';
require_once 'services/recompensas.php';


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove qualquer coisa até /api (ex: /VerdivaTeste/api, /meusite/api, etc)
$uri = preg_replace('#^(.*/)?api/?#i', '', $uri);

// Remove index.php se aparecer
$uri = preg_replace('#^/?index\.php/?#i', '', $uri);

// Remove barras do começo/fim
$uri = trim($uri, '/');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

$routes = [
    'v1/servico-de-usuarios'              => 'UsuariosService',
    'perfil-usuario'                      => 'PerfilService', 
    'servico-de-usuarios'                 => 'UsuariosService',
    'v1/servico-de-materiais'             => 'MateriaisService',
    'servico-de-materiais'                => 'MateriaisService',
    'v1/servico-de-deposito-de-materiais' => 'DepositoService',
    'servico-de-deposito-de-materiais'    => 'DepositoService',
    'v1/servico-de-recompensa'            => 'RecompensasService',
    'servico-de-recompensa'               => 'RecompensasService',
];

if (isset($routes[$uri])) {
    $service = new $routes[$uri]();
    $service->handle($method, $input);
    exit;
}

if ($uri === '' || $uri === '/') {
    echo json_encode([
        'api' => 'Verdiva API',
        'version' => '1.0',
        'status' => 'online',
        'base_url' => 'http://localhost/VerdivaTeste/',
        'endpoints' => array_keys($routes)
    ], JSON_PRETTY_PRINT);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Endpoint não encontrado', 'uri' => $uri]);