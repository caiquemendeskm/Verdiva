<?php
// api/deposito.php - Endpoint público para depósitos

// ⚠️ DEBUG (apenas para desenvolvimento – depois você pode remover)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cabeçalhos gerais da API + CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Pré-flight (CORS) – o navegador manda OPTIONS antes do POST/PUT
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

try {
    // ⚠️ aqui está certo, sua pasta é "services" minúsculo
    require_once __DIR__ . '/services/deposito.php';

    // Verifica se a classe foi carregada
    if (!class_exists('DepositoService')) {
        throw new Exception('Classe DepositoService não encontrada. Verifique o caminho do require_once.');
    }

    $service = new DepositoService();

    $method = $_SERVER['REQUEST_METHOD'];

    // Corpo da requisição (JSON)
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    // Se não veio JSON, usa $_POST (form comum)
    if (!is_array($input)) {
        $input = $_POST;
    }

    // Entrega para o service
    $service->handle($method, $input);

} catch (Throwable $e) {
    // Resposta de erro em JSON pra você ver o problema exato
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno no servidor',
        'erro'    => $e->getMessage(),
        'arquivo' => $e->getFile(),
        'linha'   => $e->getLine()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
