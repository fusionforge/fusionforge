#!/bin/sh
if [ $# != 1 ]
then
	$0 default
else
	target=$1
	if [ $(id -u) != 0 ]
	then
		echo "You must be root to run this, please enter passwd"
		su -c "$0 $target"
	else
		case "$target" in
			default)
				[ -h /cvsroot ] || ln -s /var/lib/sourceforge/chroot/cvsroot /cvsroot
				$0 purge
				;;
			chroot)
				/etc/init.d/ssh stop
				[ -f /var/lib/sourceforge/chroot/var/run/ssh.pid ] && kill $(cat /var/lib/sourceforge/chroot/var/run/ssh.pid)
				rm -f /var/lib/sourceforge/chroot/var/run/ssh.pid
				if ! grep -q "start-stop-daemon --chroot /var/lib/sourceforge/chroot" /etc/init.d/ssh ; then
				perl -pi -e "s:start-stop-daemon:start-stop-daemon --chroot /var/lib/sourceforge/chroot:g" /etc/init.d/ssh
fi
				rm -f /etc/ssh/sshd_not_to_be_run
				;;
			purge)
        			if grep -q "start-stop-daemon --chroot /var/lib/sourceforge/chroot" /etc/init.d/ssh
       				 then
               				 perl -pi -e "s:start-stop-daemon --chroot /var/lib/sourceforge/chroot:start-stop-daemon:g" /etc/init.d/ssh
					/etc/init.d/ssh restart
        			fi
				;;
		esac
	fi
fi
