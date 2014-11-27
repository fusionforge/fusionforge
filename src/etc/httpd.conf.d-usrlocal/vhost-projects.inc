# per-project vhost content : myproject1.myforge.com, myproject2.myforge.com, etc.

ServerName siteadmin.{core/web_host}
ServerAlias *.{core/web_host}
DirectoryIndex index.html index.php index.htm

# This is magic for virtual hosting!
UseCanonicalName Off

VirtualDocumentRoot /var/local/lib/gforge/chroot/home/groups/%1/htdocs
# disabled for CVE-2014-6275
# Only enable it if you know what you are doing, by default all scripts run as Apache
#VirtualScriptAlias /var/local/lib/gforge/chroot/home/groups/%1/cgi-bin

SetEnvIf Host "(.*)\.{core/web_host}" AWSTATS_FORCE_CONFIG=group-$1

php_admin_value default_charset "UTF-8"
php_admin_value include_path "/etc/gforge/custom:/etc/gforge:/usr/local/share/gforge:/usr/local/share/gforge/www/include:."

Alias /themes-gforge/ /usr/local/share/gforge/www/themes/

<Directory /var/local/lib/gforge/chroot/home/groups>
  Options Indexes FollowSymlinks
  AllowOverride All
  
  Include /etc/gforge/httpd.conf.d/auth-projects.inc
</Directory>