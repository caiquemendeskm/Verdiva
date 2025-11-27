// perfil.js - Página de Perfil do Usuário

document.addEventListener('DOMContentLoaded', async () => {
    // Garante que está logado
    if (!verificarAutenticacao()) {
        return; // verificarAutenticacao já redireciona para login se precisar
    }

    // Atualiza banner de pontos (topo)
    atualizarPontosInterfacePerfil();

    // Carrega dados do usuário na tela
    await carregarPerfil();

    // Configura botão de logout
    configurarLogout();
});

async function carregarPerfil() {
    try {
        const usuarioId = AuthManager.obterUsuarioId();
        if (!usuarioId) {
            mostrarMensagem('Usuário não identificado. Faça login novamente.', 'error');
            AuthManager.limparUsuario();
            window.location.href = 'login.html';
            return;
        }

        const resp = await UsuariosService.obterPerfil(usuarioId);
        if (resp.success && resp.usuario) {
            const u = resp.usuario;

            document.getElementById('perfilNome').textContent  = u.nome || '-';
            document.getElementById('perfilEmail').textContent = u.email || '-';

            const pontos =
                u.saldo_pontos ||
                u.pontos ||
                0;

            document.getElementById('perfilPontos').textContent = `${pontos} pts`;
            document.getElementById('spanSeusPontos').textContent = `${pontos} pts`;

            // Atualiza também no localStorage
            AuthManager.salvarUsuario(u);
        } else {
            mostrarMensagem(resp.message || 'Não foi possível carregar o perfil.', 'error');
        }
    } catch (e) {
        console.error('Erro ao carregar perfil:', e);
        mostrarMensagem('Erro ao carregar perfil do usuário.', 'error');
    }
}

function configurarLogout() {
    const btnLogout = document.getElementById('btnLogout');
    if (!btnLogout) return;

    btnLogout.addEventListener('click', () => {
        AuthManager.limparUsuario();
        mostrarMensagem('Você saiu da conta.', 'info');
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 800);
    });
}

// Atualiza só o banner de pontos no topo
function atualizarPontosInterfacePerfil() {
    const usuario = AuthManager.obterUsuario();
    const pontos = (usuario && (usuario.saldo_pontos || usuario.pontos)) || 0;

    document.querySelectorAll('.points-value, #spanSeusPontos').forEach(elem => {
        elem.textContent = `${pontos} pts`;
    });
}
