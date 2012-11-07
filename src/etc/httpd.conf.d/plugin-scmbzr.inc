Alias {core/url_prefix}/scm/loggerhead/static /usr/share/loggerhead/static
RewriteEngine On
RewriteRule ^{core/url_prefix}/scm/loggerhead$ {core/url_prefix}/scm/loggerhead/ [R]

WSGIScriptAlias {core/url_prefix}/scm/loggerhead /usr/bin/loggerhead.wsgi

<Location {core/url_prefix}/scm/loggerhead>
    WSGIProcessGroup fusionforge-plugin-scmbzr
    Order deny,allow
    Allow from all
</Location>
