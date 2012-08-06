#! /bin/sh
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
	[ -h /cvsroot ] || ln -s /var/lib/gforge/chroot/cvsroot /cvsroot
	$0 purge
	;;

    chroot)
    	if [ -d /etc/ssh ]
	then
		invoke-rc.d ssh stop
		[ -f /var/lib/gforge/chroot/var/run/ssh.pid ] && kill $(cat /var/lib/gforge/chroot/var/run/ssh.pid)
		rm -f /var/lib/gforge/chroot/var/run/ssh.pid
		if ! grep -q "start-stop-daemon --chroot /var/lib/gforge/chroot" /etc/init.d/ssh ; then
	    	perl -pi -e "s:start-stop-daemon:start-stop-daemon --chroot /var/lib/gforge/chroot:g" /etc/init.d/ssh
		fi
		rm -f /etc/ssh/sshd_not_to_be_run
	fi
    	if [ -d /etc/ssh-nonfree ]
	then
		invoke-rc.d ssh-nonfree stop
		[ -f /var/lib/gforge/chroot/var/run/ssh-nonfree.pid ] && kill $(cat /var/lib/gforge/chroot/var/run/ssh-nonfree.pid)
		rm -f /var/lib/gforge/chroot/var/run/ssh-nonfree.pid
		if ! grep -q "start-stop-daemon --chroot /var/lib/gforge/chroot" /etc/init.d/ssh-nonfree ; then
	    	perl -pi -e "s:start-stop-daemon:start-stop-daemon --chroot /var/lib/gforge/chroot:g" /etc/init.d/ssh-nonfree
		fi
		rm -f /etc/ssh-nonfree/sshd_not_to_be_run
	fi
	;;

    purge)
	if grep -q "start-stop-daemon --chroot /var/lib/gforge/chroot" /etc/init.d/ssh ; then
	    perl -pi -e "s:start-stop-daemon --chroot /var/lib/gforge/chroot:start-stop-daemon:g" /etc/init.d/ssh
	    invoke-rc.d ssh restart
	fi
	;;

    *)
	echo "Usage: $0 {configure|chroot|purge}"
	exit 1
	;;
	
esac
