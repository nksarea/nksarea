@ECHO OFF

if 1%error%1 EQU 11 goto :start

PING 1.1.1.1 -n 1 -w 10000 >NUL
pause

"C:\Program Files\WinRAR\Rar.exe" v -inull "C:\Users\Lorze\SkyDrive\Programming\php\nksarea1\projects\15.rar"
IF NOT %errorlevel% LEQ 1 set errortext=%errortext%, 1:%errorlevel%:rar
IF NOT %errorlevel% LEQ 1 set error=0

echo %error% %errortext%
exit

:start
	set error=1
	set errorText=
	cd "C:\Users\Lorze\SkyDrive\Programming\php\nksarea1\temp\4fcc6e915c105"
	command.bat > output.txt