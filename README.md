# 🌐 Rede Social Privada

Uma plataforma de rede social descentralizada e privada com criptografia ponta a ponta, construída com **Next.js**, **React**, **TypeScript**, **shadcn/ui**, **PHP** e **MySQL**.

## 🎯 Funcionalidades

- ✅ **Criptografia End-to-End** (TweetNaCl.js)
- ✅ **Mensagens Instantâneas** criptografadas
- ✅ **Feed de Posts** com curtidas e comentários
- ✅ **Sistema de Amizades** com pedidos e bloqueio
- ✅ **Controle Total de Privacidade**
- ✅ **Interface Moderna** com shadcn/ui
- ✅ **Tema Claro/Escuro**
- ✅ **Autenticação JWT**
- ✅ **Upload de Mídia**
- 🚧 **WebRTC** para videochamadas (próxima fase)
- 🚧 **Fóruns Temáticos** (próxima fase)
- 🚧 **Stories Temporários** (próxima fase)

## 📋 Tecnologias

### Frontend
- **Framework**: Next.js 15 (App Router)
- **UI Library**: React 19
- **Linguagem**: TypeScript
- **Componentes UI**: shadcn/ui
- **Estilização**: Tailwind CSS
- **Criptografia**: TweetNaCl.js
- **HTTP Client**: Axios
- **Notificações**: Sonner

### Backend
- **Linguagem**: PHP 8.x
- **Banco de Dados**: MySQL 8.x
- **Autenticação**: JWT
- **Hash de Senhas**: bcrypt

## 🚀 Instalação e Setup

### Pré-requisitos

- **Node.js** 18+ e npm/pnpm
- **PHP** 8.0+
- **MySQL** 8.0+
- **Composer** (opcional, para dependências PHP futuras)

### 1. Clone o Repositório

```bash
cd rede-social
```

### 2. Setup do Frontend

```bash
cd frontend

# Instalar dependências
npm install
# ou
pnpm install

# Criar arquivo de ambiente
cp .env.local.example .env.local

# Editar .env.local com suas configurações
# NEXT_PUBLIC_API_URL=http://localhost:8000
```

### 3. Setup do Backend

#### Configurar Banco de Dados

```bash
# Entrar no MySQL
mysql -u root -p

# Criar banco de dados e importar schema
source database/schema.sql
```

#### Configurar Arquivo de Constantes

Edite `backend/config/constants.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
define('DB_NAME', 'rede_social');

// IMPORTANTE: Mude isso para uma chave secreta forte em produção!
define('JWT_SECRET', 'sua-chave-secreta-super-segura-aqui');
```

#### Criar Diretórios de Upload

```bash
# Windows PowerShell
New-Item -ItemType Directory -Force backend/uploads/avatars
New-Item -ItemType Directory -Force backend/uploads/posts
New-Item -ItemType Directory -Force backend/uploads/stories

# Linux/Mac
mkdir -p backend/uploads/{avatars,posts,stories}
chmod -R 755 backend/uploads
```

### 4. Iniciar os Servidores

#### Frontend (Terminal 1)

```bash
cd frontend
npm run dev
```

Acesse: http://localhost:3000

#### Backend (Terminal 2)

```bash
cd backend
php -S localhost:8000
```

API rodando em: http://localhost:8000

## 📁 Estrutura do Projeto

```
rede-social/
├── frontend/                    # Aplicação Next.js
│   ├── src/
│   │   ├── app/                # App Router (páginas)
│   │   │   ├── auth/          # Login e Registro
│   │   │   ├── feed/          # Feed de posts (próximo)
│   │   │   └── chat/          # Mensagens (próximo)
│   │   ├── components/        # Componentes React
│   │   │   ├── ui/           # shadcn/ui components
│   │   │   └── theme-provider.tsx
│   │   └── lib/              # Bibliotecas e utils
│   │       ├── api.ts        # Cliente API
│   │       ├── encryption.ts # Criptografia E2E
│   │       └── utils.ts      # Funções auxiliares
│   ├── package.json
│   ├── tailwind.config.ts
│   └── components.json       # Config shadcn/ui
│
├── backend/                    # API PHP
│   ├── api/                  # Endpoints REST
│   │   ├── auth.php         # Login/Registro
│   │   ├── messages.php     # Mensagens
│   │   ├── posts.php        # Posts
│   │   └── friends.php      # Amizades
│   ├── config/              # Configurações
│   │   ├── database.php     # Conexão DB
│   │   └── constants.php    # Constantes
│   ├── middleware/          # Middlewares
│   │   └── cors.php         # CORS headers
│   ├── utils/              # Utilitários
│   │   └── jwt.php         # JWT functions
│   └── uploads/            # Arquivos enviados
│
└── database/                # SQL Scripts
    └── schema.sql          # Schema completo
```

## 🔐 Segurança

