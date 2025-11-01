@echo off
echo ====================================
echo Configurando Banco de Dados MySQL
echo ====================================
echo.

REM Locais comuns de instalação do MySQL
set MYSQL_PATHS[0]="C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"
set MYSQL_PATHS[1]="C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe"
set MYSQL_PATHS[2]="C:\Program Files (x86)\MySQL\MySQL Server 8.0\bin\mysql.exe"
set MYSQL_PATHS[3]="C:\xampp\mysql\bin\mysql.exe"
set MYSQL_PATHS[4]="C:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe"

set MYSQL_FOUND=0

for /L %%i in (0,1,4) do (
    if exist !MYSQL_PATHS[%%i]! (
        set MYSQL_PATH=!MYSQL_PATHS[%%i]!
        set MYSQL_FOUND=1
        goto :found
    )
)

:found
if %MYSQL_FOUND%==1 (
    echo MySQL encontrado em: %MYSQL_PATH%
    echo.
    echo Digite a senha do root do MySQL:
    %MYSQL_PATH% -u root -p < database\schema.sql
    echo.
    echo Banco de dados configurado com sucesso!
) else (
    echo ERRO: MySQL não encontrado!
    echo.
    echo Por favor, execute manualmente:
    echo 1. Abra o MySQL Workbench ou phpMyAdmin
    echo 2. Execute o arquivo: database\schema.sql
    echo.
    echo Ou adicione o MySQL ao PATH do Windows
)

pause
