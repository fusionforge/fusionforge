# SSL is off by default to not provide a false sense of security
# If/when you have a real SSL certificate, uncomment the "really-on" line and 
# comment out the "off" line.

###Include {core/config_path}/httpd.conf.d/ssl-off.inc
# IT DOES BRAKE ALL, PLEASE DON'T COMMIT UNTESTED 
Include {core/config_path}/httpd.conf.d/ssl-really-on.inc

