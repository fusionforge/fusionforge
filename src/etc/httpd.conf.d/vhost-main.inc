ServerName {core/web_host}
ServerAlias www.{core/web_host}
ServerAdmin webmaster@{core/web_host}
DocumentRoot {core/source_path}/www
DirectoryIndex index.html index.php

<IfModule mod_userdir.c>
  UserDir disabled
</IfModule>

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
Alias /scripts/jquery/ {core/source_path}/lib/vendor/jquery/
Alias /scripts/jquery-tipsy/ {core/source_path}/lib/vendor/jquery-tipsy/
Alias /scripts/jquery-storage/ {core/source_path}/lib/vendor/jquery-storage/
Alias /scripts/prototype/ {core/source_path}/lib/vendor/prototype/
Alias /scripts/scriptaculous/ {core/source_path}/lib/vendor/scriptaculous/
Alias /scripts/yui/ {core/source_path}/lib/vendor/yui/build/

# For FusionForge without DNS delegation, uncomment this
# and remove 30-vhosts-projects.conf
# Include {core/config_path}/httpd.conf.d/projects-in-mainvhost.inc

