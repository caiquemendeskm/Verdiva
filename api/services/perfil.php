<?php
// services/perfil.php - Serviço de perfil do usuário

require_once __DIR__ . '/../config/database.php';

class PerfilService {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function handle($method, $input) {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        if ($method !== 'GET') {
            return $this->json(['success' => false, 'message' => 'Método inválido'], 405);
        }

        // id do usuário vem da query string: ?usuario_id=8
        $usuarioId = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 0;

        if ($usuarioId <= 0) {
            return $this->json(['success' => false, 'message' => 'ID de usuário obrigatório'], 400);
        }

        try {
            // 1) Dados do usuário + pontos
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.id,
                    u.cpf,
                    u.email,
                    u.telefone,
                    u.data_cadastro,
                    COALESCE(p.saldo_pontos, 0)       AS saldo_pontos,
                    COALESCE(p.total_acumulado, 0)    AS total_acumulado,
                    COALESCE(p.total_resgatado, 0)    AS total_resgatado
                FROM usuarios u
                LEFT JOIN pontos_usuario p ON p.usuario_id = u.id
                WHERE u.id = ?
                LIMIT 1
            ");
            $stmt->execute([$usuarioId]);
            $usuario = $stmt->fetch();

            if (!$usuario) {
                return $this->json(['success' => false, 'message' => 'Usuário não encontrado'], 404);
            }

            // 2) Histórico (últimos depósitos e resgates)
            $stmtHist = $this->pdo->prepare("
                SELECT
                    'deposito' AS tipo,
                    d.criado_em AS data,
                    m.categoria AS descricao,
                    d.pontos_ganhos AS pontos
                FROM depositos d
                INNER JOIN materiais m ON m.id = d.material_id
                WHERE d.usuario_id = ?
                
                UNION ALL
                
                SELECT
                    'resgate' AS tipo,
                    r.criado_em AS data,
                    rec.nome AS descricao,
                    -r.pontos_utilizados AS pontos
                FROM resgates r
                INNER JOIN recompensas rec ON rec.id = r.recompensa_id
                WHERE r.usuario_id = ?
                
                ORDER BY data DESC
                LIMIT 10
            ");
            $stmtHist->execute([$usuarioId, $usuarioId]);
            $historico = $stmtHist->fetchAll();

            $this->json([
                'success'  => true,
                'perfil'   => [
                    'usuario'   => $usuario,
                    'historico' => $historico
                ]
            ]);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Erro ao carregar perfil',
                'detalhe' => $e->getMessage()
            ], 500);
        }
    }

    private function json($data, $code = 200) {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
