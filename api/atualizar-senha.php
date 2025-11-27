<?php

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['email']) || empty($data['nova_senha'])) {
    echo json_encode(['success' => false, 'message' => 'Dados faltando.']);
    exit;
}

$email = $data['email'];
$novaSenha = $data['nova_senha'];

if (strlen($novaSenha) < 6) {
    echo json_encode(['success' => false, 'message' => 'Senha muito curta.']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=verdivabd;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
    $stmt->execute([$hash, $email]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor.']);
}
?>