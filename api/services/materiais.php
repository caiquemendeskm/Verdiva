<?php
// services/materiais.php - Serviço de materiais integrado ao banco de dados

require_once __DIR__ . '/../config/database.php';

class MateriaisService {
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
                $this->getMateriais();
                break;
            case 'POST':
                $this->adicionarMaterial($input);
                break;
            case 'PUT':
                $this->atualizarMaterial($input);
                break;
            default:
                http_response_code(405);
                echo json_encode([
                    'success' => false,
                    'error' => 'Método não permitido. Use GET para consultar materiais.'
                ]);
                break;
        }
    }
    
    private function getMateriais() {
        try {
            // Buscar material específico por ID ou tipo
            $materialId = $_GET['id'] ?? null;
            $tipo = $_GET['tipo'] ?? null;
            
            if ($materialId) {
                $stmt = $this->pdo->prepare("SELECT * FROM materiais WHERE id = ?");
                $stmt->execute([$materialId]);
                $material = $stmt->fetch();
                
                if (!$material) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Material não encontrado']);
                    return;
                }
                
                echo json_encode([
                    'success' => true,
                    'material' => $this->formatarMaterial($material)
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if ($tipo) {
                $stmt = $this->pdo->prepare("SELECT * FROM materiais WHERE tipo = ? AND status = 'accepted'");
                $stmt->execute([$tipo]);
                $material = $stmt->fetch();
                
                if (!$material) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Material não encontrado']);
                    return;
                }
                
                echo json_encode([
                    'success' => true,
                    'material' => $this->formatarMaterial($material)
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Listar todos os materiais aceitos
            $stmt = $this->pdo->query("SELECT * FROM materiais WHERE status = 'accepted' ORDER BY tipo");
            $materiais = $stmt->fetchAll();
            
            $materiaisFormatados = array_map([$this, 'formatarMaterial'], $materiais);
            
            echo json_encode([
                'success' => true,
                'message' => 'Materiais disponíveis para depósito',
                'data' => $materiaisFormatados,
                'total' => count($materiaisFormatados),
                'info' => [
                    'sistema_pontos' => 'Pontos calculados por peso ou unidade',
                    'conversao' => '100 pontos = R$ 1,00',
                    'horario_funcionamento' => '24 horas por dia'
                ],
                'timestamp' => date('c')
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar materiais: ' . $e->getMessage()
            ]);
        }
    }
    
    private function formatarMaterial($material) {
        return [
            'id' => (int)$material['id'],
            'Material' => [
                'Tipo' => $material['tipo'],
                'Categoria' => $material['categoria']
            ],
            'pontosConfig' => [
                'porKg' => (float)$material['pontos_por_kg'],
                'porUnidade' => $material['pontos_por_unidade'] ? (float)$material['pontos_por_unidade'] : null,
                'minimo' => $material['peso_minimo_gramas'] ? $material['peso_minimo_gramas'] . 'g' : '1 unidade'
            ],
            'status' => $material['status'],
            'instrucoes' => $material['instrucoes']
        ];
    }
    
    private function adicionarMaterial($input) {
        if (!isset($input['tipo'], $input['categoria'], $input['pontos_por_kg'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Campos obrigatórios: tipo, categoria, pontos_por_kg'
            ]);
            return;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO materiais (tipo, categoria, pontos_por_kg, pontos_por_unidade, peso_minimo_gramas, status, instrucoes)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $input['tipo'],
                $input['categoria'],
                $input['pontos_por_kg'],
                $input['pontos_por_unidade'] ?? null,
                $input['peso_minimo_gramas'] ?? 0,
                $input['status'] ?? 'accepted',
                $input['instrucoes'] ?? ''
            ]);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Material adicionado com sucesso',
                'id' => $this->pdo->lastInsertId()
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao adicionar material: ' . $e->getMessage()
            ]);
        }
    }
    
    private function atualizarMaterial($input) {
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID do material é obrigatório'
            ]);
            return;
        }
        
        $campos = [];
        $valores = [];
        
        $camposPermitidos = ['categoria', 'pontos_por_kg', 'pontos_por_unidade', 'peso_minimo_gramas', 'status', 'instrucoes'];
        
        foreach ($camposPermitidos as $campo) {
            if (isset($input[$campo])) {
                $campos[] = "$campo = ?";
                $valores[] = $input[$campo];
            }
        }
        
        if (empty($campos)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Nenhum campo para atualizar'
            ]);
            return;
        }
        
        $valores[] = $input['id'];
        
        try {
            $sql = "UPDATE materiais SET " . implode(', ', $campos) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($valores);
            
            echo json_encode([
                'success' => true,
                'message' => 'Material atualizado com sucesso'
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao atualizar material: ' . $e->getMessage()
            ]);
        }
    }
}
