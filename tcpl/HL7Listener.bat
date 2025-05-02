@ECHO OFF
START c:/wamp/www/main/tcpl/HL7Listener.exe -Port 5100 -FilePath c:/wamp/www/main/inbound/in -NoACK
EXIT 0