@ECHO OFF

set error=1
set errorText=
cd "C:\Users\Lorze\SkyDrive\Programming\php\nksarea1\temp\4fcc6f22b5d82"

"C:\Program Files\WinRAR\Rar.exe" y -inull "C:\Users\Lorze\SkyDrive\Programming\php\nksarea1\projects\15.rar"
IF NOT %errorlevel% LEQ 1 set errortext=%errortext%, 1:%errorlevel%:rar
IF NOT %errorlevel% LEQ 1 set error=0

echo %error% %errortext%
cd..
rem rmdir /S /Q "C:\Users\Lorze\SkyDrive\Programming\php\nksarea1\temp\4fcc6f22b5d82"