### Criptografia End-to-End

As mensagens são criptografadas no cliente usando **TweetNaCl.js** (Curve25519-XSalsa20-Poly1305):

```typescript
// Gerar chaves ao registrar
const keyPair = generateKeyPair();

// Enviar mensagem criptografada
const encrypted = encryptMessage(
  message, 
  recipientPublicKey, 
  senderSecretKey
);

// Descriptografar mensagem recebida
const decrypted = decryptMessage(
  encryptedData, 
  senderPublicKey, 
  recipientSecretKey
);
```

### Autenticação JWT

- Tokens JWT com expiração de 24 horas
- Senhas com bcrypt (cost factor 10)
- Middleware de autenticação em todas as rotas protegidas

### CORS

Configurado para aceitar apenas `http://localhost:3000` (desenvolvimento). **Mudar em produção!**

## 🎨 Interface com shadcn/ui

Este projeto usa **shadcn/ui** para componentes de interface modernos e acessíveis:

- **Button**, **Input**, **Card**: Formulários e layouts
- **Avatar**: Perfis de usuário
- **Label**: Labels de formulários
- **Textarea**: Áreas de texto
- **Sonner**: Notificações toast

Adicione novos componentes:

```bash
npx shadcn-ui@latest add dialog
npx shadcn-ui@latest add scroll-area
```

## 📡 API Endpoints

### Autenticação

- `POST /api/auth.php?action=register` - Registrar usuário
- `POST /api/auth.php?action=login` - Login
- `POST /api/auth.php?action=logout` - Logout

### Mensagens

- `GET /api/messages.php?action=list&recipient_id={id}` - Listar mensagens
- `POST /api/messages.php?action=send` - Enviar mensagem
- `GET /api/messages.php?action=conversations` - Listar conversas
- `POST /api/messages.php?action=mark_read` - Marcar como lido

### Posts

- `GET /api/posts.php?action=feed` - Feed de posts
- `POST /api/posts.php?action=create` - Criar post
- `POST /api/posts.php?action=like` - Curtir post
- `POST /api/posts.php?action=comment` - Comentar em post
- `DELETE /api/posts.php?action=delete&post_id={id}` - Deletar post

### Amigos

- `GET /api/friends.php?action=list` - Listar amigos
- `POST /api/friends.php?action=request` - Enviar pedido de amizade
- `POST /api/friends.php?action=accept` - Aceitar pedido
- `POST /api/friends.php?action=reject` - Rejeitar pedido
- `GET /api/friends.php?action=pending` - Pedidos pendentes
- `POST /api/friends.php?action=block` - Bloquear usuário

## 🧪 Testando a Aplicação

### 1. Criar Conta

1. Acesse http://localhost:3000
2. Clique em "Registre-se"
3. Preencha os dados
4. Um par de chaves de criptografia será gerado automaticamente

### 2. Login

1. Entre com suas credenciais
2. Token JWT será salvo no localStorage
3. Você será redirecionado para o feed (quando implementado)

### 3. Enviar Mensagem (Próxima Fase)

1. Selecione um amigo
2. Digite a mensagem
3. A mensagem será criptografada antes de enviar
4. Apenas você e o destinatário podem ler

## 🚧 Próximas Fases

### Fase 2 (Em Desenvolvimento)
- [ ] Página de Feed com criação de posts
- [ ] Interface de Chat em tempo real
- [ ] Sistema de notificações
- [ ] Perfil de usuário editável

### Fase 3 (Futuro)
- [ ] WebRTC para videochamadas
- [ ] Stories temporários (24h)
- [ ] Fóruns temáticos
- [ ] Busca de usuários

### Fase 4 (Avançado)
- [ ] OAuth2 (Google, GitHub)
- [ ] Server-Sent Events para notificações em tempo real
- [ ] Export de dados (GDPR)
- [ ] Modo offline com Service Workers

## 🌐 Deploy em Produção

### InfinityFree (ou outro host PHP)

1. **Upload dos Arquivos**
   - Frontend: Build Next.js e configure servidor Node.js OU use export estático
   - Backend: Upload via FTP para `public_html/api/`

2. **Configurar Banco de Dados**
   - Criar DB no painel do host
   - Importar `database/schema.sql`
   - Atualizar `backend/config/constants.php`

3. **Configurar CORS**
   - Atualizar `backend/middleware/cors.php` com seu domínio

4. **Variáveis de Ambiente**
   - Atualizar `NEXT_PUBLIC_API_URL` para seu domínio de API

## 📝 Licença

Este projeto é de código aberto para fins educacionais.

## 👨‍💻 Contribuindo

Contribuições são bem-vindas! Por favor:

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📞 Suporte

Para dúvidas ou problemas, abra uma issue no GitHub.

---

**Desenvolvido com ❤️ usando Next.js, React, TypeScript, shadcn/ui e PHP**
