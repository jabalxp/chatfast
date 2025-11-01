# Script de Upload FTP para InfinityFree
# Credenciais
$ftpServer = "ftp://ftpupload.net"
$ftpUsername = "if0_40308362"
$ftpPassword = "esTLOj3jMoR"
$localPath = "C:\Users\Parafodas\Desktop\TPTDS\rede-social\backend"

Write-Host "=================================" -ForegroundColor Cyan
Write-Host "  Upload para InfinityFree" -ForegroundColor Cyan
Write-Host "=================================" -ForegroundColor Cyan
Write-Host ""

# Função para fazer upload de arquivo
function Upload-File {
    param (
        [string]$LocalFile,
        [string]$RemotePath
    )
    
    try {
        $fileName = Split-Path $LocalFile -Leaf
        $ftpUri = "$ftpServer/htdocs/$RemotePath"
        
        Write-Host "Uploading: $RemotePath" -ForegroundColor Yellow
        
        $webclient = New-Object System.Net.WebClient
        $webclient.Credentials = New-Object System.Net.NetworkCredential($ftpUsername, $ftpPassword)
        $webclient.UploadFile($ftpUri, $LocalFile)
        
        Write-Host "  ✓ OK" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "  ✗ Erro: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Função para criar diretório FTP
function Create-FtpDirectory {
    param (
        [string]$RemoteDir
    )
    
    try {
        $ftpUri = "$ftpServer/htdocs/$RemoteDir"
        $request = [System.Net.FtpWebRequest]::Create($ftpUri)
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUsername, $ftpPassword)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $response = $request.GetResponse()
        $response.Close()
        Write-Host "Criado diretório: $RemoteDir" -ForegroundColor Cyan
    }
    catch {
        # Diretório pode já existir, ignorar erro
    }
}

# Criar estrutura de diretórios
Write-Host "Criando estrutura de pastas..." -ForegroundColor Cyan
Create-FtpDirectory "api"
Create-FtpDirectory "config"
Create-FtpDirectory "middleware"
Create-FtpDirectory "utils"
Create-FtpDirectory "uploads"
Create-FtpDirectory "uploads/avatars"
Create-FtpDirectory "uploads/posts"
Create-FtpDirectory "uploads/stories"
Write-Host ""

# Lista de arquivos para upload
$files = @{
    # Config
    "config/constants.php" = "config/constants.php"
    "config/database.php" = "config/database.php"
    
    # Middleware
    "middleware/cors.php" = "middleware/cors.php"
    
    # Utils
    "utils/jwt.php" = "utils/jwt.php"
    
    # API
    "api/auth.php" = "api/auth.php"
    "api/messages.php" = "api/messages.php"
    "api/posts.php" = "api/posts.php"
    "api/users.php" = "api/users.php"
    "api/friends.php" = "api/friends.php"
    
    # Root files
    ".htaccess" = ".htaccess"
    "test-connection.php" = "test-connection.php"
    "404.html" = "404.html"
}

Write-Host "Iniciando upload de arquivos..." -ForegroundColor Cyan
Write-Host ""

$sucessos = 0
$erros = 0

foreach ($file in $files.GetEnumerator()) {
    $localFile = Join-Path $localPath $file.Key
    
    if (Test-Path $localFile) {
        if (Upload-File -LocalFile $localFile -RemotePath $file.Value) {
            $sucessos++
        } else {
            $erros++
        }
    } else {
        Write-Host "Arquivo não encontrado: $($file.Key)" -ForegroundColor Red
        $erros++
    }
}

Write-Host ""
Write-Host "=================================" -ForegroundColor Cyan
Write-Host "  Resultado do Upload" -ForegroundColor Cyan
Write-Host "=================================" -ForegroundColor Cyan
Write-Host "Sucessos: $sucessos" -ForegroundColor Green
Write-Host "Erros: $erros" -ForegroundColor Red
Write-Host ""
Write-Host "Teste a conexão em:" -ForegroundColor Yellow
Write-Host "https://parafodas.infinityfreeapp.com/test-connection.php" -ForegroundColor Cyan
Write-Host ""
