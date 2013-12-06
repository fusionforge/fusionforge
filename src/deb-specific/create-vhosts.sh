#! /bin/sh

set -e

/usr/share/gforge/bin/prepare-vhosts-file.pl

case "$1" in
	--norestart)
		exit 0
		;;
	*)
		if [ -x /usr/sbin/apache2 ]; then
    		    /usr/sbin/invoke-rc.d --quiet apache2 reload
		fi
		;;
esac
