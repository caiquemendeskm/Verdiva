<?php
// services/deposito.php - Serviço de depósito integrado ao banco de dados

require_once __DIR__ . '/../config/database.php';

class DepositoService {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function handle($method, $input) {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // 🔹 Responde o pré-flight do navegador (CORS)
        if ($method === 'OPTIONS') {
            http_response_code(204); // No Content
            exit;
        }
        
        switch ($method) {
            case 'GET':
                $this->getHistoricoDepositos();
                break;
                
            case 'POST':
                $this->registrarDeposito($input);
                break;
                
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Método não permitido']);
                break;
        }
    }
    
    private function getHistoricoDepositos() {
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
            
            // Buscar histórico de depósitos do usuário
            $stmt = $this->pdo->prepare("
                SELECT d.id, d.usuario_id, d.peso_gramas, d.quantidade_unidades, 
                       d.pontos_ganhos, d.maquina_id, d.localizacao, d.transacao_id, 
                       d.status, d.criado_em,
                       m.tipo as material_tipo, m.categoria as material_categoria
                FROM depositos d
                JOIN materiais m ON d.material_id = m.id
                WHERE d.usuario_id = ?
                ORDER BY d.criado_em DESC
            ");
            $stmt->execute([$usuarioId]);
            $depositos = $stmt->fetchAll();
            
            // Buscar pontos totais do usuário
            $stmt = $this->pdo->prepare("
                SELECT saldo_pontos, total_acumulado, total_resgatado
                FROM pontos_usuario
                WHERE usuario_id = ?
            ");
            $stmt->execute([$usuarioId]);
            $pontos = $stmt->fetch();
            
            // Formatar histórico
            $historicoFormatado = array_map(function($deposito) use ($pontos) {
                return [
                    'id' => (int)$deposito['id'],
                    'usuarioId' => (int)$deposito['usuario_id'],
                    'maquinaId' => $deposito['maquina_id'],
                    'Registro' => [
                        'Material' => ucfirst($deposito['material_tipo']),
                        'Quantidade' => $this->formatarQuantidade($deposito['peso_gramas'], $deposito['quantidade_unidades']),
                        'Pontos' => (string)$deposito['pontos_ganhos'],
                        'Total-Pontos' => (string)($pontos['saldo_pontos'] ?? 0)
                    ],
                    'detalhes' => [
                        'peso' => (int)$deposito['peso_gramas'],
                        'unidades' => (int)$deposito['quantidade_unidades'],
                        'categoria' => $deposito['material_categoria'],
                        'pontosGanhos' => (int)$deposito['pontos_ganhos']
                    ],
                    'timestamp' => date('c', strtotime($deposito['criado_em'])),
                    'transacaoId' => $deposito['transacao_id'],
                    'status' => $deposito['status'],
                    'localizacao' => $deposito['localizacao']
                ];
            }, $depositos);
            
            // Calcular estatísticas
            $totalPontos = array_sum(array_column($depositos, 'pontos_ganhos'));
            $pesoTotal = array_sum(array_column($depositos, 'peso_gramas'));
            
            echo json_encode([
                'success' => true,
                'message' => 'Histórico de depósitos obtido com sucesso',
                'data' => $historicoFormatado,
                'total' => count($historicoFormatado),
                'estatisticas' => [
                    'saldoPontos' => (int)($pontos['saldo_pontos'] ?? 0),
                    'totalAcumulado' => (int)($pontos['total_acumulado'] ?? 0),
                    'totalResgatado' => (int)($pontos['total_resgatado'] ?? 0),
                    'totalMateriais' => count($depositos),
                    'pesoTotal' => number_format($pesoTotal / 1000, 2) . 'kg',
                    'economiaAmbiente' => 'CO2 evitado: ' . number_format($pesoTotal * 0.0005, 1) . 'kg'
                ],
                'timestamp' => date('c')
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar histórico: ' . $e->getMessage()
            ]);
        }
    }
    
    private function registrarDeposito($input) {
        if (!$input || !isset($input['usuario_id'], $input['material_tipo'], $input['peso_gramas'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Dados obrigatórios: usuario_id, material_tipo, peso_gramas'
            ]);
            return;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            $usuarioId = (int)$input['usuario_id'];
            $materialTipo = strtolower($input['material_tipo']);
            $pesoGramas = (int)$input['peso_gramas'];
            $quantidadeUnidades = isset($input['quantidade_unidades']) ? (int)$input['quantidade_unidades'] : 1;
            
            // Buscar informações do material
            $stmt = $this->pdo->prepare("SELECT * FROM materiais WHERE tipo = ? AND status = 'accepted'");
            $stmt->execute([$materialTipo]);
            $material = $stmt->fetch();
            
            if (!$material) {
                $this->pdo->rollBack();
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Material não encontrado ou não aceito'
                ]);
                return;
            }
            
            // Calcular pontos
            $pontosPorPeso = ($pesoGramas / 1000) * $material['pontos_por_kg'];
            $pontosPorUnidade = $material['pontos_por_unidade'] ? ($quantidadeUnidades * $material['pontos_por_unidade']) : 0;
            $pontosGanhos = (int)max($pontosPorPeso, $pontosPorUnidade);
            
            // Gerar IDs únicos
            $transacaoId = 'TXN' . time() . rand(1000, 9999);
            $maquinasDisponiveis = ['VRD001', 'VRD002', 'VRD003', 'VRD004'];
            $maquinaId = $input['maquina_id'] ?? $maquinasDisponiveis[array_rand($maquinasDisponiveis)];
            $localizacao = $this->getLocalizacaoMaquina($maquinaId);
            
            // Inserir depósito
            $stmt = $this->pdo->prepare("
                INSERT INTO depositos (usuario_id, material_id, peso_gramas, quantidade_unidades, 
                                      pontos_ganhos, maquina_id, localizacao, transacao_id, status, criado_em)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'processed', NOW())
            ");
            
            $stmt->execute([
                $usuarioId,
                $material['id'],
                $pesoGramas,
                $quantidadeUnidades,
                $pontosGanhos,
                $maquinaId,
                $localizacao,
                $transacaoId
            ]);
            
            $depositoId = $this->pdo->lastInsertId();
            
            // Buscar pontos atualizados (o trigger já atualizou)
            $stmt = $this->pdo->prepare("SELECT saldo_pontos FROM pontos_usuario WHERE usuario_id = ?");
            $stmt->execute([$usuarioId]);
            $pontos = $stmt->fetch();
            $totalPontos = $pontos['saldo_pontos'];
            
            $this->pdo->commit();
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Depósito registrado com sucesso',
                'data' => [
                    'id' => $depositoId,
                    'usuarioId' => $usuarioId,
                    'maquinaId' => $maquinaId,
                    'Registro' => [
                        'Material' => ucfirst($materialTipo),
                        'Quantidade' => $this->formatarQuantidade($pesoGramas, $quantidadeUnidades),
                        'Pontos' => (string)$pontosGanhos,
                        'Total-Pontos' => (string)$totalPontos
                    ],
                    'detalhes' => [
                        'peso' => $pesoGramas,
                        'unidades' => $quantidadeUnidades,
                        'categoria' => $material['categoria'],
                        'pontosGanhos' => $pontosGanhos,
                        'valorEquivalente' => 'R$ ' . number_format($pontosGanhos * 0.01, 2, ',', '.')
                    ],
                    'timestamp' => date('c'),
                    'transacaoId' => $transacaoId,
                    'status' => 'processed',
                    'localizacao' => $localizacao,
                    'impactoAmbiental' => [
                        'co2Evitado' => number_format($pesoGramas * 0.0005, 1) . 'kg',
                        'aguaEconomizada' => number_format($pesoGramas * 0.0023, 1) . 'L'
                    ]
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao registrar depósito: ' . $e->getMessage()
            ]);
        }
    }
    
    private function formatarQuantidade($pesoGramas, $unidades) {
        if ($pesoGramas >= 1000) {
            return number_format($pesoGramas / 1000, 2) . 'kg';
        } elseif ($unidades > 1) {
            return $unidades . ' unidades (' . $pesoGramas . 'g)';
        } else {
            return $pesoGramas . 'g';
        }
    }
    
    private function getLocalizacaoMaquina($maquinaId) {
        $localizacoes = [
            'VRD001' => 'Shopping Verde - Piso 2',
            'VRD002' => 'Supermercado Eco - Entrada',
            'VRD003' => 'Estação Metro Verdiva - Hall Principal',
            'VRD004' => 'Universidade Sustentável - Biblioteca'
        ];
        
        return $localizacoes[$maquinaId] ?? 'Localização não identificada';
    }
}
