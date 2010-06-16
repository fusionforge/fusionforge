ServerName {core/web_host}
ServerAlias www.{core/web_host}
ServerAdmin webmaster@{core/web_host}
DocumentRoot /opt/fusionforge/www
DirectoryIndex index.html index.php

<IfModule mod_userdir.c>
  UserDir disabled
</IfModule>

<Directory /opt/fusionforge/www>
  Include /opt/fusionforge/etc/httpd.conf.d/secrets.inc
</Directory>

<Location /projects>
  ForceType application/x-httpd-php
</Location>
<Location /users>
  ForceType application/x-httpd-php
</Location>

Alias /images/ /opt/fusionforge/www/images/

Include /opt/fusionforge/etc/httpd.conf.d/plugin-*.inc

# For FusionForge without DNS delegation, uncomment this
# and remove 30-vhosts-projects.conf
# Include /opt/fusionforge/etc/httpd.conf.d/projects-in-mainvhost.inc
  
