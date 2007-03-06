#! /bin/sh
# 
# Generate CVS repositories snapshots.
# Suppose that the repository can be checkout using '.' as module.
# Users may prevent this for their projects using the CVSROOT/modules
# file.

set -e
#set -x

if [  $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

CVSROOT=/var/lib/gforge/chroot/cvsroot
SCMSNAPSHOTSDIR=/var/lib/gforge/scmsnapshots
TMPDIR=/tmp

case "$1" in
    generate)
        # Create temporary dir
        work_dir=$TMPDIR/gforge-plugin-scmcvs.$$
        trap "rm -rf $work_dir" ERR EXIT
        today=`date +%Y-%m-%d`

	cd $CVSROOT
        ls | while read dir ; do
	    if [ "$dir" != "cvs-locks" ]; then
                # Make tgz archive
		mkdir -p $work_dir/$dir-scm-$today
		cd $work_dir/$dir-scm-$today
		if cvs -f -Q -d :local:$CVSROOT/$dir co -P .; then
		    cd $work_dir
		    tar czf $dir-scm-latest.tar.gz $dir-scm-$today
		    mv $dir-scm-latest.tar.gz $SCMSNAPSHOTSDIR
		    rm -fr $dir-scm-$today
		fi
	    fi
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
