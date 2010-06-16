# per-project vhost content

ServerName siteadmin.{domain_name}
ServerAlias *.{domain_name}
DirectoryIndex index.html index.php index.htm

# This is magic for virtual hosting!
UseCanonicalName Off

VirtualDocumentRoot {gforge_chroot}{groupdir}/%1/htdocs
VirtualScriptAlias {gforge_chroot}{groupdir}/%1/cgi-bin

SetEnvIf Host "(.*)\.{domain_name}" AWSTATS_FORCE_CONFIG=group-$1

php_admin_value default_charset "UTF-8"
php_admin_value include_path "{sys_custom_path}:{gforge_etc}:{usr_share_gforge}:{usr_share_gforge}/www/include:."

Alias /themes-gforge/ {usr_share_gforge}/www/themes/

<Directory {gforge_chroot}{groupdir}>
  Options Indexes FollowSymlinks
  AllowOverride All
  
  Include {gforge_etc}/httpd.conf.d/auth.projects.inc
</Directory>
