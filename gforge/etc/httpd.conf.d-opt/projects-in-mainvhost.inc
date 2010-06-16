# This is used instead of *.forgename vhosts only

# Project home pages are in a virtual /www/<group> location
AliasMatch ^/www/([^/]*)/(.*) /opt/fusionforge/data/chroot/home/groups/$1/htdocs/$2
ScriptAliasMatch ^/([^/]*)/cgi-bin/(.*) /opt/fusionforge/data/chroot/home/groups/$1/cgi-bin/$2
<Directory /opt/fusionforge/data/chroot/home/groups>
  Options Indexes FollowSymlinks
  AllowOverride All
  
  Include /etc/gforge/httpd.conf.d/auth.projects.inc
</Directory>
