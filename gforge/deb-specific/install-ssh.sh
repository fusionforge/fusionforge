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
				[ -f /etc/init.d/ssh ] && /etc/init.d/ssh stop
				[ -f /etc/init.d/ssh-nonfree ] && /etc/init.d/ssh-nonfree stop
				[ -f /var/lib/sourceforge/chroot/var/run/ssh.pid ] && kill $(cat /var/lib/sourceforge/chroot/var/run/ssh.pid)
				[ -f /var/lib/sourceforge/chroot/var/run/ssh-nonfree.pid ] && kill $(cat /var/lib/sourceforge/chroot/var/run/ssh-nonfree.pid)
				rm -f /var/lib/sourceforge/chroot/var/run/ssh.pid
				rm -f /var/lib/sourceforge/chroot/var/run/ssh-nonfree.pid
			        do_config=$(grep ^do_config= /etc/sourceforge/sourceforge.conf | cut -d= -f2-)
    			        if [ ! "$do_config" = "true" ] ; then
					if [ -f /etc/init.d/ssh ] ; then
				    		if ! grep -q "start-stop-daemon --chroot /var/lib/sourceforge/chroot" /etc/init.d/ssh ; then
							perl -pi -e "s:start-stop-daemon:start-stop-daemon --chroot /var/lib/sourceforge/chroot:g" /etc/init.d/ssh
				    		fi
				    		rm -f /etc/ssh/sshd_not_to_be_run
					fi
					if [ -f /etc/init.d/ssh-nonfree ] ; then
				    		if ! grep -q "start-stop-daemon --chroot /var/lib/sourceforge/chroot" /etc/init.d/ssh-nonfree ; then
							perl -pi -e "s:start-stop-daemon:start-stop-daemon --chroot /var/lib/sourceforge/chroot:g" /etc/init.d/ssh-nonfree
				    		fi
				    		rm -f /etc/ssh/sshd-nonfree_not_to_be_run
					fi
				fi
				[ -f /etc/init.d/ssh ] && /etc/init.d/ssh start
				[ -f /etc/init.d/ssh-nonfree ] && /etc/init.d/ssh-nonfree start
				;;
			purge)
			        do_config=$(grep ^do_config= /etc/sourceforge/sourceforge.conf | cut -d= -f2-)
    			        if [ ! "$do_config" = "true" ] ; then
					if [ -f /etc/init.d/ssh ] ; then
				    		if grep -q "start-stop-daemon --chroot /var/lib/sourceforge/chroot" /etc/init.d/ssh
						then
							perl -pi -e "s:start-stop-daemon --chroot /var/lib/sourceforge/chroot:start-stop-daemon:g" /etc/init.d/ssh
							/etc/init.d/ssh restart
				    		fi
					fi
					if [ -f /etc/init.d/ssh-nonfree ] ; then
				    		if grep -q "start-stop-daemon --chroot /var/lib/sourceforge/chroot" /etc/init.d/ssh-nonfree
						then
							perl -pi -e "s:start-stop-daemon --chroot /var/lib/sourceforge/chroot:start-stop-daemon:g" /etc/init.d/ssh-nonfree
							/etc/init.d/ssh-nonfree restart
				    		fi
					fi
				fi
				;;
		esac
	fi
fi
