# 🚀 Deploy InfinityFree - Passo a Passo RÁPIDO

## 📋 Informações da sua conta:
- **FTP Host**: Você precisa pegar no painel do InfinityFree
- **FTP User**: Você precisa pegar no painel do InfinityFree  
- **FTP Password**: Você precisa pegar no painel do InfinityFree
- **MySQL já configurado**: ✅ if0_40308362_dbbank

---

## 🔥 PASSO 1: Pegar credenciais FTP

1. Acesse: https://app.infinityfree.com/accounts
2. Clique na sua conta
3. Procure por "**FTP Details**" ou "**Account Details**"
4. Anote:
   - FTP Hostname (exemplo: `ftpupload.net` ou `ftp.yoursite.infinityfreeapp.com`)
   - FTP Username (exemplo: `if0_40308362`)
   - FTP Password (a mesma senha que você usa para login, ou outra específica)

---

## 🔥 PASSO 2: Baixar FileZilla

Se não tem FileZilla instalado:
1. Baixe: https://filezilla-project.org/download.php?type=client
2. Instale (é rápido)

---

## 🔥 PASSO 3: Upload dos arquivos

### No FileZilla:

1. **Conectar**:
   - Host: `ftp://[SEU_FTP_HOST]`
   - Username: `[SEU_FTP_USER]`
   - Password: `[SEU_FTP_PASSWORD]`
   - Port: `21`
   - Clique em "Quickconnect"

2. **Navegar para htdocs**:
   - No lado direito (servidor remoto), entre na pasta `htdocs/`

3. **Upload do Backend**:
   - No lado esquerdo, navegue até: `C:\Users\Parafodas\Desktop\TPTDS\rede-social\backend`
   - Selecione TUDO dentro da pasta backend:
     - `api/`
     - `config/`
     - `middleware/`
     - `utils/`
     - `uploads/`
     - `.htaccess`
     - `test-connection.php`
   - Arraste para o lado direito (pasta `htdocs/`)
   - **IMPORTANTE**: Os arquivos devem ficar em `htdocs/`, NÃO em `htdocs/backend/`

4. **Aguarde o upload** (pode demorar 2-5 minutos)

---

## 🔥 PASSO 4: Testar a conexão

Após o upload, acesse no navegador:
```
https://[SEU_SITE].infinityfreeapp.com/test-connection.php
```

Deve aparecer:
```json
{
  "success": true,
  "message": "Conexão bem-sucedida!",
  "tables_count": 15
}
```

---

## 🔥 PASSO 5: Deploy do Frontend (Vercel)

### Criar conta no Vercel:
1. Acesse: https://vercel.com/signup
2. Faça login com GitHub (recomendado)

### Fazer Push para GitHub:

Abra o PowerShell na pasta do projeto e execute:

```powershell
cd C:\Users\Parafodas\Desktop\TPTDS\rede-social

# Inicializar Git
git init
git add .
git commit -m "Initial commit - Rede Social"

# Criar repositório no GitHub
# 1. Acesse: https://github.com/new
# 2. Nome: rede-social-privada
# 3. Deixe como Public ou Private
# 4. NÃO adicione README, .gitignore ou license
# 5. Clique em "Create repository"

# Copie os comandos que aparecem na tela "...or push an existing repository"
# Exemplo:
git remote add origin https://github.com/SEU_USUARIO/rede-social-privada.git
git branch -M main
git push -u origin main
```

### Deploy no Vercel:

1. Acesse: https://vercel.com/new
2. Clique em "Import Git Repository"
3. Selecione seu repositório `rede-social-privada`
4. **Configure**:
   - Framework Preset: **Next.js**
   - Root Directory: `frontend`
   - Build Command: `npm run build`
   - Output Directory: `.next`
5. **Environment Variables** - Adicione:
   - Name: `NEXT_PUBLIC_API_URL`
   - Value: `https://[SEU_SITE].infinityfreeapp.com`
6. Clique em "**Deploy**"
7. Aguarde 2-3 minutos

---

## ✅ PRONTO!

Seu site estará disponível em:
- **Frontend**: `https://[SEU_PROJETO].vercel.app`
- **Backend**: `https://[SEU_SITE].infinityfreeapp.com`

---

## 🆘 Me diga qual passo você está!

Estou aqui para ajudar em cada etapa! 🚀
