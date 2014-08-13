Alias /plugins/oslc/ @PLUGINS_PATH@/oslc/www/
<Directory @PLUGINS_PATH@/oslc/www/>
	Options FollowSymLinks
	Order allow,deny
	allow from all
	RewriteEngine On
	RewriteBase /plugins/oslc/

	RewriteCond %{REQUEST_FILENAME} -s [OR]
	RewriteCond %{REQUEST_FILENAME} -l [OR]
	RewriteCond %{REQUEST_FILENAME} -d
	RewriteRule ^.*$ - [NC,L]
	RewriteRule ^.*$ index.php [NC,L]
	Include @CONFIG_PATH@/httpd.conf.d/secrets.inc
</Directory>
