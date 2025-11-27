<?php

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['senha'], $data['hash'])) {
    echo json_encode(['valid' => false]);
    exit;
}

$valid = password_verify($data['senha'], $data['hash']);
echo json_encode(['valid' => $valid]);
?>