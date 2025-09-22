# Guia de Deployment - API Verdiva PHP

Este documento fornece instruções detalhadas para fazer o deploy da API Verdiva em diferentes ambientes.

## 🌐 Deploy em Servidor Web (Apache/Nginx)

### Apache

1. **Instalar Apache e PHP**
```bash
sudo apt update
sudo apt install -y apache2 php libapache2-mod-php php-pdo php-sqlite3 php-curl php-json
```

2. **Configurar Virtual Host**
```bash
sudo nano /etc/apache2/sites-available/verdiva-api.conf
```

```apache
<VirtualHost *:80>
    ServerName api.verdiva.com
    DocumentRoot /var/www/verdiva_php_api/public
    
    <Directory /var/www/verdiva_php_api/public>
        AllowOverride All
        Require all granted
        
        # Rewrite rules para API REST
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/verdiva_error.log
    CustomLog ${APACHE_LOG_DIR}/verdiva_access.log combined
</VirtualHost>
```

3. **Ativar módulos e site**
```bash
sudo a2enmod rewrite
sudo a2ensite verdiva-api.conf
sudo systemctl reload apache2
```

4. **Copiar arquivos**
```bash
sudo cp -r verdiva_php_api /var/www/
sudo chown -R www-data:www-data /var/www/verdiva_php_api
sudo chmod -R 755 /var/www/verdiva_php_api
sudo chmod -R 777 /var/www/verdiva_php_api/database
```

### Nginx

1. **Instalar Nginx e PHP-FPM**
```bash
sudo apt update
sudo apt install -y nginx php-fpm php-pdo php-sqlite3 php-curl php-json
```

2. **Configurar servidor**
```bash
sudo nano /etc/nginx/sites-available/verdiva-api
```

```nginx
server {
    listen 80;
    server_name api.verdiva.com;
    root /var/www/verdiva_php_api/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

3. **Ativar site**
```bash
sudo ln -s /etc/nginx/sites-available/verdiva-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 🐳 Deploy com Docker

### Dockerfile

```dockerfile
FROM php:8.1-apache

# Instalar extensões PHP
RUN docker-php-ext-install pdo pdo_sqlite

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar arquivos da aplicação
COPY . /var/www/html/

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
RUN chmod -R 777 /var/www/html/database

# Configurar Apache
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
```

### docker-compose.yml

```yaml
version: '3.8'

services:
  verdiva-api:
    build: .
    ports:
      - "8080:80"
    volumes:
      - ./database:/var/www/html/database
    environment:
      - APACHE_DOCUMENT_ROOT=/var/www/html/public
    restart: unless-stopped
```

### Comandos Docker

```bash
# Build e execução
docker-compose up -d

# Verificar logs
docker-compose logs -f

# Parar serviços
docker-compose down
```

## ☁️ Deploy em Cloud

### AWS EC2

1. **Criar instância EC2**
   - Ubuntu 22.04 LTS
   - Tipo: t2.micro (free tier)
   - Security Group: HTTP (80), HTTPS (443), SSH (22)

2. **Conectar e configurar**
```bash
ssh -i sua-chave.pem ubuntu@ip-da-instancia

# Instalar dependências
sudo apt update
sudo apt install -y apache2 php libapache2-mod-php php-pdo php-sqlite3 php-curl php-json

# Upload dos arquivos
scp -i sua-chave.pem -r verdiva_php_api ubuntu@ip-da-instancia:~/
```

3. **Configurar Apache** (seguir passos do Apache acima)

### Google Cloud Platform

1. **Criar VM no Compute Engine**
2. **Configurar firewall** para HTTP/HTTPS
3. **Seguir passos de instalação** similares ao AWS

### Heroku

1. **Criar arquivo composer.json**
```json
{
    "require": {
        "php": "^8.1"
    }
}
```

2. **Criar Procfile**
```
web: php -S 0.0.0.0:$PORT -t public/
```

3. **Deploy**
```bash
git init
git add .
git commit -m "Initial commit"
heroku create verdiva-api
git push heroku main
```

## 🔒 Configuração de HTTPS

### Certificado SSL com Let's Encrypt

