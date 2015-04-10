ScriptAliasMatch ^/plugins/([^/]*)/cgi-bin/(.*) ${FF__core__plugins_path}/$1/cgi-bin/$2
<DirectoryMatch ${FF__core__plugins_path}/[^/]*/cgi-bin/>
  Include ${FF__core__config_path}/httpd.conf.d/auth-main.inc
</DirectoryMatch>
