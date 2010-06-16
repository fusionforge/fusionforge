ServerName {domain_name}
ServerAlias www.{domain_name}
ServerAdmin {server_admin}
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

Alias /images/ {core/source_path}/www/images/

Include {core/config_path}/httpd.conf.d/plugin-*.inc

# For FusionForge without DNS delegation, uncomment this
# and remove 30-vhosts-projects.conf
# Include {core/config_path}/httpd.conf.d/projects-in-mainvhost.inc
  
