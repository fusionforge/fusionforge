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
(
if [ ! -e $source_path/plugins/$1/etc/httpd.conf.d/ ]; then exit; fi
cd $source_path/plugins/$1/etc/
for i in $(ls httpd.conf.d/*); do
    if [ ! -e $config_path/$i ]; then
	$source_path/post-install.d/httpd-expand-conf.php $i $config_path/$i
    fi
    case $i in
	*secrets*) chmod 600 $config_path/$i;;
    esac
done
# Hard-coded detection of distro-specific Apache conf layout
httpd_service=$(if [ -e /etc/redhat-release ]; then echo 'httpd'; else echo 'apache2'; fi)
service $httpd_service reload >/dev/null || true
)


# Run plugin-specific install
if [ -x $source_path/plugins/$1/bin/install.sh ]; then
    echo "Running $source_path/plugins/$1/bin/install.sh configure"
    $source_path/plugins/$1/bin/install.sh configure
fi
