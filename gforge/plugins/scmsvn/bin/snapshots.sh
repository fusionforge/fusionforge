#!/bin/sh
#
# Generate SVN repositories snapshots.

set -e
#set -x

if [  $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

SVNROOT=/var/lib/gforge/chroot/svnroot
SCMSNAPSHOTSDIR=/var/lib/gforge/scmsnapshots
TMPDIR=/tmp

case "$1" in
    generate)
	# Create temporary dir
	work_dir=$TMPDIR/gforge-plugin-scnsvn.$$
	trap "rm -rf $work_dir" ERR EXIT

        today=`date +%Y-%m-%d`
	cd $SVNROOT
        ls | while read dir ; do
            # Make tgz archive
	    mkdir -p $work_dir/$dir-scm-$today
	    cd $work_dir
	    svn checkout -q file://$SVNROOT/$dir $dir-scm-$today
            tar czf $dir-scm-latest.tar.gz $dir-scm-$today
            mv $dir-scm-latest.tar.gz $SCMSNAPSHOTSDIR
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
