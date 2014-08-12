#!/bin/bash -e
# Configure Apache

source_path=$(forge_get_config source_path)
config_path=$(forge_get_config config_path)
data_path=$(forge_get_config data_path)

cd $source_path/templates/
for i in httpd.conf httpd.conf.d/*; do
    if ! [ -e $config_path/$i ]; then
	$source_path/post-install.d/httpd-expand-conf.php $i $config_path/$i
    fi
    case $i in
	*secrets*) chmod 600 $i;;
    esac
done
# Ensure vhosts file exists - cf. 40-vhosts-extra.conf
mkdir -p -m 755 $data_path/etc/
touch $data_path/etc/httpd.vhosts

# Hard-coded detection of distro-specific Apache conf layout
httpd_service=$(if [ -e /etc/redhat-release ]; then echo 'httpd'; else echo 'apache2'; fi)
if [ -e /etc/debian_version ]; then
    ln -nfs $config_path/httpd.conf /etc/apache2/sites-available/fusionforge.conf
    a2ensite fusionforge.conf
fi
if [ -e /etc/redhat-release ]; then
    ln -nfs $config_path/httpd.conf /etc/httpd/conf.d/fusionforge.conf
fi
service $httpd_service restart
