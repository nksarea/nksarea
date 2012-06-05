@ECHO OFF

if 1%error%1 EQU 11 goto :start

%{insert}%

echo %error% %errortext%
exit

:start
	set error=1
	set errorText=
	cd "%{temp_dir}%"
	command.bat > output.txt