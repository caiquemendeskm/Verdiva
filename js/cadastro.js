// cadastro.js - Script para página de cadastro

document.addEventListener('DOMContentLoaded', () => {
    // Verificar se já está logado
    if (AuthManager.estaLogado()) {
        window.location.href = 'Homepage.html';
        return;
    }
    
    const formCadastro = document.querySelector('form');
    
    if (formCadastro) {
        formCadastro.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Obter valores dos campos
            const cpf = document.getElementById('cpf').value;
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha')?.value;
            const telefone = document.getElementById('telefone').value;
            
            // Validações
            if (!cpf || !email || !senha || !telefone) {
                mostrarMensagem('Por favor, preencha todos os campos', 'error');
                return;
            }
            
            if (confirmarSenha && senha !== confirmarSenha) {
                mostrarMensagem('As senhas não coincidem', 'error');
                return;
            }
            
            try {
                // Desabilitar botão durante requisição
                const btnSubmit = formCadastro.querySelector('button[type="submit"]');
                btnSubmit.disabled = true;
                btnSubmit.textContent = 'Cadastrando...';
                
                const response = await UsuariosService.cadastrar({
                    cpf: cpf.replace(/\D/g, ''),
                    email,
                    senha,
                    telefone: telefone.replace(/\D/g, '')
                });
                
                if (response.success) {
                    mostrarMensagem('Cadastro realizado com sucesso! Redirecionando para login...', 'success');
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 2000);
                } else {
                    mostrarMensagem(response.message || 'Erro ao fazer cadastro', 'error');
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Cadastrar';
                }
            } catch (error) {
                console.error('Erro no cadastro:', error);
                mostrarMensagem(error.message || 'Erro ao fazer cadastro. Tente novamente.', 'error');
                
                const btnSubmit = formCadastro.querySelector('button[type="submit"]');
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Cadastrar';
            }
        });
    }
});
