#! /bin/sh
# 
# $Id$
#
# Configure SSH for Sourceforge
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

case "$1" in
    configure)
	[ -h /cvsroot ] || ln -s /var/lib/sourceforge/chroot/cvsroot /cvsroot
	$0 purge
	;;

    chroot)
	invoke-rc.d ssh stop
	[ -f /var/lib/sourceforge/chroot/var/run/ssh.pid ] && kill $(cat /var/lib/sourceforge/chroot/var/run/ssh.pid)
	rm -f /var/lib/sourceforge/chroot/var/run/ssh.pid
	if ! grep -q "start-stop-daemon --chroot /var/lib/sourceforge/chroot" /etc/init.d/ssh ; then
	    perl -pi -e "s:start-stop-daemon:start-stop-daemon --chroot /var/lib/sourceforge/chroot:g" /etc/init.d/ssh
	fi
	rm -f /etc/ssh/sshd_not_to_be_run
	;;

    purge)
	if grep -q "start-stop-daemon --chroot /var/lib/sourceforge/chroot" /etc/init.d/ssh ; then
	    perl -pi -e "s:start-stop-daemon --chroot /var/lib/sourceforge/chroot:start-stop-daemon:g" /etc/init.d/ssh
	    invoke-rc.d ssh restart
	fi
	;;

    *)
	echo "Usage: $0 {configure|chroot|purge}"
	exit 1
	;;
	
esac
