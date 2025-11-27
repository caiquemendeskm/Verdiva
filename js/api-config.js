// api-config.js - Configuração da API

const API_CONFIG = {
    baseURL: 'http://localhost/VerdivaTeste/api',
    endpoints: {
        usuarios: '/servico-de-usuarios',
        materiais: '/servico-de-materiais',
        depositos: '/servico-de-deposito-de-materiais',
        recompensas: '/servico-de-recompensa'
    }
};

class APIClient {
    static async request(endpoint, options = {}) {
        const url = `${API_CONFIG.baseURL}${endpoint}`;
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };
        
        const finalOptions = { ...defaultOptions, ...options };
        
        if (finalOptions.body && typeof finalOptions.body === 'object') {
            finalOptions.body = JSON.stringify(finalOptions.body);
        }
        
        try {
            const response = await fetch(url, finalOptions);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || data.error || 'Erro na requisição');
            }
            
            return data;
        } catch (error) {
            console.error('Erro na requisição:', error);
            throw error;
        }
    }
    
    static async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url, { method: 'GET' });
    }
    
    static async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: data
        });
    }
    
    static async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: data
        });
    }
    
    static async delete(endpoint, data) {
        return this.request(endpoint, {
            method: 'DELETE',
            body: data
        });
    }
}

// Serviços específicos
class UsuariosService {
    static async cadastrar(dados) {
        return APIClient.post(API_CONFIG.endpoints.usuarios, dados);
    }
    
    static async login(email, senha) {
        const response = await APIClient.post(API_CONFIG.endpoints.usuarios, {
            action: 'login',
            email,
            senha
        });
        
        if (response.success && response.usuario) {
            AuthManager.salvarUsuario(response.usuario);
        }
        
        return response;
    }
    
    static async obterPerfil(usuarioId) {
        return APIClient.get(API_CONFIG.endpoints.usuarios, { id: usuarioId });
    }
    
    static logout() {
        AuthManager.limparUsuario();
        window.location.href = 'login.html';
    }
}

class MateriaisService {
    static async listar() {
        return APIClient.get(API_CONFIG.endpoints.materiais);
    }
    
    static async obterPorTipo(tipo) {
        return APIClient.get(API_CONFIG.endpoints.materiais, { tipo });
    }
}

class DepositosService {
    static async registrar(usuarioId, materialTipo, pesoGramas, quantidadeUnidades = 1) {
        return APIClient.post(API_CONFIG.endpoints.depositos, {
            usuario_id: usuarioId,
            material_tipo: materialTipo,
            peso_gramas: pesoGramas,
            quantidade_unidades: quantidadeUnidades
        });
    }
    
    static async obterHistorico(usuarioId) {
        return APIClient.get(API_CONFIG.endpoints.depositos, { usuario_id: usuarioId });
    }
}

class RecompensasService {
    static async listar(usuarioId = null) {
        const params = usuarioId ? { usuario_id: usuarioId } : {};
        return APIClient.get(API_CONFIG.endpoints.recompensas, params);
    }
    
    static async resgatar(usuarioId, recompensaId) {
        return APIClient.post(API_CONFIG.endpoints.recompensas, {
            usuario_id: usuarioId,
            recompensa_id: recompensaId
        });
    }
    
    static async obterResgates(usuarioId) {
        return APIClient.get(API_CONFIG.endpoints.recompensas, {
            resgates: true,
            usuario_id: usuarioId
        });
    }
}

// Função para verificar se o usuário está logado
function verificarAutenticacao() {
    if (!AuthManager.estaLogado()) {
        window.location.href = 'login.html';
        return false;
    }
    return true;
}

// Função para exibir mensagens
function mostrarMensagem(mensagem, tipo = 'info') {
    // Criar elemento de notificação
    const notificacao = document.createElement('div');
    notificacao.className = `notificacao notificacao-${tipo}`;
    notificacao.textContent = mensagem;
    notificacao.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${tipo === 'success' ? '#4CAF50' : tipo === 'error' ? '#f44336' : '#2196F3'};
        color: white;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notificacao);
    
    setTimeout(() => {
        notificacao.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notificacao.remove(), 300);
    }, 3000);
}

// Adicionar estilos de animação
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
