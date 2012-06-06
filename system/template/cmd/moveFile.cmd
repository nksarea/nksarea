move "%{source}%" "%{destination}%"
IF NOT %errorlevel% EQU 0 set errortext=%errortext%, 1:%errorlevel%:move
IF NOT %errorlevel% EQU 0 set error=0