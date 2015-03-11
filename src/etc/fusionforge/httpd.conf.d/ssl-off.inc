<IfModule apache_ssl.c>
  SSLDisable
</IfModule>

# Uncomment the following lines to redirect HTTP to HTTPS globally:

#RewriteEngine On
#RewriteRule ^(.*)$ https://%{SERVER_NAME}%{REQUEST_URI} [R=301,L]
