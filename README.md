# API Verdiva - PHP

Sistema de reciclagem inteligente que permite que usuários depositem materiais recicláveis em máquinas inteligentes e recebam pontos em troca, que podem ser acumulados e trocados por recompensas em lojas parceiras.

## 📋 Descrição

A API Verdiva é uma solução completa desenvolvida em PHP puro que oferece:

- **Gestão de Usuários**: Cadastro e gerenciamento de dados pessoais
- **Catálogo de Materiais**: Tipos de materiais aceitos e sua pontuação
- **Sistema de Depósitos**: Registro de materiais depositados e conversão em pontos
- **Programa de Recompensas**: Troca de pontos por benefícios em lojas parceiras

## 🚀 Tecnologias Utilizadas

- **PHP 8.1+**: Linguagem principal
- **SQLite**: Banco de dados
- **PDO**: Abstração de banco de dados
- **JSON**: Formato de dados para requisições e respostas
- **REST**: Arquitetura da API

## 📁 Estrutura do Projeto

```
verdiva_php_api/
├── public/
│   └── index.php              # Ponto de entrada da API
├── src/
│   ├── controllers/           # Controladores da aplicação
│   │   ├── UserController.php
│   │   ├── MaterialController.php
│   │   ├── DepositoController.php
│   │   └── RecompensaController.php
│   ├── models/                # Modelos de dados
│   │   ├── User.php
│   │   ├── Material.php
│   │   ├── Deposito.php
│   │   └── Recompensa.php
│   └── routes/                # Definição de rotas
│       ├── router.php
│       ├── user_routes.php
│       ├── material_routes.php
│       ├── deposito_routes.php
│       └── recompensa_routes.php
├── config/
│   └── database.php           # Configuração do banco de dados
├── database/
│   └── verdiva.db            # Banco de dados SQLite
└── test_api.php              # Script de testes
```

## 🔧 Instalação e Configuração

### Pré-requisitos

- PHP 8.1 ou superior
- Extensões PHP: PDO, SQLite, cURL, JSON

### Instalação

1. **Clone ou baixe o projeto**
```bash
# Se usando Git
git clone <repository-url>
cd verdiva_php_api

# Ou extraia o arquivo ZIP
unzip verdiva_php_api.zip
cd verdiva_php_api
```

2. **Instale as dependências do PHP** (Ubuntu/Debian)
```bash
sudo apt update
sudo apt install -y php php-cli php-pdo php-sqlite3 php-curl php-json
```

3. **Configure permissões**
```bash
chmod -R 755 .
chmod -R 777 database/
```

4. **Inicie o servidor de desenvolvimento**
```bash
cd public
php -S 0.0.0.0:8000
```

A API estará disponível em: `http://localhost:8000`

## 📚 Documentação da API

### Base URL
```
http://localhost:8000/api/v1
```

### Headers Obrigatórios
```
Content-Type: application/json
```

### Códigos de Status HTTP
- `200 OK`: Sucesso
- `201 Created`: Recurso criado com sucesso
- `400 Bad Request`: Dados inválidos
- `404 Not Found`: Recurso não encontrado
- `500 Internal Server Error`: Erro interno do servidor

## 🔗 Endpoints

### 1. Serviço de Usuários

#### Listar Usuários
```http
GET /api/v1/servico-de-usuarios
```

**Resposta:**
```json
[
  {
    "Usuario": {
      "id": "1",
      "Nome": "João da Silva",
      "Email": "joao.silva@example.com",
      "CPF": "25417896550",
      "Total-Pontos": "100"
    }
  }
]
```

#### Criar Usuário
```http
POST /api/v1/servico-de-usuarios
```

**Requisição:**
```json
{
  "Usuario": {
    "Nome": "João da Silva",
    "Email": "joao.silva@example.com",
    "CPF": "25417896550"
  }
}
```

**Resposta:**
```json
{
  "Usuario": {
    "id": "1",
    "Nome": "João da Silva",
    "Email": "joao.silva@example.com",
    "CPF": "25417896550",
    "Total-Pontos": "0"
  }
}
```

#### Buscar Usuário por ID
```http
GET /api/v1/servico-de-usuarios/{id}
```

#### Atualizar Usuário
```http
PUT /api/v1/servico-de-usuarios/{id}
```

#### Deletar Usuário
```http
DELETE /api/v1/servico-de-usuarios/{id}
```

### 2. Serviço de Materiais

#### Listar Materiais
```http
GET /api/v1/servico-de-materiais
```

**Resposta:**
```json
[
  {
    "Material": {
      "id": "1",
      "Tipo": "vidro",
      "Pontos-por-kg": 10,
      "Pontos-por-unidade": 2
    }
  },
  {
    "Material": {
      "id": "2",
      "Tipo": "papel",
      "Pontos-por-kg": 5,
      "Pontos-por-unidade": 1
    }
  }
]
```

#### Buscar Material por ID
```http
GET /api/v1/servico-de-materiais/{id}
```

### 3. Serviço de Depósito de Materiais

#### Listar Depósitos
```http
GET /api/v1/servico-de-deposito-de-materiais
```

