# Custom logging

LogFormat "%h %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" gforge
CustomLog "|{cronolog_path} {var_log_gforge}/%Y/%m/%d/gforge.log" gforge
LogFormat "%{Host}i %h %l %u %t %{SSL_PROTOCOL}x:%{SSL_CIPHER}x \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combinedvhssl
CustomLog "|{cronolog_path} {var_log_gforge}/%Y/%m/%d/awstats.log" combinedvhssl
