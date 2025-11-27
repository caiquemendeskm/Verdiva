<?php
// services/usuarios.php - Serviço completo de usuários com autenticação

require_once __DIR__ . '/../config/database.php';

class UsuariosService {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function handle($method, $input) {
        // Configurar headers CORS
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        switch ($method) {
            case 'POST':
                // Verificar se é login ou cadastro
                if (isset($input['action']) && $input['action'] === 'login') {
                    $this->login($input);
                } else {
                    $this->cadastrar($input);
                }
                break;
            case 'GET':
                $this->obterUsuario();
                break;
            case 'PUT':
                $this->atualizar($input);
                break;
            case 'DELETE':
                $this->remover($input);
                break;
            default:
                $this->json(['success' => false, 'message' => 'Método inválido'], 405);
        }
    }

    private function cadastrar($data) {
        if (!$data || !isset($data['cpf'], $data['email'], $data['senha'], $data['telefone'])) {
            return $this->json(['success' => false, 'message' => 'Dados obrigatórios faltando.'], 400);
        }

        $cpf = preg_replace('/\D/', '', $data['cpf']);
        $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        $senha = password_hash($data['senha'], PASSWORD_DEFAULT);
        $telefone = preg_replace('/\D/', '', $data['telefone']);

        if (!$email) return $this->json(['success' => false, 'message' => 'E-mail inválido.'], 400);
        if (strlen($cpf) !== 11) return $this->json(['success' => false, 'message' => 'CPF deve ter 11 dígitos.'], 400);

        // Verificar se CPF ou email já existe
        $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE cpf = ? OR email = ?");
        $stmt->execute([$cpf, $email]);
        if ($stmt->rowCount() > 0) {
            return $this->json(['success' => false, 'message' => 'CPF ou e-mail já cadastrado.'], 409);
        }

        try {
            $this->pdo->beginTransaction();
            
            // Inserir usuário
            $sql = "INSERT INTO usuarios (cpf, email, senha, telefone, data_cadastro) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$cpf, $email, $senha, $telefone]);
            
            $usuarioId = $this->pdo->lastInsertId();
            
            // Criar registro de pontos para o novo usuário
            $stmt = $this->pdo->prepare("INSERT INTO pontos_usuario (usuario_id, saldo_pontos, total_acumulado, total_resgatado) VALUES (?, 0, 0, 0)");
            $stmt->execute([$usuarioId]);
            
            $this->pdo->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Cadastro realizado com sucesso!',
                'usuario' => [
                    'id' => $usuarioId,
                    'email' => $email,
                    'cpf' => $cpf
                ]
            ]);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->json(['success' => false, 'message' => 'Erro ao salvar no banco: ' . $e->getMessage()], 500);
        }
    }

    private function login($data) {
        if (!isset($data['email'], $data['senha'])) {
            return $this->json(['success' => false, 'message' => 'Email e senha são obrigatórios.'], 400);
        }

        $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            return $this->json(['success' => false, 'message' => 'E-mail inválido.'], 400);
        }

        // Buscar usuário por email
        $stmt = $this->pdo->prepare("SELECT u.id, u.cpf, u.email, u.senha, u.telefone, u.data_cadastro, 
                                             COALESCE(p.saldo_pontos, 0) as pontos
                                      FROM usuarios u
                                      LEFT JOIN pontos_usuario p ON u.id = p.usuario_id
                                      WHERE u.email = ? LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            return $this->json(['success' => false, 'message' => 'Usuário não encontrado.'], 404);
        }

        // Verificar senha
        if (!password_verify($data['senha'], $usuario['senha'])) {
            return $this->json(['success' => false, 'message' => 'Senha incorreta.'], 401);
        }

        // Remover senha do retorno
        unset($usuario['senha']);

        $this->json([
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'usuario' => $usuario
        ]);
    }

    private function obterUsuario() {
        // Buscar por ID ou email
        $usuarioId = $_GET['id'] ?? null;
        $email = $_GET['email'] ?? null;

        if ($usuarioId) {
            $stmt = $this->pdo->prepare("SELECT u.id, u.cpf, u.email, u.telefone, u.data_cadastro,
                                                 COALESCE(p.saldo_pontos, 0) as saldo_pontos,
                                                 COALESCE(p.total_acumulado, 0) as total_acumulado,
                                                 COALESCE(p.total_resgatado, 0) as total_resgatado
                                          FROM usuarios u
                                          LEFT JOIN pontos_usuario p ON u.id = p.usuario_id
                                          WHERE u.id = ? LIMIT 1");
            $stmt->execute([$usuarioId]);
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                return $this->json(['success' => false, 'message' => 'Usuário não encontrado.'], 404);
            }
            
            $this->json(['success' => true, 'usuario' => $usuario]);
        } elseif ($email) {
            // Para login - retorna com senha
            $stmt = $this->pdo->prepare("SELECT id, cpf, email, senha, telefone FROM usuarios WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            $this->json(['success' => true, 'usuarios' => $usuario ? [$usuario] : []]);
        } else {
            // Listar todos (sem senha)
            $stmt = $this->pdo->query("SELECT u.id, u.cpf, u.email, u.telefone, u.data_cadastro,
                                              COALESCE(p.saldo_pontos, 0) as pontos
                                       FROM usuarios u
                                       LEFT JOIN pontos_usuario p ON u.id = p.usuario_id
                                       ORDER BY u.data_cadastro DESC");
            $usuarios = $stmt->fetchAll();
            
            $this->json(['success' => true, 'usuarios' => $usuarios]);
        }
    }

    private function atualizar($data) {
        if (!isset($data['id'])) {
            return $this->json(['success' => false, 'message' => 'ID do usuário é obrigatório.'], 400);
        }

        $campos = [];
        $valores = [];

        if (isset($data['telefone'])) {
            $campos[] = 'telefone = ?';
            $valores[] = preg_replace('/\D/', '', $data['telefone']);
        }

        if (isset($data['senha'])) {
            $campos[] = 'senha = ?';
            $valores[] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }

        if (empty($campos)) {
            return $this->json(['success' => false, 'message' => 'Nenhum campo para atualizar.'], 400);
        }

        $valores[] = $data['id'];
        $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($valores)) {
            $this->json(['success' => true, 'message' => 'Usuário atualizado com sucesso!']);
        } else {
            $this->json(['success' => false, 'message' => 'Erro ao atualizar usuário.'], 500);
        }
    }

    private function remover($data) {
        if (!isset($data['id'])) {
            return $this->json(['success' => false, 'message' => 'ID do usuário é obrigatório.'], 400);
        }

        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        if ($stmt->execute([$data['id']])) {
            $this->json(['success' => true, 'message' => 'Usuário removido com sucesso!']);
        } else {
            $this->json(['success' => false, 'message' => 'Erro ao remover usuário.'], 500);
        }
    }

    private function json($data, $code = 200) {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
