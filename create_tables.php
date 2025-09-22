<?php
require_once __DIR__ . '/config/database.php';
$database = new Database();
$db = $database->getConnection();
$database->createTables();
echo "Tabelas criadas e dados de exemplo inseridos com sucesso!";
?>

