#! /bin/sh
# Configure Subversion

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this"
fi

scmsvn_serve_root=$(forge_get_config serve_root scmsvn)

case "$1" in
    configure)
        echo "Modifying inetd for Subversion server"
	if [ -x /usr/sbin/update-inetd ]; then
	    update-inetd --remove svn || true
            update-inetd --add  "svn stream tcp nowait.400 scm-gforge /usr/bin/svnserve svnserve -i -r $scmsvn_serve_root"
	else
	    echo "TODO: xinetd support"
	fi
	;;

    purge)
	update-inetd --remove svn || true
	;;

    *)
	echo "Usage: $0 {configure|purge}"
	exit 1
esac
