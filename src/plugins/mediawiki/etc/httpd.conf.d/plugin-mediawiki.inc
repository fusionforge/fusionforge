RedirectMatch 301 ^(/plugins/mediawiki/wiki/[-a-zA-Z0-9_]*)/*$ $1/index.php
AliasMatch ^/plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/api.php ${FF__core__source_path}/www/plugins/mediawiki/api.php
AliasMatch ^/plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/index.php ${FF__core__source_path}/www/plugins/mediawiki/index.php
AliasMatch ^/plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/load.php ${FF__core__source_path}/www/plugins/mediawiki/load.php
AliasMatch ^/plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/img_auth.php ${FF__core__source_path}/www/plugins/mediawiki/img_auth.php
AliasMatch ^/plugins/mediawiki/wiki/([-a-zA-Z0-9_]*)/images/(.*) ${FF__core__data_path}/plugins/mediawiki/projects/$1/images/$2
AliasMatch ^/plugins/mediawiki/wiki/([-a-zA-Z0-9_]*)/skins/(.*) ${FF__mediawiki__src_path}/skins/$2
AliasMatch ^/plugins/mediawiki/wiki/([-a-zA-Z0-9_]*)/extensions/(.*) ${FF__mediawiki__src_path}/extensions/$2
AliasMatch ^/plugins/mediawiki/dumps/(.*)$ ${FF__core__data_path}/plugins/mediawiki/dumps/$1

<Directory ${FF__core__data_path}/plugins/mediawiki/dumps>
        Options -FollowSymLinks +Indexes
        AllowOverride None
        Require all granted
</Directory>

<Directory ${FF__core__data_path}/plugins/mediawiki/projects>
        Require all granted
</Directory>

<LocationMatch "${FF__core__url_prefix}plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/skins/(.*)">
  <IfVersion >= 2.3>
    Require all granted
  </IfVersion>
  <IfVersion < 2.3>
    order allow,deny
    allow from all
  </IfVersion>
</LocationMatch>

