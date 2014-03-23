ServerName {core/lists_host}

SetEnv FORGE_CONFIG_PATH "{core/config_path}"

<IfVersion >= 2.3>
  IncludeOptional {core/config_path}/httpd.conf.d/lists-vhost-plugin-*.inc
</IfVersion>
<IfVersion < 2.3>
  Include {core/config_path}/httpd.conf.d/lists-vhost-plugin-*.inc
</IfVersion>

RewriteEngine on
RewriteRule ^/$ /cgi-bin/mailman/listinfo [R=301]
