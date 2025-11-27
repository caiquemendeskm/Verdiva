// deposito.js - Script para página de depósito de materiais

let materiaisDisponiveis = [];
let itensCarrinho = [];

document.addEventListener('DOMContentLoaded', async () => {
    // Verificar autenticação
    if (!verificarAutenticacao()) {
        return;
    }
    
    // Atualizar pontos na interface (Seus Pontos)
    atualizarPontosInterface();
    
    // Carregar materiais disponíveis
    await carregarMateriais();
    
    // Configurar eventos (botão Finalizar Depósito)
    configurarEventos();
});

async function carregarMateriais() {
    try {
        const response = await MateriaisService.listar();
        
        if (response.success && response.data) {
            materiaisDisponiveis = response.data;
            exibirMateriais(materiaisDisponiveis);
        }
    } catch (error) {
        console.error('Erro ao carregar materiais:', error);
        mostrarMensagem('Erro ao carregar materiais disponíveis', 'error');
    }
}

function exibirMateriais(materiais) {
    // Agora usamos diretamente o id "listaMateriais"
    const listaMateriais = document.getElementById('listaMateriais');
    
    if (!listaMateriais) return;
    
    const iconesPorTipo = {
        papel: '📄',
        vidro: '🍾',
        plastico: '♻️',
        metal: '🥫',
        eletronico: '📱'
    };
    
    listaMateriais.innerHTML = materiais.map(material => `
        <div class="material-card" data-tipo="${material.Material.Tipo}">
            <div class="material-icon">${iconesPorTipo[material.Material.Tipo] || '♻️'}</div>
            <h3>${material.Material.Categoria}</h3>
            <p class="material-pontos">${material.pontosConfig.porKg} pts/kg</p>
            <p class="material-instrucoes">${material.instrucoes}</p>
            <button class="btn-adicionar" onclick="adicionarMaterial('${material.Material.Tipo}', '${material.Material.Categoria}')">
                Adicionar
            </button>
        </div>
    `).join('');
}

function adicionarMaterial(tipo, categoria) {
    const material = materiaisDisponiveis.find(m => m.Material.Tipo === tipo);
    
    if (!material) {
        mostrarMensagem('Material não encontrado', 'error');
        return;
    }
    
    // Solicitar peso/quantidade
    const peso = prompt(`Digite o peso em gramas do ${categoria}:`, '100');
    
    if (!peso || isNaN(peso) || peso <= 0) {
        mostrarMensagem('Peso inválido', 'error');
        return;
    }
    
    const pesoGramas = parseInt(peso, 10);
    const quantidade = Math.ceil(pesoGramas / 100); // Estimativa de unidades
    
    // Calcular pontos
    const pontosPorPeso = (pesoGramas / 1000) * material.pontosConfig.porKg;
    const pontosPorUnidade = material.pontosConfig.porUnidade ? (quantidade * material.pontosConfig.porUnidade) : 0;
    const pontosGanhos = Math.floor(Math.max(pontosPorPeso, pontosPorUnidade));

    // Guardar peso/pontos por unidade para poder usar no +/- depois
    const pesoUnitario = quantidade > 0 ? pesoGramas / quantidade : 0;
    const pontosPorUnidadeReal = quantidade > 0 ? pontosGanhos / quantidade : 0;
    
    // Adicionar ao carrinho
    const item = {
        tipo,
        categoria,
        pesoGramas,
        quantidade,
        pontosGanhos,
        pesoUnitario,
        pontosPorUnidade: pontosPorUnidadeReal
    };
    
    itensCarrinho.push(item);
    atualizarCarrinho();
    mostrarMensagem(`${categoria} adicionado com sucesso!`, 'success');
}

