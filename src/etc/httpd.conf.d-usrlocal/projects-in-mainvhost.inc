# This is used instead of *.forgename vhosts only

# Project home pages are in a virtual /www/<group> location
AliasMatch ^/www/([^/]*)/(.*) /var/local/lib/gforge/chroot/home/groups/$1/htdocs/$2
# disabled for CVE-2014-6275
# Only enable it if you know what you are doing, by default all scripts run as Apache
#ScriptAliasMatch ^/([^/]*)/cgi-bin/(.*) /var/local/lib/gforge/chroot/home/groups/$1/cgi-bin/$2
<Directory /var/local/lib/gforge/chroot/home/groups>
  Options Indexes FollowSymlinks
  AllowOverride All
  
  Include /etc/gforge/httpd.conf.d/auth-projects.inc
</Directory>
