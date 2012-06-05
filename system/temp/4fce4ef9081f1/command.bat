@ECHO OFF

set error=1
set errorText=
cd "C:/Users/Lorze/Documents/git/nksarea/system/temp/4fce4ef9081f1"

"C:\Program Files\WinRAR\Rar.exe" x -inull "C:/Users/Lorze/Documents/git/nksarea/data/projects/15.rar"
IF NOT %errorlevel% LEQ 1 set errortext=%errortext%, 1:%errorlevel%:rar
IF NOT %errorlevel% LEQ 1 set error=0

echo %error% %errortext%
cd..
rem rmdir /S /Q "C:/Users/Lorze/Documents/git/nksarea/system/temp/4fce4ef9081f1"
