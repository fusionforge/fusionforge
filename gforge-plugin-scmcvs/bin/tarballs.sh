#! /bin/sh
# 
# $Id$
#
# Generate SCM repositories tarballs
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)
set -e
# Default values

SCMROOT=/var/lib/gforge/chroot/cvsroot
SCMTARDIR=/var/lib/gforge/scmtarballs
SCMNAME=scmroot

usage(){
	echo "usage:"
	echo "  tarballs.sh [action] [cvsroot] [cvstardir]"
	echo ""
	exit 1 
}

if [  $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1 $2 $3"
fi
if test $# -lt 3; then 
	if test $# -lt 1; then
		usage
	else
		exec su -c "$0 $1 $SCMROOT $SCMTARDIR"
	fi
fi

case "$1" in
    generate)
	cd $SCMROOT
	ls | while read dir ; do
	    tar czf $SCMTARDIR/${dir}-${SCMNAME}.tar.gz.new ${dir}
	    mv $SCMTARDIR/${dir}-${SCMNAME}.tar.gz.new $SCMTARDIR/${dir}-${SCMNAME}.tar.gz
	done
	;;
    
    update)
	;;

    purge)
	;;

    *)
    	usage
	;;
esac
