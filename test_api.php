<?php

/**
 * Script de teste para a API Verdiva em PHP
 */

$BASE_URL = "http://localhost:8000/api/v1";

function makeRequest($method, $url, $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return array(
        'status' => $httpCode,
        'body' => json_decode($response, true)
    );
}

function testAPI() {
    global $BASE_URL;
    
    echo "=== Testando API Verdiva PHP ===\n\n";
    
    // Teste 1: Criar usuário
    echo "1. Testando criação de usuário...\n";
    $userData = array(
        "Usuario" => array(
            "Nome" => "João da Silva",
            "Email" => "joao.silva@example.com",
            "CPF" => "25417896550"
        )
    );
    
    $response = makeRequest('POST', "$BASE_URL/servico-de-usuarios", $userData);
    echo "Status: " . $response['status'] . "\n";
    echo "Resposta: " . json_encode($response['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if ($response['status'] == 201) {
        $userId = $response['body']['Usuario']['id'];
        echo "✅ Usuário criado com sucesso!\n\n";
    } else {
        echo "❌ Erro ao criar usuário\n\n";
        return;
    }
    
    // Teste 2: Listar materiais
    echo "2. Testando listagem de materiais...\n";
    $response = makeRequest('GET', "$BASE_URL/servico-de-materiais");
    echo "Status: " . $response['status'] . "\n";
    echo "Resposta: " . json_encode($response['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo "✅ Materiais listados com sucesso!\n\n";
    
    // Teste 3: Criar depósito
    echo "3. Testando criação de depósito...\n";
    $depositoData = array(
        "Registro" => array(
            "Material" => "papel",
            "Quantidade" => "1",
            "user_id" => $userId
        )
    );
    
    $response = makeRequest('POST', "$BASE_URL/servico-de-deposito-de-materiais", $depositoData);
    echo "Status: " . $response['status'] . "\n";
    echo "Resposta: " . json_encode($response['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo "✅ Depósito criado com sucesso!\n\n";
    
    // Teste 4: Listar recompensas
    echo "4. Testando listagem de recompensas...\n";
    $response = makeRequest('GET', "$BASE_URL/servico-de-recompensa");
    echo "Status: " . $response['status'] . "\n";
    echo "Resposta: " . json_encode($response['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo "✅ Recompensas listadas com sucesso!\n\n";
    
    // Teste 5: Consultar pontos do usuário
    echo "5. Testando consulta de pontos do usuário...\n";
    $response = makeRequest('GET', "$BASE_URL/servico-de-recompensa/usuario/$userId");
    echo "Status: " . $response['status'] . "\n";
    echo "Resposta: " . json_encode($response['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo "✅ Pontos consultados com sucesso!\n\n";
    
    // Teste 6: Criar mais depósitos para acumular pontos
    echo "6. Testando depósito por peso...\n";
    $depositoData2 = array(
        "Registro" => array(
            "Material" => "vidro",
            "Peso" => "2kg",
            "user_id" => $userId
        )
    );
    
    $response = makeRequest('POST', "$BASE_URL/servico-de-deposito-de-materiais", $depositoData2);
    echo "Status: " . $response['status'] . "\n";
    echo "Resposta: " . json_encode($response['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo "✅ Depósito por peso criado com sucesso!\n\n";
    
    echo "=== Testes concluídos ===\n";
}

testAPI();

?>

