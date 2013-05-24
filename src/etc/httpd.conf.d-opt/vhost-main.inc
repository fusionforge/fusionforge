
# We are inside <VirtualHost ...>

ServerName {core/web_host}
ServerAlias www.{core/web_host}
ServerAdmin webmaster@{core/web_host}
DocumentRoot /opt/gforge/www
DirectoryIndex index.html index.php

<IfModule mod_userdir.c>
  UserDir disabled
</IfModule>

# Define configuration/env variables for passing passwords and other secrets to PHP
<Directory /opt/gforge/www>
  Include /etc/gforge/httpd.conf.d/secrets.inc
</Directory>

<Location /projects>
  ForceType application/x-httpd-php
</Location>
<Location /users>
  ForceType application/x-httpd-php
</Location>

<IfVersion >= 2.3>
  IncludeOptional /etc/gforge/httpd.conf.d/plugin-*.inc
</IfVersion>
<IfVersion < 2.3>
  Include /etc/gforge/httpd.conf.d/plugin-*.inc
</IfVersion>

Alias /images/ /opt/gforge/www/images/

# For FusionForge without DNS delegation, uncomment this
# and remove 30-vhosts-projects.conf
# Include /etc/gforge/httpd.conf.d/projects-in-mainvhost.inc
  
