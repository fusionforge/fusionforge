AliasMatch "^/moin_static[0-9]*/applets/FCKeditor/(.*)" "/usr/share/fckeditor/$1"
<Directory "/usr/share/fckeditor/">
  Options None
  AllowOverride None
</Directory>

# The path to static contents changes (named after moinmoin version).
AliasMatch "^/moin_static[0-9]*/(.*)" "/usr/share/moin/htdocs/$1"
<Directory "/usr/share/moin/htdocs/">
  Options -Indexes -FollowSymlinks
  AllowOverride None
</Directory>

<IfModule wsgi_module>
  WSGIProcessGroup fusionforge-plugin-moinmoin
  WSGIScriptAliasMatch /plugins/moinmoin/[-_a-zA-Z0-9]*/(.*) "${FF__core__data_path}/plugins/moinmoin/wikidata/moin.wsgi/$1"
</IfModule>

<Location ${FF__core__url_prefix}plugins/moinmoin>
  <IfVersion >= 2.3>
    Require all granted
  </IfVersion>
  <IfVersion < 2.3>
    order allow,deny
    allow from all
  </IfVersion>
</Location>

<LocationMatch "^/moin_static[0-9]*/(.*)">
  <IfVersion >= 2.3>
    Require all granted
  </IfVersion>
  <IfVersion < 2.3>
    order allow,deny
    allow from all
  </IfVersion>
</LocationMatch>
