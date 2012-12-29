#! /bin/sh
#
# Configure Subversion for Sourceforge
# Roland Mas, Gforge



set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

gforge_chroot=$(
	PATH=/usr/share/gforge/bin:/usr/share/fusionforge/bin:$PATH
	forge_get_config chroot
    )

case "$1" in
    configure)
        echo "Modifying inetd for Subversion server"
        # First, dedupe the commented lines
        update-inetd --remove  "svnserve stream tcp nowait.400 gforge_scm /usr/bin/svnserve svnserve -i -r $gforge_chroot" || true
        update-inetd --remove  "svn stream tcp nowait.400 scm-gforge /usr/bin/svnserve svnserve -i -r $gforge_chroot" || true
        update-inetd --remove  "svn stream tcp nowait.400 scm-gforge /usr/bin/svnserve svnserve -i -r "
        update-inetd --add  "svn stream tcp nowait.400 scm-gforge /usr/bin/svnserve svnserve -i -r $gforge_chroot"
	;;

    purge)
        update-inetd --remove  "svnserve stream tcp nowait.400 gforge_scm /usr/bin/svnserve svnserve -i -r $gforge_chroot"
        update-inetd --remove  "svn stream tcp nowait.400 scm-gforge /usr/bin/svnserve svnserve -i -r $gforge_chroot"
        update-inetd --remove  "svn stream tcp nowait.400 scm-gforge /usr/bin/svnserve svnserve -i -r "
	;;

    *)
	echo "Usage: $0 {configure|purge}"
	exit 1
esac
