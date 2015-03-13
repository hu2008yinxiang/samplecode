@echo off
robocopy /MIR /R:3 /W:1 /Z /COPY:DT /DCOPY:D /NDL %~dp0 "\\192.168.110.4\projects\php_poker" /XD .svn tools iphotos
::pause
exit