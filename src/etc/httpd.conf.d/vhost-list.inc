ServerName ${FF__core__lists_host}

SetEnv FF__core__config_path "${FF__core__config_path}"

IncludeOptional ${FF__core__config_path}/httpd.conf.d/lists-vhost-plugin-*.inc

RewriteEngine on
RewriteRule ^/$ /cgi-bin/mailman/listinfo [R=301]
