#! /bin/sh
# 
# $Id$
#
# Configure Subversion for Sourceforge
# Roland Mas, Gforge

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

case "$1" in
    configure)
        echo "Modifying inetd for Subversion server"
        # First, dedupe the commented lines
        update-inetd --remove  "svnserve stream tcp nowait.400 gforge_scm /usr/bin/svnserve svnserve -i -r /var/lib/gforge/chroot/svnroot" || true
        update-inetd --add  "svnserve stream tcp nowait.400 gforge_scm /usr/bin/svnserve svnserve -i -r /var/lib/gforge/chroot/svnroot"
	/usr/lib/gforge/plugins/scmsvn/bin/install-viewcvs.sh
	;;

    purge)
        update-inetd --remove  "svnserve stream tcp nowait.400 gforge_scm /usr/bin/svnserve svnserve -i -r /var/lib/gforge/chroot/svnroot"
	;;

    *)
	echo "Usage: $0 {configure|purge}"
	exit 1
esac
