ServerName {core/web_host}
ServerAlias www.{core/web_host}
ServerAdmin webmaster@{core/web_host}
DocumentRoot /usr/share/gforge/www
DirectoryIndex index.html index.php

<IfModule mod_userdir.c>
  UserDir disabled
</IfModule>

<Directory /usr/share/gforge/www>
  Include /etc/gforge/httpd.conf.d/secrets.inc
</Directory>

<Location /projects>
  ForceType application/x-httpd-php
</Location>
<Location /users>
  ForceType application/x-httpd-php
</Location>

Include /etc/gforge/httpd.conf.d/plugin-*.inc

Alias /images/ /usr/share/gforge/www/images/

# For FusionForge without DNS delegation, uncomment this
# and remove 30-vhosts-projects.conf
# Include /etc/gforge/httpd.conf.d/projects-in-mainvhost.inc
  
