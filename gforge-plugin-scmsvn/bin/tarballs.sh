#! /bin/sh
# 
# Generate SCM repositories tarballs thanks to hot-backup.py
# Subversion tools - a simple tar.gz file of the SCM repository may
# lead to an unusable archive.
set -e
# Default values
SCMROOT=/var/lib/gforge/chroot/svnroot
SCMTARDIR=/var/lib/gforge/scmtarballs
SCMNAME=scmroot
TMPDIR=/tmp

usage(){
	echo "usage:"
	echo "  tarballs.sh [action] [svnroot] [svntardir]"
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

if [  $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

case "$1" in
    generate)
	# create tmp dir
	work_dir=$TMPDIR/gforge-plugin-scmsvn.$$
	mkdir -p $work_dir
	trap "rm -rf $work_dir" ERR EXIT

	cd $work_dir
	ls $SCMROOT | while read dir ; do
	    /usr/lib/subversion/hot-backup.py $SCMROOT/$dir . > /dev/null
	    tar czf $dir-${SCMNAME}.tar.gz.new $dir*
	    mv -f ${dir}-${SCMNAME}.tar.gz.new $SCMTARDIR/${dir}-${SCMNAME}.tar.gz
	    rm -rf $dir*
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
