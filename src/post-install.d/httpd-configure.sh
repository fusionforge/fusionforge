#!/bin/bash -e
# Configure Apache

source_path=$(forge_get_config source_path)
config_path=$(forge_get_config config_path)
data_path=$(forge_get_config data_path)

cd $source_path/templates/
for i in httpd.conf $(ls httpd.conf.d/*); do
    if [ ! -e $config_path/$i ]; then
	$source_path/post-install.d/httpd-expand-conf.php $i $config_path/$i
    fi
    case $i in
	*secrets*) chmod 600 $config_path/$i;;
    esac
done

if [ -x /usr/sbin/a2ensite ]; then
    ln -nfs $config_path/httpd.conf /etc/apache2/sites-available/fusionforge.conf
    a2ensite fusionforge.conf
elif [ -e /etc/redhat-release ]; then
    ln -nfs $config_path/httpd.conf /etc/httpd/conf.d/fusionforge.conf
else
    echo "Note: install $config_path/httpd.conf in your Apache configuration"
fi

# Generate SSL cert if needed
cert=$config_path/ssl-cert.pem
key=$config_path/ssl-cert.key
if [ ! -e $cert -o ! -e $key ] ; then
    openssl req -x509 -days 3650 -new -nodes -batch -text -out $cert -keyout $key
fi

# Setup Docman/FRS/Tracker attachments
# (not done in 'make install' because e.g. dpkg ignores existing dirs, cf. DP10.9[1])
apache_user=$(forge_get_config apache_user)
apache_group=$(forge_get_config apache_group)
chown $apache_user: $data_path/docman/
chown $apache_user: $data_path/download/
chown $apache_user: $data_path/forum/
chown $apache_user: $data_path/tracker/

# Plugins activation from the web UI
chown $apache_user: $source_path/www/plugins/

# Enable required modules
if [ -x /usr/sbin/a2enmod ]; then
    a2enmod php5
    a2enmod ssl
    a2enmod env
    a2enmod headers
    a2enmod rewrite
    a2enmod alias
    a2enmod dir
    a2enmod vhost_alias
    #a2enmod proxy
    #a2enmod proxy_http
    #a2enmod cgi
else
    echo "TODO: enable Apache modules"
fi

if [ -x /usr/sbin/a2dissite ]; then
    a2dissite default
fi

# Hard-coded detection of distro-specific Apache conf layout
apache_service=$(forge_get_config apache_service)
if service $apache_service status; then
    service $apache_service reload
fi
