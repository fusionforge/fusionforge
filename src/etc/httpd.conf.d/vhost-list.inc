ServerName ${FF__core__lists_host}

RewriteEngine on
RewriteRule ^/$ /mailman/listinfo [R=301]

ScriptAlias /mailman/ ${FF__core__mailman_cgi_path}/
<Directory ${FF__core__mailman_cgi_path}/>
  Require all granted
</Directory>

Alias /pipermail /var/lib/mailman/archives/public
<Directory /var/lib/mailman/archives/public>
  AllowOverride Options
  Options FollowSymLinks
  Require all granted
</Directory>

Alias /images/mailman /usr/share/images/mailman
