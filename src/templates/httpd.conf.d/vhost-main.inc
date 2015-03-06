
# We are inside <VirtualHost ...>

ServerName ${FF__core__web_host}
ServerAlias www.${FF__core__web_host}
ServerAdmin webmaster@${FF__core__web_host}
DocumentRoot ${FF__core__source_path}/www
DirectoryIndex index.html index.php

SetEnv FF__core__config_path "${FF__core__config_path}"

<IfModule mod_userdir.c>
  UserDir disabled
</IfModule>

# Define configuration/env variables for passing passwords and other secrets to PHP
<Directory ${FF__core__source_path}/www>
  Include ${FF__core__config_path}/httpd.conf.d/post-install-secrets.inc
</Directory>

<Location /projects>
  ForceType application/x-httpd-php
</Location>
<Location /users>
  ForceType application/x-httpd-php
</Location>

IncludeOptional ${FF__core__config_path}/httpd.conf.d/plugin-*.inc

Alias /images/ ${FF__core__source_path}/www/images/
Alias /scripts/coolfieldset/ ${FF__core__source_path}/vendor/coolfieldset/
Alias /scripts/iframe-resizer/ ${FF__core__source_path}/vendor/iframe-resizer/
Alias /scripts/jquery/ ${FF__core__source_path}/vendor/jquery/
Alias /scripts/jquery-ui/ ${FF__core__source_path}/vendor/jquery-ui/
Alias /scripts/jquery-storage/ ${FF__core__source_path}/vendor/jquery-storage/
Alias /scripts/jquery-simpletreemenu/ ${FF__core__source_path}/vendor/jquery-simpletreemenu/
Alias /scripts/jquery-jqplot/ ${FF__core__source_path}/vendor/jquery-jqplot/
Alias /scripts/jquery-livequery/ ${FF__core__source_path}/vendor/jquery-livequery/
Alias /scripts/jquery-teamwork-gantt/ ${FF__core__source_path}/vendor/jquery-teamwork-gantt/
Alias /scripts/jquery-splitter/ ${FF__core__source_path}/vendor/jquery-splitter/
Alias /scripts/jquery-auto-height/ ${FF__core__source_path}/vendor/jquery-auto-height/

# For FusionForge without DNS delegation, uncomment this
# and remove 30-vhosts-projects.conf
# Include ${FF__core__config_path}/httpd.conf.d/projects-in-mainvhost.inc

