# esp8266-php-telnet-uploader

just my share of what I hacked for my purpose of uploadig and testing files over telnet.

**Note: upload of files is a little slow, so please be patient**

setup on esp8266
=============
1. install tel.lua (basic example from nodemcu firmware) as init.lua (so that the telnet server starts on boot)
2. setup wifi connection as you need (AP/station,...)


setup on pc
========
1. Setup PHP&web server
2. Copy files there (and maybe tweak a bit)
3. Open in web browser
4. Optionally edit index.php and set different folder for files

todo
====
remember used commands
Speed up upload?
delete file button (you can use file.remove(FILE) command now)


requirements
=======
PHP,webserver

You need to know what IP you esp8266 module has/gets - maybe from your router's DHCP table or you may set it statically.
(I don't think that there is any other good way around this as there is not much memory on the ESP8266 and for me only the telnet has small enough footprint.)

