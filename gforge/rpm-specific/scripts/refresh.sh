cd /usr/share/gforge && ./setup -confdir /etc/gforge/ -input /etc/gforge/gforge.conf -noapache && cd -

echo 'If you have modified the database configuration, you have to restart apache (service httpd restart)'
