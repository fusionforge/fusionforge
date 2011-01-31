# per-project vhost content

ServerName siteadmin.{core/web_host}
ServerAlias *.{core/web_host}
DirectoryIndex index.html index.php index.htm

# This is magic for virtual hosting!
UseCanonicalName Off

VirtualDocumentRoot /var/lib/gforge/chroot/home/groups/%1/htdocs
VirtualScriptAlias /var/lib/gforge/chroot/home/groups/%1/cgi-bin

SetEnvIf Host "(.*)\.{core/web_host}" AWSTATS_FORCE_CONFIG=group-$1

php_admin_value default_charset "UTF-8"
php_admin_value include_path "/etc/gforge/custom:/etc/gforge:/opt/gforge:/opt/gforge/www/include:."

Alias /themes-gforge/ /opt/gforge/www/themes/

<Directory /var/lib/gforge/chroot/home/groups>
  Options Indexes FollowSymlinks
  AllowOverride All
  
  Include /etc/gforge/httpd.conf.d/auth-projects.inc
</Directory>
