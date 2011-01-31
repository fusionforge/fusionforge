RedirectMatch 301 ^(/plugins/mediawiki/wiki/[-a-zA-Z0-9_]*)/*$ $1/index.php
AliasMatch ^/plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/api.php {core/source_path}/www/plugins/mediawiki/api.php
AliasMatch ^/plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/index.php {core/source_path}/www/plugins/mediawiki/index.php
AliasMatch ^/plugins/mediawiki/wiki/([-a-zA-Z0-9_]*)/images/(.*) {core/data_path}/plugins/mediawiki/wikidata/$1/images/$2
AliasMatch ^/plugins/mediawiki/wiki/([-a-zA-Z0-9_]*)/skins/(.*) {mediawiki/src_path}/skins/$2
AliasMatch ^/plugins/mediawiki/wiki/([-a-zA-Z0-9_]*)/extensions/(.*) {mediawiki/src_path}/extensions/$2
AliasMatch ^/plugins/mediawiki/dumps/(.*)$ {core/data_path}/plugins/mediawiki/dumps/$1

<Directory {core/data_path}/plugins/mediawiki/dumps>
        Options -FollowSymLinks +Indexes
        AllowOverride None
        order allow,deny
        allow from all
</Directory>
