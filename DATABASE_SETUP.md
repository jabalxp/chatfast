# 🗄️ Configuração do Banco de Dados MySQL

## Opção 1: Usando MySQL Workbench (Recomendado)

1. **Abra o MySQL Workbench**
2. **Conecte ao servidor MySQL** (localhost)
3. **Clique em "Server" → "Data Import"**
4. **Selecione "Import from Self-Contained File"**
5. **Navegue até**: `C:\Users\Parafodas\Desktop\TPTDS\rede-social\database\schema.sql`
6. **Clique em "Start Import"**
7. ✅ **Pronto!**

## Opção 2: Usando phpMyAdmin (XAMPP/WAMP)

1. **Abra o phpMyAdmin** (http://localhost/phpmyadmin)
2. **Clique em "SQL" no menu superior**
3. **Abra o arquivo**: `database/schema.sql` em um editor de texto
4. **Copie todo o conteúdo**
5. **Cole na área de texto do phpMyAdmin**
6. **Clique em "Executar"**
7. ✅ **Pronto!**

## Opção 3: Linha de Comando (Manual)

Se você souber onde o MySQL está instalado:

### XAMPP:
```powershell
cd C:\xampp\mysql\bin
.\mysql.exe -u root -p
```

### MySQL Official:
```powershell
cd "C:\Program Files\MySQL\MySQL Server 8.0\bin"
.\mysql.exe -u root -p
```

Depois de conectar, execute:
```sql
source C:/Users/Parafodas/Desktop/TPTDS/rede-social/database/schema.sql
```

## Opção 4: Script Automático

Execute o arquivo `setup-database.bat` na raiz do projeto:

```powershell
.\setup-database.bat
```

## Verificar Instalação

Após importar o schema, verifique se funcionou:

```sql
USE rede_social;
SHOW TABLES;
```

Você deve ver estas tabelas:
- users
- friendships
- messages
- posts
- comments
- notifications
- privacy_settings
- forums
- stories
- group_chats
- etc.

## 🔧 Configurar Credenciais do Backend

Depois de criar o banco, edite o arquivo:

**`backend/config/constants.php`**

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Seu usuário MySQL
define('DB_PASS', '');               // Sua senha MySQL (vazio se não tiver)
define('DB_NAME', 'rede_social');
define('JWT_SECRET', 'mude-para-uma-chave-secreta-forte-e-aleatoria-123456789');
```

## ❓ Problemas Comuns

### "Access denied for user 'root'@'localhost'"
- Verifique a senha do root do MySQL
- Tente sem senha (campo vazio)

### "Unknown database 'rede_social'"
- O banco não foi criado
- O script `schema.sql` não foi executado completamente

### "Table already exists"
- O banco já foi criado antes
- Pode pular este passo ou deletar e recriar:
  ```sql
  DROP DATABASE rede_social;
  ```

## ✅ Próximo Passo

Depois de configurar o banco de dados, inicie o backend PHP:

```powershell
cd backend
php -S localhost:8000
```

🎉 **Seu sistema estará pronto para usar!**
