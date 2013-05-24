
# We are inside <VirtualHost ...>

ServerName {core/web_host}
ServerAlias www.{core/web_host}
ServerAdmin webmaster@{core/web_host}
DocumentRoot {core/source_path}/www
DirectoryIndex index.html index.php

<IfModule mod_userdir.c>
  UserDir disabled
</IfModule>

# Define configuration/env variables for passing passwords and other secrets to PHP
<Directory {core/source_path}/www>
  Include {core/config_path}/httpd.conf.d/secrets.inc
</Directory>

<Location /projects>
  ForceType application/x-httpd-php
</Location>
<Location /users>
  ForceType application/x-httpd-php
</Location>

<IfVersion >= 2.3>
  IncludeOptional {core/config_path}/httpd.conf.d/plugin-*.inc
</IfVersion>
<IfVersion < 2.3>
  Include {core/config_path}/httpd.conf.d/plugin-*.inc
</IfVersion>

Alias /images/ {core/source_path}/www/images/

# For FusionForge without DNS delegation, uncomment this
# and remove 30-vhosts-projects.conf
# Include {core/config_path}/httpd.conf.d/projects-in-mainvhost.inc
  
