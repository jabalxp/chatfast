# 📝 Próximos Passos - InfinityFree Setup

## ✅ Configuração Completa!

Suas credenciais InfinityFree já estão configuradas em `backend/config/constants.php`:

- **Host**: sql109.infinityfree.com
- **User**: if0_40308362
- **Password**: esTLOj3jMoR
- **Database**: if0_40308362_dbbank ✅ (já existe!)

## 🗄️ Passo 1: Importar o Schema no Banco Existente

Seu banco de dados **`if0_40308362_dbbank`** já está criado no InfinityFree!

## 📤 Passo 2: Importar o Schema

### Opção A: Via phpMyAdmin (Recomendado)

1. No painel InfinityFree, clique em **MySQL Databases**
2. Clique no botão **Manage** (abre phpMyAdmin)
3. Selecione o banco `if0_40308362_redesocial`
4. Clique na aba **SQL**
5. Abra o arquivo `database/schema.sql` em um editor
6. **IMPORTANTE**: InfinityFree limita queries grandes. Copie e execute em partes:

**Parte 1 - Tabelas de Usuários:**
```sql
-- Copie e execute apenas as tabelas: users, privacy_settings
```

**Parte 2 - Tabelas Sociais:**
```sql
-- Copie e execute: friendships, messages, group_chats, group_members, group_messages
```

**Parte 3 - Tabelas de Conteúdo:**
```sql
-- Copie e execute: posts, post_media, post_likes, comments, stories
```

**Parte 4 - Tabelas de Fóruns:**
```sql
-- Copie e execute: forums, forum_topics, forum_replies, notifications
```

### Opção B: Script Dividido

Criei scripts menores que você pode executar um por vez no phpMyAdmin.

## 📁 Passo 3: Upload via FTP

### Credenciais FTP do InfinityFree:

Você receberá por email ou no painel:
- **Host**: ftpupload.net
- **Username**: (geralmente o mesmo: if0_40308362)
- **Password**: (senha FTP, pode ser diferente)

### Estrutura de Upload:

```
htdocs/
├── api/
│   ├── auth.php
│   ├── messages.php
│   ├── posts.php
│   ├── users.php
│   └── friends.php
├── config/
│   ├── constants.php  ← Já configurado!
│   └── database.php
├── middleware/
│   └── cors.php
├── utils/
│   └── jwt.php
├── uploads/
│   ├── avatars/
│   ├── posts/
│   └── stories/
├── .htaccess
└── 404.html
```

### Usando FileZilla:

1. Baixe **FileZilla** (https://filezilla-project.org/)
2. Conecte com as credenciais FTP
3. Navegue até a pasta `htdocs/`
4. Arraste os arquivos do backend para lá

## 🌐 Passo 4: Testar a API

Depois do upload, teste:

**URL da sua API**:
```
https://seusite.infinityfreeapp.com/api/auth.php
```

**Teste de registro** (via navegador ou Postman):
```
POST https://seusite.infinityfreeapp.com/api/auth.php?action=register
Content-Type: application/json

{
  "username": "testuser",
  "email": "test@test.com",
  "password": "password123",
  "publicKey": "test_key_123"
}
```

## 🎨 Passo 5: Deploy do Frontend (Vercel)

1. **Push para GitHub**:
```bash
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/seu-usuario/rede-social.git
git push -u origin main
```

2. **Deploy no Vercel**:
   - Acesse https://vercel.com
   - Clique em "New Project"
   - Importe o repositório
   - Configure a variável de ambiente:
     ```
     NEXT_PUBLIC_API_URL=https://seusite.infinityfreeapp.com
     ```
   - Deploy automático! ✨

## 🔧 Testar Localmente Primeiro (Recomendado)

Antes de fazer o deploy, teste localmente:

1. **Configure banco local**: Use `constants.local.php`
2. **Inicie backend**: `cd backend; php -S localhost:8000`
3. **Inicie frontend**: `cd frontend; npm run dev`
4. **Teste registro/login**
5. **Depois faça o deploy**

## ⚠️ Observações Importantes

- InfinityFree tem **limite de 50.000 hits/dia**
- Uploads limitados a **10MB**
- Pode haver **delays** de até 24h para DNS propagar
- **Não suporta Node.js** (por isso frontend vai no Vercel)

## 📞 Precisa de Ajuda?

Se tiver algum erro durante o processo, me avise! Posso ajudar com:
- Dividir o schema.sql em partes menores
- Configurar CORS para seu domínio específico
- Troubleshooting de erros de conexão

---

**Status Atual**: ✅ Backend configurado para InfinityFree!
**Próximo**: Criar banco de dados e fazer upload via FTP
