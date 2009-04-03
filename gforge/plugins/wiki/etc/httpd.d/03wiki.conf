<Location /wiki/g>
	php_admin_flag register_long_arrays on 
	ForceType application/x-httpd-php
</Location>
        
<Location "/www">
        Options -ExecCGI -FollowSymLinks
        DirectoryIndex index.html index.htm
        php_admin_flag engine off
        php_admin_value open_basedir /opt/groups/
</Location>

RewriteEngine On
RewriteRule     ^/www/([^/]*)/(.*) /opt/groups/$1/www/$2 [L]
