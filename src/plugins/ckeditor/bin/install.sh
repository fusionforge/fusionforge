#!/bin/bash -e
# ckeditor post-install

source_path=$(forge_get_config source_path)
data_path=$(forge_get_config data_path)
plugindir=$(forge_get_config source_path)/plugins/ckeditor
extraconfigdirs=$(forge_get_config extra_config_dirs)

ckeditordir=$((ls -d /usr/share/ckeditor 2>/dev/null || echo '/usr/share/javascript/ckeditor') | tail -1)
# Debian: /usr/share/javascript/ckeditor/
# CentOS6: /usr/share/ckeditor/

case "$1" in
	configure)
		# adapt the ini file
		sed -i -e "s@^src_path.*@src_path = $ckeditordir@" $extraconfigdirs/ckeditor.ini
		;;
	triggerd)
		# here just for compatibility. Nothing to do.
		;;
	remove)
		# Remove plugin symlink in source_path/www/plugins/
		;;
	*)
		echo "Usage: $0 {configure|triggered|remove}"
		exit 1
esac


