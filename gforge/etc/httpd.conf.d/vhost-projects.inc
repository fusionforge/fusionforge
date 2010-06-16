# per-project vhost content

ServerName siteadmin.{domain_name}
ServerAlias *.{domain_name}
DirectoryIndex index.html index.php index.htm

# This is magic for virtual hosting!
UseCanonicalName Off

VirtualDocumentRoot {core/groupdir_prefix}/%1/htdocs
VirtualScriptAlias {core/groupdir_prefix}/%1/cgi-bin

SetEnvIf Host "(.*)\.{domain_name}" AWSTATS_FORCE_CONFIG=group-$1

php_admin_value default_charset "UTF-8"
php_admin_value include_path "{core/custom_path}:{core/config_path}:{core/source_path}:{core/source_path}/www/include:."

Alias /themes-gforge/ {core/source_path}/www/themes/

<Directory {core/groupdir_prefix}>
  Options Indexes FollowSymlinks
  AllowOverride All
  
  Include {core/config_path}/httpd.conf.d/auth.projects.inc
</Directory>
