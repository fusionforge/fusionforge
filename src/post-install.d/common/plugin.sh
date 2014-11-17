#!/bin/bash -e
#
# Copyright (C) 2014  Inria (Sylvain Beucler)
#
# This file is part of FusionForge. FusionForge is free software;
# you can redistribute it and/or modify it under the terms of the
# GNU General Public License as published by the Free Software
# Foundation; either version 2 of the Licence, or (at your option)
# any later version.
#
# FusionForge is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with FusionForge; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

source_path=$(forge_get_config source_path)
config_path=$(forge_get_config config_path)
apache_service=$(forge_get_config apache_service)

if [ ! -d $source_path/plugins/$1 ]; then
    echo "Unknown plugin '$1'"
    exit 1
fi

case "$2" in
    configure)
	$0 $1 configure-conffiles
	$0 $1 configure-exec
	;;

    configure-conffiles)
	# Restart apache if there is some change in config
	if [ ! -d $source_path/plugins/$1/etc/httpd.conf.d/ ]; then exit; fi
	
	# Distros may want to install new conffiles using tools such as ucf(1)
	DESTDIR=$3
	mkdir -m 755 -p $DESTDIR$config_path/httpd.conf.d/
	
	cd $source_path/plugins/$1/etc/
	for i in $(ls httpd.conf.d/*); do
	    if [ ! -e $DESTDIR$config_path/$i ]; then
		$source_path/post-install.d/web/expand-conf.php $i $DESTDIR$config_path/$i
	    fi
	    case $i in
		*secrets*) chmod 600 $DESTDIR$config_path/$i;;
	    esac
	done
	;;

    configure-exec)
	# Enable plugin
	$source_path/bin/forge pluginActivate $1

	# Run plugin-specific DB upgrade
	if [ -x $source_path/post-install.d/db/upgrade.php ]; then
	    $source_path/post-install.d/db/upgrade.php $1
	fi

	# Run plugin-specific install
	if [ -x $source_path/plugins/$1/bin/install.sh ]; then
	    echo "Running $source_path/plugins/$1/bin/install.sh configure"
	    $source_path/plugins/$1/bin/install.sh configure
	fi

	# Restart Apache if new conffiles were added
	if [ ! -d $source_path/plugins/$1/etc/httpd.conf.d/ ]; then
	    service $apache_service reload >/dev/null || true
	fi
	;;

    triggered)
	# Run plugin-specific triggered (e.g. mediawiki)
	if [ -x $source_path/plugins/$1/bin/install.sh ]; then
	    echo "Running $source_path/plugins/$1/bin/install.sh triggered"
	    $source_path/plugins/$1/bin/install.sh triggered "$2"
	fi
	;;

    remove)
	# Remove plugin symlink in source_path/www/plugins/
	# TODO: dependencies issues on removal
	#$source_path/bin/forge pluginDeactivate $1

	# Run plugin-specific remove
	if [ -x $source_path/plugins/$1/bin/install.sh ]; then
	    echo "Running $source_path/plugins/$1/bin/install.sh remove"
	    $source_path/plugins/$1/bin/install.sh remove
	fi
	;;

    purge)
	# note: can't be called from Debian's postrm - rely on ucfq(1)
	cd $source_path/plugins/$1/etc/
	for i in $(ls httpd.conf.d/*); do
	    rm -f $config_path/$i
	done
	;;

    *)
	echo "Usage: $0 plugin_name configure|remove"
	exit 1
	;;
esac
