# Custom logging

LogFormat "%h %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" fusionforge
CustomLog {core/log_path}/access.log fusionforge
<IfModule mod_ssl.c>
LogFormat "%{Host}i %h %l %u %t %{SSL_PROTOCOL}x:%{SSL_CIPHER}x \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combinedvhssl
</IfModule>
CustomLog {core/log_path}/awstats.log combinedvhssl
