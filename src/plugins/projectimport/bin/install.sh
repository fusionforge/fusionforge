#! /bin/sh
# 
# Configure projectimport plugin

# invoked by plugin.postinst

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

case "$1" in
    configure)
	chown www-data:www-data /var/lib/gforge/plugins/projectimport
	;;

    *)
	echo "Usage: $0 {configure}"
	exit 1
esac
