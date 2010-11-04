RedirectMatch 301 ^(/plugins/mediawiki/wiki/[-a-zA-Z0-9_]*)/*$ $1/index.php
AliasMatch ^/plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/api.php /usr/share/gforge/www/plugins/mediawiki/api.php
AliasMatch ^/plugins/mediawiki/wiki/[-a-zA-Z0-9_]*/index.php /usr/share/gforge/www/plugins/mediawiki/index.php
AliasMatch ^/plugins/mediawiki/wiki/([-a-zA-Z0-9_]*)/images/(.*) /var/lib/gforge/plugins/mediawiki/wikidata/$1/images/$2
AliasMatch ^/plugins/mediawiki/wiki/([-a-zA-Z0-9_]*)/skins/(.*) /usr/share/mediawiki/skins/$2
AliasMatch ^/plugins/mediawiki/wiki/([-a-zA-Z0-9_]*)/extensions/(.*) /usr/share/mediawiki/extensions/$2
