AddHandler cgi-script .cgi

ScriptAlias /cgi-bin/mailman/ /var/lib/mailman/cgi-bin/
ScriptAlias /mailman/ /var/lib/mailman/cgi-bin/

Alias /pipermail /var/lib/mailman/archives/public
<Directory /var/lib/mailman/archives/public>
  AllowOverride Options
  Options FollowSymLinks
</Directory>

Alias /images/mailman /usr/share/images/mailman
<Location /images/mailman>
  order allow,deny
  allow from all
</Location>
