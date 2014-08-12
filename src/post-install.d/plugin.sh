#!/bin/bash -e
source_path=$(forge_get_config source_path)
config_path=$(forge_get_config config_path)
# No way to detect Apache's distro integration, so let's hard-code:
httpd_service=$(if [ -e /etc/redhat-release ]; then echo 'httpd'; else echo 'apache2'; fi)

if [ -z "$1" ]; then
    print "Usage: $0 plugin_name"
    exit 1
fi

# Run plugin-specific DB install/upgrade
# TODO: don't automatically enable the plugin, esp. for non-packaged installs
$source_path/bin/upgrade-db.php $1

# Restart apache if there is some change in config
# TODO: manage this with manage-apache-config.sh
if [ -f $config_path/httpd.conf.d/plugin-$1.inc ]; then
    service $httpd_service reload
fi

# Run plugin-specific install
if [ -x $source_path/plugins/$1/bin/install.sh ]; then
    echo "Running $source_path/plugins/$1/bin/install.sh configure"
    $source_path/plugins/$1/bin/install.sh configure
fi
