ServerName ${FF__core__lists_host}
ServerPath ${FF__core__url_prefix}

RewriteEngine on
RewriteRule ^/$ ${FF__core__url_prefix}mailman/listinfo [R=301]

ScriptAlias ${FF__core__url_prefix}mailman/ ${FF__core__mailman_cgi_path}/
<Directory ${FF__core__mailman_cgi_path}/>
  Require all granted
</Directory>

Alias ${FF__core__url_prefix}pipermail /var/lib/mailman/archives/public
<Directory /var/lib/mailman/archives/public>
  AllowOverride Options
  Options FollowSymLinks
  Require all granted
</Directory>

Alias ${FF__core__url_prefix}images/mailman /usr/share/images/mailman
