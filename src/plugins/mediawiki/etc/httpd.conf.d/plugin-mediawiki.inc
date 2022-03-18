RedirectMatch 301 ^(${FF__core__url_prefix}plugins/mediawiki/wiki/[-a-zA-Z0-9_]*)/*$ $1/index.php
AliasMatch ^${FF__core__url_prefix}plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/api.php ${FF__core__source_path}/www/plugins/mediawiki/api.php
AliasMatch ^${FF__core__url_prefix}plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/index.php ${FF__core__source_path}/www/plugins/mediawiki/index.php
AliasMatch ^${FF__core__url_prefix}plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/load.php ${FF__core__source_path}/www/plugins/mediawiki/load.php
AliasMatch ^${FF__core__url_prefix}plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/img_auth.php ${FF__core__source_path}/www/plugins/mediawiki/img_auth.php
AliasMatch ^${FF__core__url_prefix}plugins/mediawiki/wiki/([-a-zA-Z0-9_]*)/images/(.*) ${FF__core__data_path}/plugins/mediawiki/projects/$1/images/$2
AliasMatch ^${FF__core__url_prefix}plugins/mediawiki/wiki/([-a-zA-Z0-9_]*)/skins/(.*) ${FF__mediawiki__src_path}/skins/$2
AliasMatch ^${FF__core__url_prefix}plugins/mediawiki/wiki/([-a-zA-Z0-9_]*)/extensions/(.*) ${FF__mediawiki__src_path}/extensions/$2
AliasMatch ^${FF__core__url_prefix}plugins/mediawiki/wiki/([-a-zA-Z0-9_]*)/resources/assets/(.*) ${FF__mediawiki__src_path}/resources/assets/$2
AliasMatch ^${FF__core__url_prefix}plugins/mediawiki/dumps/(.*)$ ${FF__core__data_path}/plugins/mediawiki/dumps/$1

<Directory ${FF__core__data_path}/plugins/mediawiki/dumps>
  Options -FollowSymLinks +Indexes
  AllowOverride None
  Require all granted
</Directory>

<Directory ${FF__core__data_path}/plugins/mediawiki/projects>
  Require all granted
</Directory>

<LocationMatch "${FF__core__url_prefix}plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/skins/(.*)">
  Require all granted
</LocationMatch>

<LocationMatch "${FF__core__url_prefix}plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/resources/assets/(.*)">
  Require all granted
</LocationMatch>

SetEnv MW_INSTALL_PATH ${FF__core__source_path}/www/plugins/mediawiki
