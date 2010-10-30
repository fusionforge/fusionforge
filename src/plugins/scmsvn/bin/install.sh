#! /bin/sh
# 
# Configure Subversion for Sourceforge
# Roland Mas, Gforge



set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

gforge_chroot=$(grep ^gforge_chroot= /etc/fusionforge/fusionforge.conf | cut -d= -f2-)

case "$1" in
    configure)
        echo "Modifying inetd for Subversion server"
        # First, dedupe the commented lines
        update-inetd --remove  "svnserve stream tcp nowait.400 gforge_scm /usr/bin/svnserve svnserve -i -r $gforge_chroot" || true
        update-inetd --remove  "svn stream tcp nowait.400 scm-gforge /usr/bin/svnserve svnserve -i -r $gforge_chroot" || true
        update-inetd --add  "svn stream tcp nowait.400 scm-gforge /usr/bin/svnserve svnserve -i -r $gforge_chroot"
	;;

    purge)
        update-inetd --remove  "svnserve stream tcp nowait.400 gforge_scm /usr/bin/svnserve svnserve -i -r $gforge_chroot"
        update-inetd --remove  "svn stream tcp nowait.400 scm-gforge /usr/bin/svnserve svnserve -i -r $gforge_chroot"
	;;

    *)
	echo "Usage: $0 {configure|purge}"
	exit 1
esac
