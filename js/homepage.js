// homepage.js - Script para página inicial

document.addEventListener('DOMContentLoaded', async () => {
    // Verificar autenticação
    if (!verificarAutenticacao()) {
        return;
    }
    
    const usuario = AuthManager.obterUsuario();
    
    try {
        // Atualizar informações do usuário na página
        await atualizarPerfil();
        
        // Carregar histórico de depósitos
        await carregarHistorico();
        
        // Atualizar pontos na interface
        atualizarPontosInterface();
        
    } catch (error) {
        console.error('Erro ao carregar dados:', error);
        mostrarMensagem('Erro ao carregar dados do usuário', 'error');
    }
    
    // Adicionar evento de logout se houver botão
    const btnLogout = document.querySelector('.btn-logout, #btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', (e) => {
            e.preventDefault();
            UsuariosService.logout();
        });
    }
});

async function atualizarPerfil() {
    const usuarioId = AuthManager.obterUsuarioId();
    
    try {
        const response = await UsuariosService.obterPerfil(usuarioId);
        
        if (response.success && response.usuario) {
            const usuario = response.usuario;
            
            // Atualizar dados no localStorage
            AuthManager.salvarUsuario(usuario);
            
            // Atualizar elementos da página
            const elementoEmail = document.querySelector('.usuario-email, #usuarioEmail');
            if (elementoEmail) {
                elementoEmail.textContent = usuario.email;
            }
            
            const elementoNome = document.querySelector('.usuario-nome, #usuarioNome');
            if (elementoNome) {
                elementoNome.textContent = usuario.email.split('@')[0];
            }
            
            // Atualizar pontos
            const elementoPontos = document.querySelector('.points-value, #pontos');
            if (elementoPontos) {
                elementoPontos.textContent = `${usuario.saldo_pontos || 0} pts`;
            }
        }
    } catch (error) {
        console.error('Erro ao atualizar perfil:', error);
    }
}

async function carregarHistorico() {
    const usuarioId = AuthManager.obterUsuarioId();
    
    try {
        const response = await DepositosService.obterHistorico(usuarioId);
        
        if (response.success) {
            exibirHistorico(response.data, response.estatisticas);
        }
    } catch (error) {
        console.error('Erro ao carregar histórico:', error);
    }
}

function exibirHistorico(depositos, estatisticas) {
    // Atualizar estatísticas se houver elementos na página
    if (estatisticas) {
        const elemSaldoPontos = document.querySelector('#saldoPontos');
        if (elemSaldoPontos) {
            elemSaldoPontos.textContent = estatisticas.saldoPontos || 0;
        }
        
        const elemTotalMateriais = document.querySelector('#totalMateriais');
        if (elemTotalMateriais) {
            elemTotalMateriais.textContent = estatisticas.totalMateriais || 0;
        }
        
        const elemPesoTotal = document.querySelector('#pesoTotal');
        if (elemPesoTotal) {
            elemPesoTotal.textContent = estatisticas.pesoTotal || '0kg';
        }
    }
    
    // Exibir últimos depósitos
    const listaHistorico = document.querySelector('#historicoDepositos, .historico-lista');
    if (listaHistorico && depositos && depositos.length > 0) {
        listaHistorico.innerHTML = depositos.slice(0, 5).map(deposito => `
            <div class="deposito-item">
                <div class="deposito-info">
                    <span class="material-icon">♻️</span>
                    <span class="material-nome">${deposito.Registro.Material}</span>
                    <span class="material-quantidade">${deposito.Registro.Quantidade}</span>
                </div>
                <div class="deposito-pontos">
                    <span class="pontos-ganhos">+${deposito.Registro.Pontos} pts</span>
                    <span class="deposito-data">${new Date(deposito.timestamp).toLocaleDateString('pt-BR')}</span>
                </div>
            </div>
        `).join('');
    }
}

function atualizarPontosInterface() {
    const usuario = AuthManager.obterUsuario();
    const pontos = usuario.saldo_pontos || usuario.pontos || 0;
    
    // Atualizar todos os elementos que mostram pontos
    document.querySelectorAll('.points-value, #pontos, .pontos-usuario').forEach(elem => {
        elem.textContent = `${pontos} pts`;
    });
}