function atualizarCarrinho() {
    // Lista dos itens adicionados (lado esquerdo)
    const listaItens = document.querySelector('#itensAdicionados') || document.querySelector('.items-list-box ul');
    // Total de pontos do depósito atual (lado direito)
    const totalPontosElem = document.getElementById('totalPontos') || document.querySelector('.points-number');
    
    if (listaItens) {
        const iconesPorTipo = {
            papel: '📄',
            vidro: '🍾',
            plastico: '♻️',
            metal: '🥫',
            eletronico: '📱'
        };
        
        listaItens.innerHTML = itensCarrinho.map((item, index) => `
            <li>
                <span class="item-icon">${iconesPorTipo[item.tipo] || '♻️'}</span>
                <span class="item-name">${item.categoria}</span>
                <span class="item-weight">${item.pesoGramas}g</span>
                <span class="item-points">${item.pontosGanhos} pts</span>

                <!-- Controles de quantidade (+ / -) -->
                <span class="item-qty-controls">
                    <button class="btn-qty" onclick="alterarQuantidade(${index}, -1)">-</button>
                    <span class="item-quantidade">${item.quantidade}</span>
                    <button class="btn-qty" onclick="alterarQuantidade(${index}, 1)">+</button>
                </span>

                <span class="item-check">✅</span>
                <button class="btn-remover" onclick="removerItem(${index})">❌</button>
            </li>
        `).join('');
    }
    
    if (totalPontosElem) {
        const total = itensCarrinho.reduce((sum, item) => sum + item.pontosGanhos, 0);
        totalPontosElem.textContent = total;
    }
}

function alterarQuantidade(index, delta) {
    const item = itensCarrinho[index];
    if (!item) return;

    const novaQtd = item.quantidade + delta;

    // Se zerar ou ficar negativa, remove o item
    if (novaQtd <= 0) {
        removerItem(index);
        return;
    }

    item.quantidade = novaQtd;

    // Recalcula peso e pontos com base nos valores por unidade
    if (item.pesoUnitario) {
        item.pesoGramas = Math.round(item.pesoUnitario * novaQtd);
    }
    if (item.pontosPorUnidade) {
        item.pontosGanhos = Math.round(item.pontosPorUnidade * novaQtd);
    }

    atualizarCarrinho();
}

function removerItem(index) {
    itensCarrinho.splice(index, 1);
    atualizarCarrinho();
    mostrarMensagem('Item removido', 'info');
}

function configurarEventos() {
    const btnFinalizar = document.getElementById('btnFinalizarDeposito');
    
    if (btnFinalizar) {
        btnFinalizar.onclick = async (e) => {
            e.preventDefault();
            await finalizarDeposito();
        };
    }

    // fallback: qualquer botão com texto "Finalizar"
    document.querySelectorAll('button').forEach(btn => {
        if (btn.textContent.includes('Finalizar')) {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                await finalizarDeposito();
            });
        }
    });
}

async function finalizarDeposito() {
    if (itensCarrinho.length === 0) {
        mostrarMensagem('Adicione pelo menos um item antes de finalizar', 'error');
        return;
    }
    
    const usuarioId = AuthManager.obterUsuarioId();

    // 🔹 Proteção extra: se não tiver usuário, manda pro login
    if (!usuarioId) {
        mostrarMensagem('Erro: usuário não identificado. Faça login novamente.', 'error');
        AuthManager.limparUsuario();
        window.location.href = 'login.html';
        return;
    }
    
    try {
        // Registrar cada item
        for (const item of itensCarrinho) {
            await DepositosService.registrar(
                usuarioId,
                item.tipo,
                item.pesoGramas,
                item.quantidade
            );
        }
        
        // Atualizar pontos do usuário
        const response = await UsuariosService.obterPerfil(usuarioId);
        if (response.success && response.usuario) {
            AuthManager.salvarUsuario(response.usuario);
        }
        
        const totalPontos = itensCarrinho.reduce((sum, item) => sum + item.pontosGanhos, 0);
        
        mostrarMensagem(`Depósito finalizado! Você ganhou ${totalPontos} pontos!`, 'success');
        
        // Limpar carrinho
        itensCarrinho = [];
        atualizarCarrinho();
        
        // Redirecionar após 2 segundos
        setTimeout(() => {
            window.location.href = 'Homepage.html';
        }, 2000);
        
    } catch (error) {
        console.error('Erro ao finalizar depósito:', error);
        mostrarMensagem(error.message || 'Erro ao finalizar depósito', 'error');
    }
}

function atualizarPontosInterface() {
    const usuario = AuthManager.obterUsuario();
    const pontos = (usuario && (usuario.saldo_pontos || usuario.pontos)) || 0;
    
    document.querySelectorAll('.points-value, #pontos, #spanSeusPontos').forEach(elem => {
        elem.textContent = `${pontos} pts`;
    });
}

// Tornar funções globais para uso inline no HTML
window.adicionarMaterial = adicionarMaterial;
window.removerItem = removerItem;
window.finalizarDeposito = finalizarDeposito;
window.alterarQuantidade = alterarQuantidade;
