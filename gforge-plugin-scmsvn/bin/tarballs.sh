#! /bin/sh
# 
# Generate SVN repositories tarballs thanks to hot-backup.py
# Subversion tools - a simple tar.gz file of the SVN repository may
# lead to an unusable archive.

set -e
#set -x

if [  $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

SVNROOT=/var/lib/gforge/chroot/svnroot
SCMTARDIR=/var/lib/gforge/scmtarballs
TMPDIR=/tmp

case "$1" in
    generate)
	# create tmp dir
	work_dir=$TMPDIR/gforge-plugin-scnsvn.$$
	mkdir -p $work_dir
	trap "rm -rf $work_dir" ERR EXIT

	cd $work_dir
	ls $SVNROOT | while read dir ; do
	    /usr/lib/subversion/hot-backup.py $SVNROOT/$dir . > /dev/null
	    tar czf $dir-scmroot.tar.gz.new $dir*
	    mv -f ${dir}-scmroot.tar.gz.new $SCMTARDIR/${dir}-scmroot.tar.gz
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
