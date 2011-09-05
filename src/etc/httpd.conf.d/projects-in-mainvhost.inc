# This is used instead of *.forgename vhosts only

# Project home pages are in a virtual /www/<group> location
AliasMatch ^/www/([^/]*)/(.*) {core/groupdir_prefix}/$1/htdocs/$2
ScriptAliasMatch ^/([^/]*)/cgi-bin/(.*) {core/groupdir_prefix}/$1/cgi-bin/$2
<Directory {core/groupdir_prefix}>
  Options Indexes FollowSymlinks
  AllowOverride All
  
  Include {core/config_path}/httpd.conf.d/auth-projects.inc
</Directory>
