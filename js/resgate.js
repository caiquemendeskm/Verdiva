// resgate.js - Script para página de resgate de recompensas

let recompensasDisponiveis = [];

document.addEventListener('DOMContentLoaded', async () => {
    // Verificar autenticação
    if (!verificarAutenticacao()) {
        return;
    }
    
    // Atualizar pontos na interface
    atualizarPontosInterface();
    
    // Carregar recompensas disponíveis
    await carregarRecompensas();
    
    // Carregar histórico de resgates
    await carregarHistoricoResgates();
});

async function carregarRecompensas() {
    const usuarioId = AuthManager.obterUsuarioId();
    
    try {
        const response = await RecompensasService.listar(usuarioId);
        
        if (response.success && response.data) {
            recompensasDisponiveis = response.data;
            exibirRecompensas(recompensasDisponiveis, response.pontosUsuario);
        }
    } catch (error) {
        console.error('Erro ao carregar recompensas:', error);
        mostrarMensagem('Erro ao carregar recompensas disponíveis', 'error');
    }
}

function exibirRecompensas(recompensas, pontosUsuario) {
    const listaRecompensas = document.querySelector('#listaRecompensas, .recompensas-lista');
    
    if (!listaRecompensas) return;
    
    const iconesPorCategoria = {
        transporte: '🚌',
        jardinagem: '🌱',
        alimentacao: '🛒',
        entretenimento: '🎬',
        moda: '👕',
        educacao: '📚'
    };
    
    listaRecompensas.innerHTML = recompensas.map(recompensa => {
        const podeResgatar = recompensa.podeResgatar;
        const classeDisponivel = podeResgatar ? 'disponivel' : 'indisponivel';
        
        return `
            <div class="recompensa-card ${classeDisponivel}" data-id="${recompensa.id}">
                <div class="recompensa-header">
                    <span class="recompensa-icon">${iconesPorCategoria[recompensa.categoria] || '🎁'}</span>
                    <span class="recompensa-categoria">${recompensa.categoria}</span>
                </div>
                <h3>${recompensa.nome}</h3>
                <p class="recompensa-descricao">${recompensa.descricao}</p>
                <div class="recompensa-info">
                    <span class="recompensa-pontos">${recompensa.pontosNecessarios} pontos</span>
                    <span class="recompensa-valor">${recompensa.valor}</span>
                </div>
                <p class="recompensa-parceiro">Parceiro: ${recompensa.parceiro}</p>
                <p class="recompensa-validade">Válido por ${recompensa.validadeDias} dias</p>
                <button 
                    class="btn-resgatar ${podeResgatar ? '' : 'disabled'}" 
                    onclick="resgatarRecompensa(${recompensa.id})"
                    ${!podeResgatar ? 'disabled' : ''}
                >
                    ${podeResgatar ? 'Resgatar' : 'Pontos Insuficientes'}
                </button>
            </div>
        `;
    }).join('');
}

async function resgatarRecompensa(recompensaId) {
    const recompensa = recompensasDisponiveis.find(r => r.id === recompensaId);
    
    if (!recompensa) {
        mostrarMensagem('Recompensa não encontrada', 'error');
        return;
    }
    
    const confirmar = confirm(`Deseja resgatar "${recompensa.nome}" por ${recompensa.pontosNecessarios} pontos?`);
    
    if (!confirmar) return;
    
    const usuarioId = AuthManager.obterUsuarioId();
    
    try {
        const response = await RecompensasService.resgatar(usuarioId, recompensaId);
        
        if (response.success) {
            const codigo = response.data.transacao.codigoDesconto;
            const validoAte = response.data.transacao.validoAte;
            
            mostrarMensagem(`Resgate realizado com sucesso! Código: ${codigo}`, 'success');
            
            // Exibir modal com detalhes do resgate
            exibirModalResgate(response.data);
            
            // Atualizar pontos do usuário
            const novosPontos = parseInt(response.data.Recompensa['Total-Pontos']);
            AuthManager.atualizarPontos(novosPontos);
            atualizarPontosInterface();
            
            // Recarregar recompensas
            await carregarRecompensas();
            await carregarHistoricoResgates();
        }
    } catch (error) {
        console.error('Erro ao resgatar:', error);
        mostrarMensagem(error.message || 'Erro ao resgatar recompensa', 'error');
    }
}

function exibirModalResgate(dados) {
    const modal = document.createElement('div');
    modal.className = 'modal-resgate';
    modal.innerHTML = `
        <div class="modal-content">
            <h2>✅ Resgate Realizado!</h2>
            <div class="resgate-detalhes">
                <p><strong>Recompensa:</strong> ${dados.transacao.recompensa}</p>
                <p><strong>Parceiro:</strong> ${dados.transacao.parceiro}</p>
                <p><strong>Valor:</strong> ${dados.transacao.valor}</p>
                <p><strong>Código:</strong> <span class="codigo-destaque">${dados.transacao.codigoDesconto}</span></p>
                <p><strong>Válido até:</strong> ${new Date(dados.transacao.validoAte).toLocaleDateString('pt-BR')}</p>
                <p class="instrucoes"><strong>Como usar:</strong> ${dados.transacao.instrucoes}</p>
                <p class="termos">${dados.detalhesUso.restricoes}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()">Fechar</button>
        </div>
    `;
    
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    `;
    
    modal.querySelector('.modal-content').style.cssText = `
        background: white;
        padding: 30px;
        border-radius: 10px;
        max-width: 500px;
        width: 90%;
        text-align: center;
    `;
    
    modal.querySelector('.codigo-destaque').style.cssText = `
        font-size: 24px;
        font-weight: bold;
        color: #4CAF50;
        display: block;
        margin: 10px 0;
        padding: 10px;
        background: #f0f0f0;
        border-radius: 5px;
    `;
    
    document.body.appendChild(modal);
}

async function carregarHistoricoResgates() {
    const usuarioId = AuthManager.obterUsuarioId();
    
    try {
        const response = await RecompensasService.obterResgates(usuarioId);
        
        if (response.success && response.data) {
            exibirHistoricoResgates(response.data);
        }
    } catch (error) {
        console.error('Erro ao carregar histórico de resgates:', error);
    }
}

function exibirHistoricoResgates(resgates) {
    const listaHistorico = document.querySelector('#historicoResgates, .historico-resgates');
    
    if (!listaHistorico || resgates.length === 0) return;
    
    listaHistorico.innerHTML = `
        <h3>Meus Resgates</h3>
        ${resgates.map(resgate => `
            <div class="resgate-item status-${resgate.status}">
                <div class="resgate-info">
                    <strong>${resgate.recompensaNome}</strong>
                    <span class="resgate-codigo">Código: ${resgate.codigoDesconto}</span>
                </div>
                <div class="resgate-detalhes">
                    <span class="resgate-pontos">${resgate.pontosUtilizados} pts</span>
                    <span class="resgate-valor">${resgate.valorReais}</span>
                    <span class="resgate-status">${resgate.status === 'active' ? 'Ativo' : 'Usado'}</span>
                    <span class="resgate-validade">Válido até: ${new Date(resgate.validoAte).toLocaleDateString('pt-BR')}</span>
                </div>
            </div>
        `).join('')}
    `;
}

function atualizarPontosInterface() {
    const usuario = AuthManager.obterUsuario();
    const pontos = usuario.saldo_pontos || usuario.pontos || 0;
    
    document.querySelectorAll('.points-value, #pontos, .pontos-usuario').forEach(elem => {
        elem.textContent = `${pontos} pts`;
    });
}

// Tornar função global
window.resgatarRecompensa = resgatarRecompensa;
