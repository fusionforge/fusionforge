#! /bin/sh
# 
# $Id$
#
# Generate CVS repositories tarballs
# GForge
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)

set -e

if [  $(id -u) != 0 ] ; then
    echo "This script must be run as root."
    exit 1
fi
if test $# -lt 3; then 
	echo "usage:"
	echo "  tarballs.sh [action] [cvsroot] [cvstardir]"
	echo ""
	exit 1 
fi

CVSROOT=$2
CVSTARDIR=$3

case "$1" in
    generate)
	cd $CVSROOT
	ls | while read dir ; do
	    tar czf $CVSTARDIR/${dir}-cvsroot.tar.gz.new ${dir}
	    mv $CVSTARDIR/${dir}-cvsroot.tar.gz.new $CVSTARDIR/${dir}-cvsroot.tar.gz
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