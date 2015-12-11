#!/bin/bash
# Configure Subversion

set -e

source $(forge_get_config source_path)/post-install.d/common/service.inc

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this"
fi

case "$1" in
    configure)
	scmsvn_repos_path=$(forge_get_config repos_path scmsvn)
	scmsvn_serve_root=$(forge_get_config serve_root scmsvn)

	echo "Modifying xinetd for Subversion server"
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
	service xinetd restart

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
