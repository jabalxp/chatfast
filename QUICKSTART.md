# 🚀 Quick Start Guide

Este guia rápido ajudará você a configurar e executar o projeto em menos de 10 minutos!

## ⚡ Setup Rápido

### 1. Instalar Dependências Frontend

```powershell
cd frontend
npm install
```

### 2. Configurar Banco de Dados

```powershell
# Abrir MySQL
mysql -u root -p

# No prompt do MySQL:
```

```sql
CREATE DATABASE rede_social;
USE rede_social;
SOURCE ../database/schema.sql;
EXIT;
```

### 3. Configurar Backend

Edite `backend/config/constants.php`:

```php
define('DB_USER', 'root');
define('DB_PASS', 'sua_senha_mysql');
define('JWT_SECRET', 'mude-para-uma-chave-segura-aleatoria');
```

### 4. Criar Arquivo .env

```powershell
cd frontend
Copy-Item .env.local.example .env.local
```

### 5. Iniciar Servidores

**Terminal 1 - Frontend:**
```powershell
cd frontend
npm run dev
```

**Terminal 2 - Backend:**
```powershell
cd backend
php -S localhost:8000
```

### 6. Testar!

1. Acesse: http://localhost:3000
2. Clique em "Registre-se"
3. Crie uma conta
4. Faça login
5. ✅ Pronto!

## 🎯 Testando Funcionalidades

### Criar Usuários de Teste

Use o MySQL para criar um segundo usuário:

```sql
-- Senha: password123
INSERT INTO users (username, email, password_hash, public_key, created_at) VALUES
('testuser2', 'test2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sample_public_key_2', NOW());
```

### Testar API com cURL

```powershell
# Registrar usuário
curl -X POST http://localhost:8000/api/auth.php?action=register `
  -H "Content-Type: application/json" `
  -d '{\"username\":\"testuser\",\"email\":\"test@test.com\",\"password\":\"password123\",\"publicKey\":\"test_key\"}'

# Login
curl -X POST http://localhost:8000/api/auth.php?action=login `
  -H "Content-Type: application/json" `
  -d '{\"email\":\"test@test.com\",\"password\":\"password123\"}'
```

## 🐛 Troubleshooting

### Erro: "Cannot find module"
```powershell
cd frontend
rm -rf node_modules package-lock.json
npm install
```

### Erro: "Database connection failed"
- Verifique se o MySQL está rodando
- Confirme usuário e senha em `backend/config/constants.php`
- Verifique se o banco `rede_social` existe

### Erro: CORS
- Certifique-se de que o backend está rodando em `localhost:8000`
- O frontend deve estar em `localhost:3000`

### Porta em uso
```powershell
# Frontend em porta diferente
npm run dev -- -p 3001

# Backend em porta diferente
php -S localhost:8080
# Atualizar NEXT_PUBLIC_API_URL em .env.local
```

## 📖 Próximos Passos

Consulte o [README.md](README.md) principal para:
- Estrutura completa do projeto
- API endpoints disponíveis
- Arquitetura de segurança
- Roadmap de desenvolvimento

---

**Bom desenvolvimento! 🎉**
