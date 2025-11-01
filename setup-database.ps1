# Script PowerShell para configurar o banco de dados
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "Configurando Banco de Dados MySQL" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

# Caminho do MySQL no XAMPP
$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
$schemaFile = Join-Path $PSScriptRoot "database\schema.sql"

if (-not (Test-Path $mysqlPath)) {
    Write-Host "ERRO: MySQL não encontrado em $mysqlPath" -ForegroundColor Red
    Write-Host ""
    Write-Host "Por favor, use uma das alternativas:" -ForegroundColor Yellow
    Write-Host "1. MySQL Workbench - File > Run SQL Script" -ForegroundColor White
    Write-Host "2. phpMyAdmin - http://localhost/phpmyadmin" -ForegroundColor White
    exit 1
}

Write-Host "MySQL encontrado!" -ForegroundColor Green
Write-Host ""
Write-Host "Digite a senha do root do MySQL (pressione Enter se não tiver senha):" -ForegroundColor Yellow

# Executar o schema
& $mysqlPath -u root -p -e "source $schemaFile"

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✓ Banco de dados criado com sucesso!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Próximos passos:" -ForegroundColor Cyan
    Write-Host "1. Configure o arquivo backend/config/constants.php" -ForegroundColor White
    Write-Host "2. Inicie o backend: cd backend; php -S localhost:8000" -ForegroundColor White
} else {
    Write-Host ""
    Write-Host "✗ Erro ao criar banco de dados" -ForegroundColor Red
    Write-Host "Tente usar o MySQL Workbench ou phpMyAdmin" -ForegroundColor Yellow
}

Write-Host ""
Read-Host "Pressione Enter para continuar"
