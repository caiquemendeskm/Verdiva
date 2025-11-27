<?php
// config/database.php - Configuração centralizada do banco de dados

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        // CONFIGURAÇÃO PADRÃO DO XAMPP (LOCALHOST)
        $host = 'localhost';
        $db   = 'verdivabd';  // Nome do banco que está no phpMyAdmin
        $user = 'root';       // Usuário padrão do XAMPP
        $pass = '';           // Senha vazia (SEM senha)

        try {
            // Conexão com o banco via PDO
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8mb4",
                $user,
                $pass
            );

            // Atributos de segurança
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Exibe erro detalhado (apenas para desenvolvimento)
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro de conexão com o banco de dados',
                'detalhe' => $e->getMessage()  // <- Pode ajudar a identificar o problema se persistir
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}
