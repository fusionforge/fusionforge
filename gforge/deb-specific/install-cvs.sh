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
	echo "Modifying inetd for cvs server"
	echo "CVS usual config is changed for sourceforge one"
        # First, dedupe the commented lines
	update-inetd --remove  "cvspserver	stream	tcp	nowait.400	root	/usr/sbin/tcpd	/usr/lib/sourceforge/bin/cvs-pserver"
	update-inetd --comment-chars "#SF_WAS_HERE#" --enable cvspserver
        # Then, insinuate ourselves
	update-inetd --comment-chars "#SF_WAS_HERE#" --disable cvspserver
	update-inetd --add  "cvspserver	stream	tcp	nowait.400	root	/usr/sbin/tcpd	/usr/lib/sourceforge/bin/cvs-pserver"
	;;

    purge)
	echo "Purging inetd for cvs server"
	# echo "You should dpkg-reconfigure cvs to use std install"
	update-inetd --remove  "cvspserver	stream	tcp	nowait.400	root	/usr/sbin/tcpd	/usr/lib/sourceforge/bin/cvs-pserver"
	update-inetd --comment-chars "#SF_WAS_HERE#" --enable cvspserver
	;;

    *)
	echo "Usage: $0 {configure|purge}"
	exit 1
esac
