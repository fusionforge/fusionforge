ServerName ${FF__core__lists_host}

SetEnv FF__core__config_path "${FF__core__config_path}"

IncludeOptional ${FF__core__config_path}/httpd.conf.d/lists-vhost-plugin-*.inc

RewriteEngine on
RewriteRule ^/$ /mailman/listinfo [R=301]

ScriptAlias /mailman/ /usr/lib/cgi-bin/mailman/

Alias /pipermail /var/lib/mailman/archives/public
<Directory /var/lib/mailman/archives/public>
  AllowOverride Options
  Options FollowSymLinks
  Require all granted
</Directory>

Alias /images/mailman /usr/share/images/mailman
