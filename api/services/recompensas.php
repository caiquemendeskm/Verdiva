<?php
// services/recompensas.php - Serviço de recompensas integrado ao banco de dados

require_once __DIR__ . '/../config/database.php';

class RecompensasService {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function handle($method, $input) {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        switch ($method) {
            case 'GET':
                // Verificar se é para listar recompensas ou resgates
                if (isset($_GET['resgates'])) {
                    $this->getResgates();
                } else {
                    $this->getRecompensas();
                }
                break;
                
            case 'POST':
                $this->resgatarPontos($input);
                break;
                
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Método não permitido']);
                break;
        }
    }
    
    private function getRecompensas() {
        try {
            $usuarioId = $_GET['usuario_id'] ?? null;
            $categoria = $_GET['categoria'] ?? null;
            
            // Buscar pontos do usuário se fornecido
            $pontosUsuario = 0;
            if ($usuarioId) {
                $stmt = $this->pdo->prepare("SELECT saldo_pontos FROM pontos_usuario WHERE usuario_id = ?");
                $stmt->execute([$usuarioId]);
                $pontos = $stmt->fetch();
                $pontosUsuario = $pontos['saldo_pontos'] ?? 0;
            }
            
            // Buscar recompensas
            $sql = "SELECT * FROM recompensas WHERE disponivel = TRUE";
            if ($categoria) {
                $sql .= " AND categoria = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$categoria]);
            } else {
                $stmt = $this->pdo->query($sql);
            }
            
            $recompensas = $stmt->fetchAll();
            
            // Formatar recompensas
            $recompensasFormatadas = array_map(function($r) use ($pontosUsuario) {
                return [
                    'id' => (int)$r['id'],
                    'nome' => $r['nome'],
                    'descricao' => $r['descricao'],
                    'pontosNecessarios' => (int)$r['pontos_necessarios'],
                    'valor' => 'R$ ' . number_format($r['valor_reais'], 2, ',', '.'),
                    'categoria' => $r['categoria'],
                    'parceiro' => $r['parceiro'],
                    'validadeDias' => (int)$r['validade_dias'],
                    'disponivel' => (bool)$r['disponivel'],
                    'termos' => $r['termos'],
                    'podeResgatar' => $pontosUsuario >= $r['pontos_necessarios']
                ];
            }, $recompensas);
            
            // Agrupar por categoria
            $categorias = array_unique(array_column($recompensas, 'categoria'));
            $descricaoCategorias = [
                'alimentacao' => 'Descontos em supermercados e restaurantes',
                'moda' => 'Roupas e acessórios sustentáveis',
                'transporte' => 'Créditos para transporte público',
                'entretenimento' => 'Cinema, teatro e eventos',
                'jardinagem' => 'Produtos para cultivo urbano',
                'educacao' => 'Cursos e workshops sobre sustentabilidade'
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Recompensas disponíveis obtidas com sucesso',
                'data' => $recompensasFormatadas,
                'total' => count($recompensasFormatadas),
                'pontosUsuario' => $pontosUsuario,
                'categorias' => $descricaoCategorias,
                'info' => [
                    'conversao' => '100 pontos = R$ 1,00',
                    'pontuacaoMinima' => 500,
                    'validadeMedia' => '30-90 dias',
                    'novosParceirosMensais' => 2
                ],
                'timestamp' => date('c')
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar recompensas: ' . $e->getMessage()
            ]);
        }
    }
    
    private function getResgates() {
        try {
            $usuarioId = $_GET['usuario_id'] ?? null;
            
            if (!$usuarioId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID do usuário é obrigatório'
                ]);
                return;
            }
            
            // Buscar histórico de resgates
            $stmt = $this->pdo->prepare("
                SELECT r.*, rec.nome as recompensa_nome, rec.categoria, rec.parceiro
                FROM resgates r
                JOIN recompensas rec ON r.recompensa_id = rec.id
                WHERE r.usuario_id = ?
                ORDER BY r.criado_em DESC
            ");
            $stmt->execute([$usuarioId]);
            $resgates = $stmt->fetchAll();
            
            $resgatesFormatados = array_map(function($r) {
                return [
                    'id' => (int)$r['id'],
                    'recompensaNome' => $r['recompensa_nome'],
                    'categoria' => $r['categoria'],
                    'parceiro' => $r['parceiro'],
                    'pontosUtilizados' => (int)$r['pontos_utilizados'],
                    'valorReais' => 'R$ ' . number_format($r['valor_reais'], 2, ',', '.'),
                    'codigoDesconto' => $r['codigo_desconto'],
                    'transacaoId' => $r['transacao_id'],
                    'validoAte' => $r['valido_ate'],
                    'status' => $r['status'],
                    'criadoEm' => date('c', strtotime($r['criado_em']))
                ];
            }, $resgates);
            
            echo json_encode([
                'success' => true,
                'message' => 'Histórico de resgates obtido com sucesso',
                'data' => $resgatesFormatados,
                'total' => count($resgatesFormatados),
                'timestamp' => date('c')
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar resgates: ' . $e->getMessage()
            ]);
        }
    }
    
    private function resgatarPontos($input) {
        if (!$input || !isset($input['usuario_id'], $input['recompensa_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Dados obrigatórios: usuario_id, recompensa_id'
            ]);
            return;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            $usuarioId = (int)$input['usuario_id'];
            $recompensaId = (int)$input['recompensa_id'];
            
            // Buscar pontos do usuário
            $stmt = $this->pdo->prepare("SELECT saldo_pontos FROM pontos_usuario WHERE usuario_id = ?");
            $stmt->execute([$usuarioId]);
            $pontos = $stmt->fetch();
            
            if (!$pontos) {
                $this->pdo->rollBack();
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Usuário não encontrado'
                ]);
                return;
            }
            
            $saldoPontos = $pontos['saldo_pontos'];
            
            // Buscar recompensa
            $stmt = $this->pdo->prepare("SELECT * FROM recompensas WHERE id = ? AND disponivel = TRUE");
            $stmt->execute([$recompensaId]);
            $recompensa = $stmt->fetch();
            
            if (!$recompensa) {
                $this->pdo->rollBack();
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Recompensa não encontrada ou indisponível'
                ]);
                return;
            }
            
            $pontosNecessarios = $recompensa['pontos_necessarios'];
            
            // Verificar se tem pontos suficientes
            if ($saldoPontos < $pontosNecessarios) {
                $this->pdo->rollBack();
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Pontos insuficientes para resgate',
                    'pontosDisponiveis' => $saldoPontos,
                    'pontosNecessarios' => $pontosNecessarios,
                    'faltam' => $pontosNecessarios - $saldoPontos
                ]);
                return;
            }
            
            // Gerar código de desconto único
            $codigoDesconto = $this->gerarCodigoDesconto($pontosNecessarios);
            $transacaoId = 'RSG' . time() . rand(10000, 99999);
            $validoAte = date('Y-m-d', strtotime('+' . $recompensa['validade_dias'] . ' days'));
            
            // Inserir resgate (o trigger vai atualizar os pontos automaticamente)
            $stmt = $this->pdo->prepare("
                INSERT INTO resgates (usuario_id, recompensa_id, pontos_utilizados, valor_reais, 
                                     codigo_desconto, transacao_id, valido_ate, status, criado_em)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
            ");
            
            $stmt->execute([
                $usuarioId,
                $recompensaId,
                $pontosNecessarios,
                $recompensa['valor_reais'],
                $codigoDesconto,
                $transacaoId,
                $validoAte
            ]);
            
            $resgateId = $this->pdo->lastInsertId();
            
            // Buscar pontos atualizados
            $stmt = $this->pdo->prepare("SELECT saldo_pontos FROM pontos_usuario WHERE usuario_id = ?");
            $stmt->execute([$usuarioId]);
            $pontosAtualizados = $stmt->fetch();
            
            $this->pdo->commit();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Pontos resgatados com sucesso!',
                'data' => [
                    'resgateId' => $resgateId,
                    'Recompensa' => [
                        'Total-Pontos' => (string)$pontosAtualizados['saldo_pontos'],
                        'Resgatar-pontos' => (string)$pontosNecessarios
                    ],
                    'transacao' => [
                        'id' => $transacaoId,
                        'tipo' => 'resgate',
                        'valor' => 'R$ ' . number_format($recompensa['valor_reais'], 2, ',', '.'),
                        'pontosUtilizados' => $pontosNecessarios,
                        'recompensa' => $recompensa['nome'],
                        'parceiro' => $recompensa['parceiro'],
                        'categoria' => $recompensa['categoria'],
                        'codigoDesconto' => $codigoDesconto,
                        'validoAte' => $validoAte,
                        'instrucoes' => $recompensa['instrucoes']
                    ],
                    'detalhesUso' => [
                        'comoUsar' => 'Apresente o código no estabelecimento parceiro',
                        'restricoes' => $recompensa['termos'],
                        'suporte' => 'WhatsApp: (11) 99999-8888'
                    ],
                    'economiaAmbiental' => [
                        'equivalenteKgReciclados' => number_format($pontosNecessarios / 10, 1),
                        'co2Evitado' => number_format($pontosNecessarios * 0.05, 1) . 'kg'
                    ],
                    'timestamp' => date('c'),
                    'status' => 'success'
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao resgatar pontos: ' . $e->getMessage()
            ]);
        }
    }
    
    private function gerarCodigoDesconto($pontos) {
        $prefixes = ['VERDE', 'ECO', 'SUST', 'RECIC', 'VIDA'];
        $prefix = $prefixes[array_rand($prefixes)];
        $numero = str_pad($pontos, 4, '0', STR_PAD_LEFT);
        $sufixo = strtoupper(substr(md5(time() . $pontos . rand()), 0, 3));
        
        return $prefix . $numero . $sufixo;
    }
}
