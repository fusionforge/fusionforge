#! /bin/sh
# 
# Configure CVS for FusionForge
# Copyright 2014 Sylvain Beucler
# Copyright 2014, 2016 Roland Mas

PATH=$(forge_get_config binary_path):$PATH
source_path=`forge_get_config source_path`
log_path=`forge_get_config log_path`
data_path=`forge_get_config data_path`

set -e

if [ `id -u` != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

case "$1" in
    configure)
	scmcvs_repos_path=$(forge_get_config repos_path scmcvs)
	source_path=$(forge_get_config source_path)

	echo "Modifying xinetd for CVS server"
	if [ ! -e /etc/xinetd.d/fusionforge-plugin-scmcvs ]; then
	    cat > /etc/xinetd.d/fusionforge-plugin-scmcvs <<-EOF
		service cvspserver
		{
		    port            = 2401
		    socket_type     = stream
		    wait            = no
		    user            = root
		    server          = $source_path/plugins/scmcvs/sbin/cvs-pserver
		}
		EOF
	fi
	service xinetd restart

	rm -f $data_path/dumps/*cvs*dump

	if [ -e $data_path/chroot/cvsroot ] && [ ! -e $data_path/chroot/cvs ] ; then
	    cd $data_path/chroot
	    ln -s cvsroot cvs
	fi

	# Restart some services
	[ -d /etc/ssh ] && service $(forge_get_config ssh_service) restart || true
	;;

    remove)
	rm -f /etc/xinetd.d/fusionforge-plugin-scmcvs
	;;

    *)
	echo "Usage: $0 {configure|remove}"
	exit 1
esac
