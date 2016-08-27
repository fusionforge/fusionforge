
# We are inside <VirtualHost ...>

ServerName ${FF__core__web_host}
ServerAlias www.${FF__core__web_host}
ServerAdmin webmaster@${FF__core__web_host}
ServerPath ${FF__core__url_prefix}
DocumentRoot ${FF__core__source_path}/www
DirectoryIndex index.html index.php

SetEnv FF__core__config_path "${FF__core__config_path}"

<IfModule mod_userdir.c>
  UserDir disabled
</IfModule>

# Main directory options (${FF__core__source_path}/www contains the
# PHP scripts of FusionForge)
<Directory ${FF__core__source_path}/www>
  Options Indexes FollowSymlinks
  AllowOverride All

  # define access restrictions for the main site
  Include ${FF__core__config_path}/httpd.conf.d/auth-main.inc

  #
  # PHP settings
  #
  php_admin_value default_charset "UTF-8"
  php_flag register_globals "off"
  php_flag magic_quotes_gpc "off"

  # Report E_ALL errors
  php_admin_value error_reporting 2147483647

  # Don't echo error to user
  php_admin_value display_errors 0

  # ...but log the errors to error.log
  php_admin_value log_errors 1

  # Define configuration/env variables for passing passwords and other secrets to PHP
  RequestHeader unset FusionForgeDbhost
  RequestHeader append FusionForgeDbhost "${FF__core__database_host}"

  RequestHeader unset FusionForgeDbport
  RequestHeader append FusionForgeDbport "${FF__core__database_port}"

  RequestHeader unset FusionForgeDbname
  RequestHeader append FusionForgeDbname "${FF__core__database_name}"

  RequestHeader unset FusionForgeDbuser
  RequestHeader append FusionForgeDbuser "${FF__core__database_user}"

  RequestHeader unset FusionForgeDbpasswd
  RequestHeader append FusionForgeDbpasswd "${FF__core__database_password}"

  RequestHeader unset FusionForgeSessionKey
  RequestHeader append FusionForgeSessionKey "${FF__core__session_key}"
</Directory>

# Directory options for vendor dir (${FF__core__source_path}/vendor
# contains the Javascript scripts of FusionForge)
<Directory ${FF__core__source_path}/vendor>
  Options Indexes FollowSymlinks
  AllowOverride All

  Include ${FF__core__config_path}/httpd.conf.d/auth-main.inc

</Directory>

<Location ${FF__core__url_prefix}projects>
  ForceType application/x-httpd-php
</Location>
<Location ${FF__core__url_prefix}users>
  ForceType application/x-httpd-php
</Location>

IncludeOptional ${FF__core__config_path}/httpd.conf.d/plugin-*.inc

Alias ${FF__core__url_prefix}images/ ${FF__core__source_path}/www/images/
Alias ${FF__core__url_prefix}scripts/coolfieldset/ ${FF__core__source_path}/vendor/coolfieldset/
Alias ${FF__core__url_prefix}scripts/iframe-resizer/ ${FF__core__source_path}/vendor/iframe-resizer/
Alias ${FF__core__url_prefix}scripts/jquery/ ${FF__core__source_path}/vendor/jquery/
Alias ${FF__core__url_prefix}scripts/jquery-ui/ ${FF__core__source_path}/vendor/jquery-ui/
Alias ${FF__core__url_prefix}scripts/jquery-storage/ ${FF__core__source_path}/vendor/jquery-storage/
Alias ${FF__core__url_prefix}scripts/jquery-simpletreemenu/ ${FF__core__source_path}/vendor/jquery-simpletreemenu/
Alias ${FF__core__url_prefix}scripts/jquery-jqplot/ ${FF__core__source_path}/vendor/jquery-jqplot/
Alias ${FF__core__url_prefix}scripts/jquery-livequery/ ${FF__core__source_path}/vendor/jquery-livequery/
Alias ${FF__core__url_prefix}scripts/jquery-teamwork-gantt/ ${FF__core__source_path}/vendor/jquery-teamwork-gantt/
Alias ${FF__core__url_prefix}scripts/jquery-splitter/ ${FF__core__source_path}/vendor/jquery-splitter/
Alias ${FF__core__url_prefix}scripts/jquery-auto-height/ ${FF__core__source_path}/vendor/jquery-auto-height/

# For FusionForge without DNS delegation, uncomment this
# and remove 30-vhosts-projects.conf
# Include ${FF__core__config_path}/httpd.conf.d/projects-in-mainvhost.inc

