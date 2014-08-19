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

# Setup Docman/FRS/Tracker attachments
# (not done in 'make install' because e.g. dpkg ignores existing dirs, cf. DP10.9[1])
apache_user=$(forge_get_config apache_user)
apache_group=$(forge_get_config apache_group)
chown $apache_user: $data_path/docman/
chown $apache_user: $data_path/download/
chown $apache_user: $data_path/tracker/

# Enable required modules
if [ -x /usr/sbin/a2enmod ]; then
    a2enmod php5 || true
    a2enmod ssl || true
    a2enmod env || true
    a2enmod headers || true
    a2enmod rewrite || true
    a2enmod alias || true
    a2enmod dir || true
    #a2enmod vhost_alias || true
    #a2enmod proxy || true
    #a2enmod proxy_http || true
    #a2enmod cgi || true
else
    echo "TODO: enable Apache modules"
fi

# Hard-coded detection of distro-specific Apache conf layout
apache_service=$(if [ -e /etc/redhat-release ]; then echo 'httpd'; else echo 'apache2'; fi)
service $apache_service reload >/dev/null || true
