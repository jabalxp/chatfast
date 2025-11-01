import ftplib
import os

# InfinityFree FTP credentials
FTP_HOST = 'ftpupload.net'
FTP_USER = 'if0_40308362'
FTP_PASS = 'esTLOj3jMoR'
FTP_DIR = 'htdocs'

def upload_file(local_path, remote_path):
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        ftp.cwd(FTP_DIR)
        
        with open(local_path, 'rb') as f:
            ftp.storbinary(f'STOR {remote_path}', f)
        
        print(f'✅ Uploaded: {remote_path}')
        ftp.quit()
        return True
    except Exception as e:
        print(f'❌ Error: {e}')
        return False

# Upload auth.php
upload_file('backend/api/auth.php', 'api/auth.php')
print('\n✅ Upload concluído! Teste: https://cururui.ct.ws/api/auth.php?action=register')
