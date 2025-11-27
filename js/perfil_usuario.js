// perfil_usuario.js - Tela de perfil do usuário

document.addEventListener('DOMContentLoaded', async () => {
    // Garante que está logado
    if (typeof verificarAutenticacao === 'function') {
        if (!verificarAutenticacao()) return;
    }

    const usuario = AuthManager.obterUsuario();
    if (!usuario) {
        console.warn('Nenhum usuário logado encontrado no localStorage');
        return;
    }

    // 🔹 Preenche informações básicas
    const nomeElem         = document.getElementById('perfil-nome');
    const emailElem        = document.getElementById('perfil-email');
    const localizacaoElem  = document.getElementById('perfil-localizacao');
    const pontosSaldoSpan  = document.getElementById('pontos-saldo');
    const pontosCircleElem = document.querySelector('.points-number');
    const bannerPontosSpan = document.querySelector('.points-value, #spanSeusPontos');

    const saldoPontos = usuario.saldo_pontos ?? usuario.pontos ?? 0;

    if (nomeElem)        nomeElem.textContent        = usuario.nome  || '-';
    if (emailElem)       emailElem.textContent       = usuario.email || '-';
    if (localizacaoElem) localizacaoElem.textContent = usuario.cidade || usuario.localizacao || '-';

    if (pontosSaldoSpan)  pontosSaldoSpan.textContent  = saldoPontos;
    if (pontosCircleElem) pontosCircleElem.textContent = saldoPontos;
    if (bannerPontosSpan) bannerPontosSpan.textContent = `${saldoPontos} pts`;

    // 🔹 Carrega histórico de depósitos pela API
    try {
        const resp = await DepositosService.obterHistorico(usuario.id);

        if (resp.success && Array.isArray(resp.data)) {
            const lista = document.getElementById('historico-lista');
            if (lista) {
                if (resp.data.length === 0) {
                    lista.innerHTML = `<p class="history-empty">Nenhum depósito encontrado.</p>`;
                } else {
                    const ultimos = resp.data.slice(0, 5);
                    lista.innerHTML = ultimos.map(item => `
                        <div class="history-item">
                            <span class="history-icon">🏦</span>
                            <div class="history-details">
                                <span class="history-desc">
                                    ${item.Registro?.Material || item.detalhes?.categoria || 'Depósito'}
                                </span>
                                <span class="history-date">
                                    ${new Date(item.timestamp || item.criado_em).toLocaleString('pt-BR')}
                                </span>
                            </div>
                            <span class="history-points positive">
                                +${item.detalhes?.pontosGanhos ?? item.pontosGanhos ?? 0} pts
                            </span>
                        </div>
                    `).join('');
                }
            }

            // Atualiza pontos
            if (resp.estatisticas && resp.estatisticas.saldoPontos != null) {
                const novoSaldo = resp.estatisticas.saldoPontos;
                if (pontosSaldoSpan)  pontosSaldoSpan.textContent  = novoSaldo;
                if (pontosCircleElem) pontosCircleElem.textContent = novoSaldo;
                if (bannerPontosSpan) bannerPontosSpan.textContent = `${novoSaldo} pts`;

                const u = AuthManager.obterUsuario();
                if (u) {
                    u.saldo_pontos = novoSaldo;
                    AuthManager.salvarUsuario(u);
                }
            }
        }
    } catch (err) {
        console.error('Erro ao carregar histórico de depósitos:', err);
    }

    // 🔹 CONFIGURAR LOGOUT AGORA!
    configurarLogout();
});

function configurarLogout() {
    const btnLogout = document.getElementById('btnLogout');
    if (!btnLogout) return;

    btnLogout.addEventListener('click', () => {

        if (typeof mostrarMensagem === 'function') {
            mostrarMensagem('Você saiu da conta.', 'info');
        }

        AuthManager.limparUsuario();

        setTimeout(() => {
            window.location.href = 'login.html';
        }, 800);
    });
}
