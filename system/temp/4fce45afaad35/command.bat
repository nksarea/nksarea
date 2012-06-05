@ECHO OFF

set error=1
set errorText=
cd "system/temp/4fce45afaad35"

"C:\Program Files\WinRAR\Rar.exe" y -inull "C:/Users/Lorze/Documents/git/nksareadata/projects/15.rar"
IF NOT %errorlevel% LEQ 1 set errortext=%errortext%, 1:%errorlevel%:rar
IF NOT %errorlevel% LEQ 1 set error=0

echo %error% %errortext%
cd..
rem rmdir /S /Q "system/temp/4fce45afaad35"
