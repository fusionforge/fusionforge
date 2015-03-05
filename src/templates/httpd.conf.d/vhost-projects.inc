# per-project vhost content : myproject1.myforge.com, myproject2.myforge.com, etc.

ServerName siteadmin.${FF__core__web_host}
ServerAlias *.${FF__core__web_host}
DirectoryIndex index.html index.php index.htm

SetEnv FF__core__config_path "${FF__core__config_path}"

# This is magic for virtual hosting!
UseCanonicalName Off

VirtualDocumentRoot ${FF__core__groupdir_prefix}/%1/htdocs
# disabled for CVE-2014-6275
# Only enable it if you know what you are doing, by default all scripts run as Apache
#VirtualScriptAlias ${FF__core__groupdir_prefix}/%1/cgi-bin

SetEnvIf Host "(.*)\.${FF__core__web_host}" AWSTATS_FORCE_CONFIG=group-$1

php_admin_value default_charset "UTF-8"
php_admin_value include_path "${FF__core__custom_path}:${FF__core__config_path}:${FF__core__source_path}:${FF__core__source_path}/www/include:."

Alias /themes-gforge/ ${FF__core__source_path}/www/themes/

<Directory ${FF__core__groupdir_prefix}>
  Options Indexes FollowSymlinks
  AllowOverride All
  
  Include ${FF__core__config_path}/httpd.conf.d/auth-projects.inc
</Directory>