#### Criar Depósito (por quantidade)
```http
POST /api/v1/servico-de-deposito-de-materiais
```

**Requisição:**
```json
{
  "Registro": {
    "Material": "papel",
    "Quantidade": "5",
    "user_id": 1
  }
}
```

**Resposta:**
```json
{
  "Registro": {
    "id": "1",
    "user_id": 1,
    "Material": "papel",
    "Peso": "",
    "Quantidade": "5",
    "Pontos": "5",
    "Total-Pontos": "105",
    "Data": "2025-09-22 10:30:00"
  }
}
```

#### Criar Depósito (por peso)
```http
POST /api/v1/servico-de-deposito-de-materiais
```

**Requisição:**
```json
{
  "Registro": {
    "Material": "vidro",
    "Peso": "2kg",
    "user_id": 1
  }
}
```

**Resposta:**
```json
{
  "Registro": {
    "id": "2",
    "user_id": 1,
    "Material": "vidro",
    "Peso": "2kg",
    "Quantidade": "",
    "Pontos": "20",
    "Total-Pontos": "125",
    "Data": "2025-09-22 10:35:00"
  }
}
```

#### Buscar Depósitos por Usuário
```http
GET /api/v1/servico-de-deposito-de-materiais/usuario/{user_id}
```

### 4. Serviço de Recompensas

#### Listar Recompensas
```http
GET /api/v1/servico-de-recompensa
```

**Resposta:**
```json
[
  {
    "Recompensa": {
      "id": 1,
      "Nome": "Desconto 10% Supermercado",
      "Descricao": "10% de desconto em compras",
      "Pontos-Necessarios": 100,
      "Loja-Parceira": "Supermercado ABC",
      "Ativo": true
    }
  }
]
```

#### Consultar Pontos do Usuário
```http
GET /api/v1/servico-de-recompensa/usuario/{user_id}
```

**Resposta:**
```json
{
  "Recompensa": {
    "Total-Pontos": "125",
    "Resgatar": "0"
  }
}
```

#### Resgatar Recompensa
```http
POST /api/v1/servico-de-recompensa
```

**Requisição:**
```json
{
  "Recompensa": {
    "user_id": 1,
    "recompensa_id": 1
  }
}
```

**Resposta:**
```json
{
  "Recompensa": {
    "Total-Pontos": "25",
    "Resgatar-pontos": "100",
    "Recompensa-Resgatada": "Desconto 10% Supermercado"
  }
}
```

## 🧪 Testes

Execute o script de testes para verificar se todos os endpoints estão funcionando:

```bash
php test_api.php
```

O script testará:
- ✅ Criação de usuário
- ✅ Listagem de materiais
- ✅ Criação de depósitos (por quantidade e peso)
- ✅ Listagem de recompensas
- ✅ Consulta de pontos do usuário

## 🗄️ Banco de Dados

A API utiliza SQLite com as seguintes tabelas:

### Tabela `users`
- `id`: Chave primária
- `nome`: Nome do usuário
- `email`: Email único
- `cpf`: CPF único
- `total_pontos`: Total de pontos acumulados

### Tabela `materials`
- `id`: Chave primária
- `tipo`: Tipo do material (vidro, papel, plástico, metal)
- `pontos_por_kg`: Pontos por quilograma
- `pontos_por_unidade`: Pontos por unidade

### Tabela `depositos`
- `id`: Chave primária
- `user_id`: Referência ao usuário
- `material_id`: Referência ao material
- `peso`: Peso depositado (opcional)
- `quantidade`: Quantidade depositada (opcional)
- `pontos_ganhos`: Pontos ganhos no depósito
- `data_deposito`: Data e hora do depósito

### Tabela `recompensas`
- `id`: Chave primária
- `nome`: Nome da recompensa
- `descricao`: Descrição da recompensa
- `pontos_necessarios`: Pontos necessários para resgate
- `loja_parceira`: Nome da loja parceira
- `ativo`: Status da recompensa

### Tabela `resgates`
- `id`: Chave primária
- `user_id`: Referência ao usuário
- `recompensa_id`: Referência à recompensa
- `pontos_utilizados`: Pontos utilizados no resgate
- `data_resgate`: Data e hora do resgate

## 🔒 Segurança

- Sanitização de dados de entrada
- Prepared statements para prevenir SQL injection
- Headers CORS configurados
- Validação de tipos de dados

## 🚀 Deploy em Produção

Para deploy em produção, considere:

1. **Servidor Web**: Apache ou Nginx
2. **Banco de Dados**: MySQL ou PostgreSQL para melhor performance
3. **HTTPS**: Certificado SSL obrigatório
4. **Logs**: Configurar logs de erro e acesso
5. **Backup**: Sistema de backup automático do banco

## 📞 Suporte

Para dúvidas ou problemas:
- Verifique os logs de erro do PHP
- Execute os testes automatizados
- Consulte a documentação dos endpoints

## 📄 Licença

Este projeto foi desenvolvido para fins educacionais e de demonstração.

---

**API Verdiva v1.0** - Sistema de Reciclagem Inteligente

"# Verdiva" 
"# Verdiva" 
