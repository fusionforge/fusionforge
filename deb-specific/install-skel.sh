#! /bin/sh
# 
# [Blah blah blah, here should be a description of what this script does]
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)

set -e

# if [ $(id -u) != 0 ] ; then
#     echo "You must be root to run this, please enter passwd"
#     exec su -c "$0 $1"
# fi

case "$1" in
    configure-files)
	cp -a /etc/file /etc/file.gforge-new
	# Patch /etc/file.gforge-new
	;;

    configure)

	# invoke-rc.d <service> <action>
	;;

    purge-files)
	cp -a /etc/file /etc/file.gforge-new
	# Patch /etc/file.gforge-new
	;;

    purge)
	;;

    *)
	echo "Usage: $0 {configure|configure-files|purge|purge-files}"
	exit 1
esac
