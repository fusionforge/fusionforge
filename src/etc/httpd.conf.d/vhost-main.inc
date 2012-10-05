
# We are inside <VirtualHost ...>

ServerName {core/web_host}
ServerAlias www.{core/web_host}
ServerAdmin webmaster@{core/web_host}
DocumentRoot {core/source_path}/www
DirectoryIndex index.html index.php

SetEnv FORGE_CONFIG_PATH "{core/config_path}"

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

Include {core/config_path}/httpd.conf.d/plugin-*.inc

Alias /images/ {core/source_path}/www/images/
Alias /scripts/coolfieldset/ {core/source_path}/lib/vendor/coolfieldset/
Alias /scripts/jquery/ {core/source_path}/lib/vendor/jquery/
Alias /scripts/jquery-ui/ {core/source_path}/lib/vendor/jquery-ui/
Alias /scripts/jquery-tipsy/ {core/source_path}/lib/vendor/jquery-tipsy/
Alias /scripts/jquery-storage/ {core/source_path}/lib/vendor/jquery-storage/
Alias /scripts/jquery-simpletreemenu/ {core/source_path}/lib/vendor/jquery-simpletreemenu/
Alias /scripts/jquery-jqplot/ {core/source_path}/lib/vendor/jquery-jqplot/
Alias /scripts/prototype/ {core/source_path}/lib/vendor/prototype/
Alias /scripts/scriptaculous/ {core/source_path}/lib/vendor/scriptaculous/
Alias /scripts/yui/ {core/source_path}/lib/vendor/yui/build/

# For FusionForge without DNS delegation, uncomment this
# and remove 30-vhosts-projects.conf
# Include {core/config_path}/httpd.conf.d/projects-in-mainvhost.inc

