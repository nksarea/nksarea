@ECHO OFF

set error=1
set errorText=
cd "%{temp_dir}%"

%{insert}%

echo %error% %errortext%
cd..
rmdir /S /Q "%{temp_dir}%"
