// login.js - Script para página de login

document.addEventListener('DOMContentLoaded', () => {
    // Verificar se já está logado
    if (AuthManager.estaLogado()) {
        window.location.href = 'Homepage.html';
        return;
    }
    
    const formLogin = document.querySelector('form');
    
    if (formLogin) {
        formLogin.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.querySelector('input[type="email"]').value;
            const senha = document.querySelector('input[type="password"]').value;
            
            if (!email || !senha) {
                mostrarMensagem('Por favor, preencha todos os campos', 'error');
                return;
            }
            
            try {
                // Desabilitar botão durante requisição
                const btnSubmit = formLogin.querySelector('button[type="submit"]');
                btnSubmit.disabled = true;
                btnSubmit.textContent = 'Entrando...';
                
                const response = await UsuariosService.login(email, senha);
                
                if (response.success) {
                    mostrarMensagem('Login realizado com sucesso!', 'success');
                    setTimeout(() => {
                        window.location.href = 'Homepage.html';
                    }, 1000);
                } else {
                    mostrarMensagem(response.message || 'Erro ao fazer login', 'error');
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Entrar';
                }
            } catch (error) {
                console.error('Erro no login:', error);
                mostrarMensagem(error.message || 'Erro ao fazer login. Tente novamente.', 'error');
                
                const btnSubmit = formLogin.querySelector('button[type="submit"]');
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Entrar';
            }
        });
    }
});
