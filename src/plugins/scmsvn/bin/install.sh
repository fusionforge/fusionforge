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

	# Enable required modules
	if [ -x /usr/sbin/a2enmod ]; then
	    a2enmod dav_svn
	fi
	# else: Apache modules already enabled in CentOS

	# Work-around memory leak in mod_dav_svn
	for conf in /etc/apache2/apache2.conf /etc/httpd/conf/httpd.conf \
	    /etc/apache2/server-tuning.conf; do
	    if [ -e $conf ] && type augtool >/dev/null 2>&1; then
		val=$(augtool "print /files$conf/IfModule[arg='mpm_worker_module' or arg='worker.c']/directive[.='MaxRequestsPerChild']/arg" | sed 's/^.*= "\(.*\)"/\1/')
		if [ "$val" = "0" ]; then
		    augtool --autosave "set /files$conf/IfModule[arg='mpm_worker_module' or arg='worker.c']/directive[.='MaxRequestsPerChild']/arg 5000" \
			|| true  # v0.10 always returns 1, v1.0 always returns 0..
		fi
	    fi
	done
	;;

    remove)
	if [ -x /usr/sbin/update-inetd ]; then
	    update-inetd --remove svn || true
	fi
	;;

    *)
	echo "Usage: $0 {configure|remove}"
	exit 1
esac
