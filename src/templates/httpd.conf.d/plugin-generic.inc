ScriptAliasMatch ^/plugins/([^/]*)/cgi-bin/(.*) {core/plugins_path}/$1/cgi-bin/$2
<DirectoryMatch {core/plugins_path}/[^/]*/cgi-bin/>
  Include {core/config_path}/httpd.conf.d/auth-main.inc
</DirectoryMatch>
