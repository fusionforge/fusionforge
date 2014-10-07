Alias {core/url_prefix}scm/loggerhead/static /usr/share/loggerhead/static
RewriteEngine On
RewriteRule ^{core/url_prefix}scm/loggerhead$ {core/url_prefix}scm/loggerhead/ [R]

<IfModule wsgi_module>
    WSGIScriptAlias {core/url_prefix}scm/loggerhead /usr/bin/loggerhead.wsgi
    WSGIProcessGroup fusionforge-plugin-scmbzr
</IfModule>

<Location {core/url_prefix}scm/loggerhead>
  <IfVersion >= 2.3>
    Require all granted
  </IfVersion>
  <IfVersion < 2.3>
    order allow,deny
    allow from all
  </IfVersion>
</Location>
