#!/bin/sh
if [ $# != 1 ] 
then 
	$0 /var/lib/sourceforge/chroot
else
	target=$1
	if [ $(id -u) != 0 ]
	then
	        echo "You must be root to run this, please enter passwd"
	        su -c "$0 $target"
	else
		echo "Installing chroot environnement at $target"
		[ ! -d $target ] && install -d -m 755 $target
		for dir in \
			bin \
			usr \
			usr/bin \
			usr/lib \
			etc \
			etc/pam.d \
			etc/security \
			lib \
			lib/security \
			dev \
			var \
			var/run \
			cvsroot \
			home \
			home/users \
			home/groups
		do
			[ ! -d $target/$dir ] && \
			mkdir $target/$dir
		done
		install -d -m 1777 $target/tmp

		# Copy needed binaries
		# For testing /bin/ls /bin/su
		# Maybe needed /bin/chgrp
		# Could be restricted /bin/bash
		# TODO: remove unneeded stuff from that list
		for binary in \
			/usr/sbin/sshd \
			/usr/bin/cvs \
			/bin/ls \
			/bin/bash \
			/bin/chgrp 
		do
			(echo "$binary"; ldd $binary | cut -d" " -f3) 
		done | sort -u | cpio --quiet -pdumVLB $target

		# Create devices files
		if [ ! -c $target/dev/null ]
		then
			mknod $target/dev/null c 1 3
		fi
		if [ ! -c $target/dev/urandom ]
		then
			mknod $target/dev/urandom c 1 9
		fi
		if [ ! -c $target/dev/console ]
		then
			mknod $target/dev/console c 5 1
		fi
		# To get uid/gid
		# Maybe ldap later
		cat > $target/etc/nsswitch.conf <<-FIN
passwd:         files
group:          files
shadow:         files
FIN
		# Copy miscellaneous files
		find /etc/ssh | cpio --quiet -pdumLB $target
		cp /etc/pam.d/ssh $target/etc/pam.d
		cp /etc/pam.d/login $target/etc/pam.d
		cp /etc/pam.d/su $target/etc/pam.d
		cp /lib/libpam* $target/lib
		
		cp /lib/libnss_files* $target/lib
		cp /lib/security/* $target/lib/security
		cp /etc/security/*.conf $target/etc/security
	fi
fi
