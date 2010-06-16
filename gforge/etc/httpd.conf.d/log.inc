# Custom logging

LogFormat "%h %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" fusionforge
CustomLog {core/log_path}/access.log fusionforge
LogFormat "%{Host}i %h %l %u %t %{SSL_PROTOCOL}x:%{SSL_CIPHER}x \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combinedvhssl
CustomLog {core/log_path}/awstats.log combinedvhssl
