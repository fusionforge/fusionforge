<Directory ${FF__scmbzr__repos_path}>
  Include ${FF__core__config_path}/httpd.conf.d/disable-scripts.inc
</Directory>
Alias ${FF__core__url_prefix}anonscm/bzr ${FF__scmbzr__repos_path}

Alias ${FF__core__url_prefix}scm/loggerhead/static /usr/share/loggerhead/static
RewriteEngine On
RewriteRule ^${FF__core__url_prefix}scm/loggerhead$ ${FF__core__url_prefix}scm/loggerhead/ [R]

<IfModule wsgi_module>
    WSGIScriptAlias ${FF__core__url_prefix}scm/loggerhead /usr/bin/loggerhead.wsgi
    WSGIProcessGroup fusionforge-plugin-scmbzr
</IfModule>

<Location ${FF__core__url_prefix}scm/loggerhead>
  <IfVersion >= 2.3>
    Require all granted
  </IfVersion>
  <IfVersion < 2.3>
    order allow,deny
    allow from all
  </IfVersion>
</Location>
