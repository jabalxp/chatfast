# 🚀 GUIA COMPLETO DE DEPLOY

## Parte 1: Upload Backend para InfinityFree (via FTP)

### Você precisa de um cliente FTP. Opções:

#### **Opção A: FileZilla (Recomendado)**
1. Baixe: https://filezilla-project.org/
2. Instale e abra
3. Conecte:
   - Host: `ftpupload.net`
   - Username: `if0_40308362`
   - Password: `esTLOj3jMoR`
   - Port: `21`
4. No lado direito (servidor), entre em `htdocs/`
5. Arraste TODOS os arquivos de `backend/` para `htdocs/`
   - ⚠️ Os arquivos devem ficar em `htdocs/api/`, `htdocs/config/`, etc.
   - ⚠️ NÃO crie pasta `htdocs/backend/`

#### **Opção B: Painel InfinityFree (Mais lento)**
1. Acesse: https://app.infinityfree.com/
2. Vá em File Manager
3. Entre em `htdocs/`
4. Faça upload dos arquivos de `backend/`

### Após o upload, teste:
Acesse: `https://SEU_SITE.infinityfreeapp.com/test-connection.php`

Deve aparecer:
```json
{
  "success": true,
  "tables_count": 15
}
```

---

## Parte 2: Deploy Frontend no Vercel

### Passo 1: Criar repositório no GitHub

1. Acesse: https://github.com/new
2. Nome: `rede-social-privada`
3. Public ou Private (sua escolha)
4. **NÃO** marque opções extras
5. Clique em "Create repository"

### Passo 2: Push do código

No PowerShell, execute:
```powershell
cd C:\Users\Parafodas\Desktop\TPTDS\rede-social

git remote add origin https://github.com/SEU_USERNAME/rede-social-privada.git
git branch -M main
git push -u origin main
```

### Passo 3: Deploy no Vercel

1. Acesse: https://vercel.com/signup
2. Faça login com GitHub
3. Clique em "New Project"
4. Importe `rede-social-privada`
5. **Configure**:
   - **Framework Preset**: Next.js
   - **Root Directory**: `frontend` ← IMPORTANTE!
   - **Build Command**: `npm run build`
   - **Output Directory**: `.next`

6. **Environment Variables** (Adicione esta variável):
   - **Name**: `NEXT_PUBLIC_API_URL`
   - **Value**: `https://SEU_SITE.infinityfreeapp.com`

7. Clique em "Deploy"
8. Aguarde 2-3 minutos

---

## Parte 3: Configurar CORS no Backend

Se você mudar a URL do Vercel, precisa atualizar o CORS no InfinityFree:

1. Edite `htdocs/.htaccess` no InfinityFree
2. Na linha do `Access-Control-Allow-Origin`, adicione a URL do Vercel:
```apache
Header set Access-Control-Allow-Origin "https://SEU_PROJETO.vercel.app"
```

---

## ✅ Pronto!

Seu site estará no ar:
- **Frontend**: `https://SEU_PROJETO.vercel.app`
- **Backend API**: `https://SEU_SITE.infinityfreeapp.com`

### Testar:
1. Acesse o frontend
2. Tente fazer registro
3. Se der erro CORS, verifique o `.htaccess` no InfinityFree

---

## 🆘 Precisa de ajuda?

Me diga em qual passo você está!
