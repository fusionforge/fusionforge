# This is used instead of *.forgename vhosts only

# Project home pages are in a virtual /www/<group> location
AliasMatch ^/www/([^/]*)/(.*) {gforge_chroot}{groupdir}/$1/htdocs/$2
ScriptAliasMatch ^/([^/]*)/cgi-bin/(.*) {gforge_chroot}{groupdir}/$1/cgi-bin/$2
<Directory {gforge_chroot}{groupdir}>
  Options Indexes FollowSymlinks
  AllowOverride All
  
  Include {gforge_etc}/httpd.conf.d/auth.projects.inc
</Directory>
