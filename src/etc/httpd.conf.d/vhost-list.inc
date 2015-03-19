ServerName ${FF__core__lists_host}

SetEnv FF__core__config_path "${FF__core__config_path}"

<IfVersion >= 2.3>
  IncludeOptional ${FF__core__config_path}/httpd.conf.d/lists-vhost-plugin-*.inc
</IfVersion>
<IfVersion < 2.3>
  Include ${FF__core__config_path}/httpd.conf.d/lists-vhost-plugin-*.inc
</IfVersion>

RewriteEngine on
RewriteRule ^/$ /cgi-bin/mailman/listinfo [R=301]
