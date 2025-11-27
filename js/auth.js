// auth.js - Gerenciamento de autenticação do usuário

class AuthManager {
    static STORAGE_KEY = 'verdiva_usuario';

    // Salva o usuário logado no localStorage
    static salvarUsuario(usuario) {
        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(usuario));
    }

    // Lê o usuário logado
    static obterUsuario() {
        const data = localStorage.getItem(this.STORAGE_KEY);
        if (!data) return null;
        try {
            return JSON.parse(data);
        } catch (e) {
            console.error('Erro ao parsear usuário do localStorage:', e);
            return null;
        }
    }

    // Remove o usuário logado
    static limparUsuario() {
        localStorage.removeItem(this.STORAGE_KEY);
    }

    // Está logado?
    static estaLogado() {
        return this.obterUsuario() !== null;
    }

    // Pega o ID, independente do nome do campo
    static obterUsuarioId() {
        const usuario = this.obterUsuario();
        if (!usuario) return null;

        // Ajuste aqui se seu backend usa outro nome de campo
        return usuario.id || usuario.usuario_id || usuario.id_usuario || null;
    }

    // Pontos do usuário
    static obterPontosUsuario() {
        const usuario = this.obterUsuario();
        return usuario ? (usuario.saldo_pontos || usuario.pontos || 0) : 0;
    }

    // Atualiza pontos dentro do localStorage
    static atualizarPontos(novosPontos) {
        const usuario = this.obterUsuario();
        if (usuario) {
            usuario.saldo_pontos = novosPontos;
            this.salvarUsuario(usuario);
        }
    }
}
