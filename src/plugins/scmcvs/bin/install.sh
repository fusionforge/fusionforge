#! /bin/sh
# 
# Configure CVS for Sourceforge
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)

PATH=/usr/share/gforge/bin:/usr/share/fusionforge/bin:$PATH
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
	echo "Modifying inetd for cvs server"
	echo "CVS usual config is changed for gforge one"
        # First, dedupe the commented lines
	update-inetd --remove  "cvspserver	stream	tcp	nowait.400	root	/usr/sbin/tcpd	$source_path/bin/cvs-pserver"
	update-inetd --remove  "cvspserver	stream	tcp	nowait.400	root	/usr/sbin/tcpd	$source_path/plugins/scmcvs/bin/cvs-pserver"
	update-inetd --remove  "cvspserver	stream	tcp	nowait.400	root	/usr/sbin/tcpd	$source_path/plugins/scmcvs/bin/cvs-pserver"
	update-inetd --comment-chars "#SF_WAS_HERE#" --enable cvspserver
        # Then, insinuate ourselves
	update-inetd --comment-chars "#SF_WAS_HERE#" --disable cvspserver
	update-inetd --add  "cvspserver	stream	tcp	nowait.400	root	/usr/sbin/tcpd	$source_path/plugins/scmcvs/sbin/cvs-pserver"

	# Making user group and cvs update from database 
	$source_path/plugins/scmcvs/bin/update-user-group-ssh.sh > /dev/null 2>&1
	rm -f $data_path/dumps/*cvs*dump

	if [ ! -e $data_path/chroot/cvs ] ; then
	    cd $data_path/chroot
	    ln -s cvsroot cvs
	fi

	# logs
	chown root:gforge $log_path/cvs
	chmod 775 $log_path/cvs

	# Restart some services
	[ -d /etc/ssh ] && invoke-rc.d ssh restart || true
	[ -d /etc/ssh-nonfree ] && invoke-rc.d ssh-nonfree restart || true
	;;

    purge)
	echo "Purging inetd for cvs server"
	# echo "You should dpkg-reconfigure cvs to use std install"
	update-inetd --remove  "cvspserver	stream	tcp	nowait.400	root	/usr/sbin/tcpd	$source_path/plugins/scmcvs/sbin/cvs-pserver"
	update-inetd --comment-chars "#SF_WAS_HERE#" --enable cvspserver
	;;

    *)
	echo "Usage: $0 {configure|purge}"
	exit 1
esac
