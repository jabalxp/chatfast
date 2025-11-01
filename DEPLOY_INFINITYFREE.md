# 🚀 Deploy no InfinityFree - Guia Completo

Este guia mostra como fazer o deploy da sua rede social privada no **InfinityFree** (hospedagem gratuita).

## 📋 Pré-requisitos

- [ ] Conta no InfinityFree (https://infinityfree.net)
- [ ] Cliente FTP (FileZilla recomendado)
- [ ] Banco de dados MySQL criado no painel InfinityFree

## 🎯 Passo 1: Criar Conta no InfinityFree

1. Acesse https://infinityfree.net
2. Clique em **"Sign Up"**
3. Preencha os dados e confirme o email
4. Faça login no **Control Panel**

## 🗄️ Passo 2: Criar Banco de Dados MySQL

### No Painel InfinityFree:

1. Vá em **"MySQL Databases"**
2. Clique em **"Create Database"**
3. Anote as credenciais:
   ```
   Host: sqlXXX.infinityfreeapp.com
   Database: if0_XXXXXXX_redesocial
   Username: if0_XXXXXXX
   Password: (sua senha)
   ```

### Importar o Schema:

1. Vá em **"MySQL Databases"** → **"Manage"** (phpMyAdmin)
2. Clique na aba **"SQL"**
3. Copie o conteúdo do arquivo `database/schema.sql`
4. Cole na área de texto
5. Clique em **"Executar"**

⚠️ **IMPORTANTE**: InfinityFree tem limite de queries. Se der erro, divida o arquivo em partes menores.

## 📁 Passo 3: Preparar Arquivos para Upload

### Backend PHP

1. **Edite `backend/config/constants.php`** com as credenciais do InfinityFree:

```php
<?php
// InfinityFree Database Configuration
define('DB_HOST', 'sqlXXX.infinityfreeapp.com'); // Seu host
define('DB_USER', 'if0_XXXXXXX');                // Seu usuário
define('DB_PASS', 'sua_senha_aqui');             // Sua senha
define('DB_NAME', 'if0_XXXXXXX_redesocial');     // Nome do banco

// JWT Secret - MUDE PARA ALGO SEGURO!
define('JWT_SECRET', 'gere-uma-chave-aleatoria-super-segura-aqui-' . bin2hex(random_bytes(32)));

// Base URL - SEU DOMÍNIO
define('BASE_URL', 'https://seusite.infinityfreeapp.com');

// Upload directories (InfinityFree)
define('UPLOAD_DIR', __DIR__ . '/../htdocs/uploads/');
define('AVATARS_DIR', UPLOAD_DIR . 'avatars/');
define('POSTS_DIR', UPLOAD_DIR . 'posts/');
define('STORIES_DIR', UPLOAD_DIR . 'stories/');

// Allowed file types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm']);
define('ALLOWED_AUDIO_TYPES', ['audio/mpeg', 'audio/wav', 'audio/ogg']);

// Max file sizes (in bytes) - InfinityFree tem limite de 10MB
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);  // 5MB
define('MAX_VIDEO_SIZE', 10 * 1024 * 1024); // 10MB (máximo InfinityFree)
define('MAX_AUDIO_SIZE', 5 * 1024 * 1024);  // 5MB

// Create uploads directories if they don't exist
if (!file_exists(AVATARS_DIR)) {
    mkdir(AVATARS_DIR, 0755, true);
}
if (!file_exists(POSTS_DIR)) {
    mkdir(POSTS_DIR, 0755, true);
}
if (!file_exists(STORIES_DIR)) {
    mkdir(STORIES_DIR, 0755, true);
}
?>
```

2. **Edite `backend/middleware/cors.php`** com seu domínio:

```php
<?php
// Seu domínio no InfinityFree
$allowed_origins = [
    'https://seusite.infinityfreeapp.com',
    'http://localhost:3000' // Apenas para desenvolvimento
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
?>
```

### Frontend Next.js

Para o InfinityFree, você tem **2 opções**:

#### Opção A: Deploy Frontend em Outro Lugar (Recomendado)

Deploy o frontend no **Vercel** (gratuito e otimizado para Next.js):

1. Push o código para GitHub
2. Acesse https://vercel.com
3. Importe o repositório
4. Configure a variável de ambiente:
   ```
   NEXT_PUBLIC_API_URL=https://seusite.infinityfreeapp.com/api
   ```
5. Deploy automático! ✨

#### Opção B: Build Estático (Limitado)

Se quiser tudo no InfinityFree:

```bash
cd frontend
npm run build
```

Isso cria uma pasta `out/` com HTML estático. **Porém**: Next.js precisa de Node.js para funcionar totalmente, e InfinityFree só suporta PHP.

## 📤 Passo 4: Upload via FTP

### Configurar FileZilla:

1. **Host**: `ftpupload.net` ou `ftp.yourdomain.com`
2. **Username**: Seu username do InfinityFree (ex: `if0_XXXXXXX`)
3. **Password**: Senha FTP (diferente da senha do painel)
4. **Port**: 21

### Estrutura de Pastas no InfinityFree:

```
htdocs/                          ← Pasta raiz (public_html)
├── api/                         ← Backend PHP
│   ├── auth.php
│   ├── messages.php
│   ├── posts.php
│   ├── users.php
│   └── friends.php
├── config/
│   ├── constants.php
│   └── database.php
├── middleware/
│   └── cors.php
├── utils/
│   └── jwt.php
├── uploads/                     ← Criar essas pastas
│   ├── avatars/
│   ├── posts/
│   └── stories/
└── .htaccess                    ← Arquivo importante!
```

### Upload dos Arquivos:

1. **Backend**: Arraste todo conteúdo de `backend/` para `htdocs/`
2. **Uploads**: Crie as pastas `htdocs/uploads/avatars`, `posts`, `stories`
3. **Permissões**: Clique direito nas pastas uploads → **File Permissions** → `755`

## ⚙️ Passo 5: Configurar .htaccess

Crie o arquivo `htdocs/.htaccess`:

```apache
# Rewrite Engine
RewriteEngine On

# Permitir CORS
Header set Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header set Access-Control-Allow-Headers "Content-Type, Authorization"

# Redirecionar para HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Proteção de arquivos sensíveis
<FilesMatch ".(env|log|sql|json|md)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# PHP Settings
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value memory_limit 128M

# Prevent directory listing
Options -Indexes

# Error Pages
ErrorDocument 404 /404.html
ErrorDocument 500 /500.html
```

## 🧪 Passo 6: Testar a API

Teste cada endpoint:

### 1. Testar Conexão:
```
https://seusite.infinityfreeapp.com/api/auth.php?action=test
```

### 2. Testar Registro:
```bash
curl -X POST https://seusite.infinityfreeapp.com/api/auth.php?action=register \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","email":"test@test.com","password":"password123","publicKey":"test_key"}'
```

## 🌐 Passo 7: Configurar Frontend (Vercel)

### Variáveis de Ambiente no Vercel:

```
NEXT_PUBLIC_API_URL=https://seusite.infinityfreeapp.com
```

### Deploy:

1. Push para GitHub
2. Conectar repositório no Vercel
3. Deploy automático
4. Acesse `https://seu-projeto.vercel.app`

## ⚠️ Limitações do InfinityFree

1. **Sem WebSocket**: Server-Sent Events (SSE) em vez de WebSocket
2. **Limite de CPU**: 50.000 hits por dia
3. **Sem Node.js**: Frontend precisa ser em outra plataforma
4. **Upload**: Máximo 10MB por arquivo
5. **MySQL**: Queries limitadas por segundo

## 🔧 Troubleshooting

### Erro 500 - Internal Server Error
- Verifique permissões das pastas (755)
- Confira credenciais do banco em `constants.php`
- Veja logs de erro no painel InfinityFree

### CORS Error
- Verifique `middleware/cors.php`
- Adicione seu domínio Vercel aos origins permitidos

### Database Connection Failed
- Confirme que o banco foi criado
- Teste conexão no phpMyAdmin
- Verifique se importou o `schema.sql`

### Upload Não Funciona
- Verifique permissões: `chmod 755 uploads/`
- Confirme limites de tamanho (10MB máximo)

## 📊 Monitoramento

No painel InfinityFree você pode ver:
- Uso de banda
- Hits por dia
- Erros de PHP
- Tamanho do banco de dados

## 🎉 Pronto!

Sua rede social está no ar! Acesse:

- **Frontend**: https://seu-projeto.vercel.app
- **API**: https://seusite.infinityfreeapp.com/api

## 📝 Checklist Final

- [ ] Banco de dados criado e schema importado
- [ ] constants.php configurado com credenciais corretas
- [ ] Arquivos PHP enviados via FTP
- [ ] Pastas uploads criadas com permissões 755
- [ ] .htaccess configurado
- [ ] CORS configurado com domínio correto
- [ ] Frontend no Vercel com API_URL configurada
- [ ] Teste de registro funcionando
- [ ] Teste de login funcionando

---

**Dúvidas?** Consulte a documentação oficial do InfinityFree: https://forum.infinityfree.net
