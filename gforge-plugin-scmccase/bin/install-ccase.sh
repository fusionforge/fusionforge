#! /bin/sh
# 
# $Id$
#
# Configure CVS for Sourceforge
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

case "$1" in
    configure)
	;;

    purge)
	;;

    *)
	echo "Usage: $0 {configure|purge}"
	exit 1
esac
