@echo off
echo ================================================
echo   UPLOAD PARA INFINITYFREE
echo ================================================
echo.

set FTP_HOST=ftpupload.net
set FTP_USER=if0_40308362
set FTP_PASS=esTLOj3jMoR
set LOCAL_PATH=C:\Users\Parafodas\Desktop\TPTDS\rede-social\backend

echo Fazendo upload dos arquivos...
echo.

curl -T "%LOCAL_PATH%\config\constants.php" ftp://%FTP_HOST%/htdocs/config/constants.php --user %FTP_USER%:%FTP_PASS% --ftp-create-dirs
if %errorlevel%==0 (echo [OK] config/constants.php) else (echo [ERRO] config/constants.php)

curl -T "%LOCAL_PATH%\config\database.php" ftp://%FTP_HOST%/htdocs/config/database.php --user %FTP_USER%:%FTP_PASS%
if %errorlevel%==0 (echo [OK] config/database.php) else (echo [ERRO] config/database.php)

curl -T "%LOCAL_PATH%\middleware\cors.php" ftp://%FTP_HOST%/htdocs/middleware/cors.php --user %FTP_USER%:%FTP_PASS% --ftp-create-dirs
if %errorlevel%==0 (echo [OK] middleware/cors.php) else (echo [ERRO] middleware/cors.php)

curl -T "%LOCAL_PATH%\utils\jwt.php" ftp://%FTP_HOST%/htdocs/utils/jwt.php --user %FTP_USER%:%FTP_PASS% --ftp-create-dirs
if %errorlevel%==0 (echo [OK] utils/jwt.php) else (echo [ERRO] utils/jwt.php)

curl -T "%LOCAL_PATH%\api\auth.php" ftp://%FTP_HOST%/htdocs/api/auth.php --user %FTP_USER%:%FTP_PASS% --ftp-create-dirs
if %errorlevel%==0 (echo [OK] api/auth.php) else (echo [ERRO] api/auth.php)

curl -T "%LOCAL_PATH%\api\messages.php" ftp://%FTP_HOST%/htdocs/api/messages.php --user %FTP_USER%:%FTP_PASS%
if %errorlevel%==0 (echo [OK] api/messages.php) else (echo [ERRO] api/messages.php)

curl -T "%LOCAL_PATH%\api\posts.php" ftp://%FTP_HOST%/htdocs/api/posts.php --user %FTP_USER%:%FTP_PASS%
if %errorlevel%==0 (echo [OK] api/posts.php) else (echo [ERRO] api/posts.php)

curl -T "%LOCAL_PATH%\api\users.php" ftp://%FTP_HOST%/htdocs/api/users.php --user %FTP_USER%:%FTP_PASS%
if %errorlevel%==0 (echo [OK] api/users.php) else (echo [ERRO] api/users.php)

curl -T "%LOCAL_PATH%\api\friends.php" ftp://%FTP_HOST%/htdocs/api/friends.php --user %FTP_USER%:%FTP_PASS%
if %errorlevel%==0 (echo [OK] api/friends.php) else (echo [ERRO] api/friends.php)

curl -T "%LOCAL_PATH%\.htaccess" ftp://%FTP_HOST%/htdocs/.htaccess --user %FTP_USER%:%FTP_PASS%
if %errorlevel%==0 (echo [OK] .htaccess) else (echo [ERRO] .htaccess)

curl -T "%LOCAL_PATH%\test-connection.php" ftp://%FTP_HOST%/htdocs/test-connection.php --user %FTP_USER%:%FTP_PASS%
if %errorlevel%==0 (echo [OK] test-connection.php) else (echo [ERRO] test-connection.php)

curl -T "%LOCAL_PATH%\404.html" ftp://%FTP_HOST%/htdocs/404.html --user %FTP_USER%:%FTP_PASS%
if %errorlevel%==0 (echo [OK] 404.html) else (echo [ERRO] 404.html)

echo.
echo ================================================
echo   UPLOAD CONCLUIDO!
echo ================================================
echo.
echo Teste em: https://parafodas.infinityfreeapp.com/test-connection.php
echo.
pause