```bash
# Instalar Certbot
sudo apt install -y certbot python3-certbot-apache

# Obter certificado
sudo certbot --apache -d api.verdiva.com

# Renovação automática
sudo crontab -e
# Adicionar: 0 12 * * * /usr/bin/certbot renew --quiet
```

## 🗄️ Banco de Dados em Produção

### Migração para MySQL

1. **Instalar MySQL**
```bash
sudo apt install -y mysql-server php-mysql
```

2. **Criar banco e usuário**
```sql
CREATE DATABASE verdiva_db;
CREATE USER 'verdiva_user'@'localhost' IDENTIFIED BY 'senha_segura';
GRANT ALL PRIVILEGES ON verdiva_db.* TO 'verdiva_user'@'localhost';
FLUSH PRIVILEGES;
```

3. **Atualizar config/database.php**
```php
class Database {
    private $host = "localhost";
    private $db_name = "verdiva_db";
    private $username = "verdiva_user";
    private $password = "senha_segura";
    
    public function getConnection() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Erro de conexão: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
```

## 📊 Monitoramento e Logs

### Configurar Logs

1. **PHP Error Logs**
```bash
sudo nano /etc/php/8.1/apache2/php.ini
```

```ini
log_errors = On
error_log = /var/log/php_errors.log
```

2. **Logs personalizados**
```php
// Adicionar em cada controller
error_log("API Verdiva - " . date('Y-m-d H:i:s') . " - " . $message);
```

### Monitoramento básico

```bash
# Script de monitoramento
#!/bin/bash
# monitor.sh

while true; do
    if ! curl -f http://localhost/api/v1/ > /dev/null 2>&1; then
        echo "$(date): API não está respondendo" >> /var/log/verdiva_monitor.log
        # Reiniciar Apache se necessário
        sudo systemctl restart apache2
    fi
    sleep 60
done
```

## 🔧 Otimizações de Performance

### PHP OPcache

```bash
sudo nano /etc/php/8.1/apache2/php.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

### Compressão Gzip

```apache
# .htaccess
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/json
</IfModule>
```

## 🛡️ Segurança em Produção

### Configurações PHP

```ini
# php.ini
expose_php = Off
display_errors = Off
log_errors = On
allow_url_fopen = Off
allow_url_include = Off
```

### Headers de Segurança

```php
// Adicionar em public/index.php
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
```

### Firewall

```bash
# UFW (Ubuntu)
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw deny 8000  # Bloquear porta de desenvolvimento
```

## 📋 Checklist de Deploy

- [ ] Servidor configurado (Apache/Nginx)
- [ ] PHP e extensões instaladas
- [ ] Banco de dados configurado
- [ ] Arquivos copiados com permissões corretas
- [ ] Virtual host/server block configurado
- [ ] SSL/HTTPS configurado
- [ ] Logs configurados
- [ ] Monitoramento ativo
- [ ] Backup configurado
- [ ] Firewall configurado
- [ ] Testes de API executados
- [ ] DNS apontando para o servidor

## 🆘 Troubleshooting

### Problemas Comuns

1. **Erro 500 - Internal Server Error**
   - Verificar logs: `tail -f /var/log/apache2/error.log`
   - Verificar permissões dos arquivos
   - Verificar sintaxe PHP: `php -l arquivo.php`

2. **Banco de dados não conecta**
   - Verificar credenciais em `config/database.php`
   - Verificar se o serviço está rodando
   - Verificar permissões do arquivo SQLite

3. **API não responde**
   - Verificar se o servidor web está rodando
   - Verificar configuração do virtual host
   - Verificar regras de firewall

4. **CORS errors**
   - Verificar headers CORS em `public/index.php`
   - Verificar configuração do servidor web

### Comandos Úteis

```bash
# Verificar status dos serviços
sudo systemctl status apache2
sudo systemctl status nginx
sudo systemctl status mysql

# Verificar logs
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log
tail -f /var/log/php_errors.log

# Testar configuração
apache2ctl configtest
nginx -t
php -m  # Verificar módulos instalados
```

---

Este guia cobre os principais cenários de deployment. Para ambientes específicos, consulte a documentação oficial das plataformas utilizadas.

