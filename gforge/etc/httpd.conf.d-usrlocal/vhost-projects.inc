# per-project vhost content

ServerName siteadmin.{domain_name}
ServerAlias *.{domain_name}
DirectoryIndex index.html index.php index.htm

# This is magic for virtual hosting!
UseCanonicalName Off

VirtualDocumentRoot /var/local/lib/gforge/chroot/home/groups/%1/htdocs
VirtualScriptAlias /var/local/lib/gforge/chroot/home/groups/%1/cgi-bin

SetEnvIf Host "(.*)\.{domain_name}" AWSTATS_FORCE_CONFIG=group-$1

php_admin_value default_charset "UTF-8"
php_admin_value include_path "/etc/gforge/custom:/etc/gforge:/usr/local/share/gforge:/usr/local/share/gforge/www/include:."

Alias /themes-gforge/ /usr/local/share/gforge/www/themes/

<Directory /var/local/lib/gforge/chroot/home/groups>
  Options Indexes FollowSymlinks
  AllowOverride All
  
  Include /etc/gforge/httpd.conf.d/auth.projects.inc
</Directory>
