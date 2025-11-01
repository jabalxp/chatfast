import os
import ftplib
from pathlib import Path

# Configurações FTP
FTP_HOST = "ftpupload.net"
FTP_USER = "if0_40308362"
FTP_PASS = "esTLOj3jMoR"
LOCAL_PATH = r"C:\Users\Parafodas\Desktop\TPTDS\rede-social\backend"

print("=" * 50)
print("  UPLOAD PARA INFINITYFREE")
print("=" * 50)
print()

# Conectar ao FTP
print(f"Conectando a {FTP_HOST}...")
ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)
print("✓ Conectado com sucesso!\n")

# Mudar para htdocs
ftp.cwd('/htdocs')

def criar_pasta_ftp(pasta):
    """Cria pasta no FTP se não existir"""
    try:
        ftp.mkd(pasta)
        print(f"✓ Criada pasta: {pasta}")
    except:
        pass  # Pasta já existe

def upload_arquivo(local_file, remote_file):
    """Faz upload de um arquivo"""
    try:
        with open(local_file, 'rb') as f:
            ftp.storbinary(f'STOR {remote_file}', f)
        print(f"✓ {remote_file}")
        return True
    except Exception as e:
        print(f"✗ Erro em {remote_file}: {e}")
        return False

# Criar estrutura de pastas
print("Criando estrutura de pastas...")
criar_pasta_ftp('api')
criar_pasta_ftp('config')
criar_pasta_ftp('middleware')
criar_pasta_ftp('utils')
criar_pasta_ftp('uploads')
criar_pasta_ftp('uploads/avatars')
criar_pasta_ftp('uploads/posts')
criar_pasta_ftp('uploads/stories')
print()

# Lista de arquivos para upload
arquivos = {
    'config/constants.php': 'config/constants.php',
    'config/database.php': 'config/database.php',
    'middleware/cors.php': 'middleware/cors.php',
    'utils/jwt.php': 'utils/jwt.php',
    'api/auth.php': 'api/auth.php',
    'api/messages.php': 'api/messages.php',
    'api/posts.php': 'api/posts.php',
    'api/users.php': 'api/users.php',
    'api/friends.php': 'api/friends.php',
    '.htaccess': '.htaccess',
    'test-connection.php': 'test-connection.php',
    '404.html': '404.html',
}

print("Fazendo upload dos arquivos...")
sucessos = 0
erros = 0

for local, remoto in arquivos.items():
    local_file = os.path.join(LOCAL_PATH, local)
    if os.path.exists(local_file):
        if upload_arquivo(local_file, remoto):
            sucessos += 1
        else:
            erros += 1
    else:
        print(f"✗ Arquivo não encontrado: {local}")
        erros += 1

# Fechar conexão
ftp.quit()

print()
print("=" * 50)
print(f"✓ Sucessos: {sucessos}")
print(f"✗ Erros: {erros}")
print("=" * 50)
print()
print("Teste a conexão em:")
print("https://parafodas.infinityfreeapp.com/test-connection.php")
print()
