#! /bin/sh
# 
# $Id$
#
# Generate CVS repositories tarballs
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)

set -e
#set -x 

if [  $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

CVSROOT=/var/lib/gforge/chroot/cvsroot
SCMTARDIR=/var/lib/gforge/scmtarballs

case "$1" in
    generate)
	cd $CVSROOT
	ls | while read dir ; do
	    tar czf $SCMTARDIR/${dir}-scmroot.tar.gz.new ${dir}
	    mv $SCMTARDIR/${dir}-scmroot.tar.gz.new $SCMTARDIR/${dir}-scmroot.tar.gz
	done
	;;
    
    update)
	;;

    purge)
	;;

    *)
	echo "Usage: $0 {generate}"
	exit 1
	;;
esac
