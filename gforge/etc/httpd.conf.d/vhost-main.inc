ServerName {domain_name}
ServerAlias www.{domain_name}
ServerAdmin {server_admin}
DocumentRoot {usr_share_gforge}/www
DirectoryIndex index.html index.php

<IfModule mod_userdir.c>
  UserDir disabled
</IfModule>

<Directory {usr_share_gforge}/www>
  Include {gforge_etc}/httpd.conf.d/secrets.inc
</Directory>

<Location /projects>
  ForceType application/x-httpd-php
</Location>
<Location /users>
  ForceType application/x-httpd-php
</Location>

Alias /images/ {usr_share_gforge}/www/images/

Include {gforge_etc}/httpd.conf.d/plugin-*.inc

# For FusionForge without DNS delegation, uncomment this
# and remove 30-vhosts-projects.conf
# Include {gforge_etc}/httpd.conf.d/projects-in-mainvhost.inc
  
