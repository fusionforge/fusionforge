#! /bin/sh
# Configure Subversion

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this"
fi

case "$1" in
    configure)
	scmsvn_repos_path=$(forge_get_config repos_path scmsvn)
	scmsvn_serve_root=$(forge_get_config serve_root scmsvn)

	echo "Modifying (x)inetd for Subversion server"
	if [ -d /etc/xinetd.d/ ]; then
	    if [ ! -e /etc/xinetd.d/fusionforge-plugin-scmsvn ]; then
		cat > /etc/xinetd.d/fusionforge-plugin-scmsvn <<-EOF
		service svn
		{
			port			= 3690
			socket_type		= stream
			protocol		= tcp
			wait			= no
			user			= nobody
			server			= /usr/bin/svnserve
			server_args		= -i -r $scmsvn_serve_root
		}
		EOF
	    fi
	    service xinetd restart || true
	elif [ -x /usr/sbin/update-inetd ]; then
	    update-inetd --remove svn || true
            update-inetd --add  "svn stream tcp nowait.400 scm-gforge /usr/bin/svnserve svnserve -i -r $scmsvn_serve_root"
	fi

	# rsync access
	if ! grep -q '^use chroot' /etc/rsyncd.conf 2>/dev/null; then
	    touch /etc/rsyncd.conf
	    echo 'use chroot=no' | sed -i -e '1ecat' /etc/rsyncd.conf
	fi
	sed -i -e 's/^use chroot.*/use chroot=no/' /etc/rsyncd.conf
	if ! grep -q '\[svn\]' /etc/rsyncd.conf; then
	    cat <<-EOF >> /etc/rsyncd.conf
		[svn]
		comment=SVN source repositories
		path=$scmsvn_repos_path
		EOF
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
	rm -f /etc/xinetd.d/fusionforge-plugin-scmsvn
	if [ -x /usr/sbin/update-inetd ]; then
	    update-inetd --remove svn || true
	fi
	;;

    *)
	echo "Usage: $0 {configure|remove}"
	exit 1
esac
