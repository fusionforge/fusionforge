#! /bin/sh
# 
# $Id$
#
# [Blah blah blah, here should be a description of what this script does]
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

CHROOTDIR=/var/lib/gforge/chroot

case "$1" in
    configure)
	echo "Installing chroot environnement at $CHROOTDIR"
	[ -d $CHROOTDIR ] || install -d -m 755 $CHROOTDIR
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
	  [ -d $CHROOTDIR/$dir ] || mkdir $CHROOTDIR/$dir
	done
	install -d -m 1777 $CHROOTDIR/tmp
	
	# Copy needed binaries
	# For testing /bin/ls /bin/su
	# Maybe needed /bin/chgrp
	# Could be restricted /bin/bash
	# TODO: remove unneeded stuff from that list
	for binary in \
	    /usr/sbin/sshd \
	    /usr/bin/cvs \
	    /bin/ls \
	    /bin/sh \
	    /bin/bash \
	    /bin/chgrp ; do
	  if [ -x "$binary" ] ; then
	      echo "$binary"
	      ldd $binary | cut -d" " -f3
	  fi
	done \
	    | sort -u \
	    | cpio --quiet -pdumVLB $CHROOTDIR

	# Create devices files
	[ -c $CHROOTDIR/dev/null ] || mknod $CHROOTDIR/dev/null c 1 3
	[ -c $CHROOTDIR/dev/urandom ] || mknod $CHROOTDIR/dev/urandom c 1 9
	[ -c $CHROOTDIR/dev/console ] || mknod $CHROOTDIR/dev/console c 5 1
	# For /dev/log
	if ! grep -q "^SYSLOGD.*/var/lib/gforge/chroot/dev/log.*" /etc/init.d/sysklogd ; then 
		echo '######################################################################################################'
		echo 'WARNING: you must have SYSLOGD="-p /dev/log -p /var/lib/gforge/chroot/dev/log" in /etc/init.d/sysklogd'
		echo 'To have cvs pserver running correctly'
		echo '######################################################################################################'
	fi

	
	# To get uid/gid
	# Maybe ldap later
	cat > $CHROOTDIR/etc/nsswitch.conf <<-FIN
passwd:         files ldap 
group:          files ldap
shadow:         files ldap
FIN
	# Copy miscellaneous files
	[ -d /etc/ssh ] && find /etc/ssh | cpio --quiet -pdumLB $CHROOTDIR
	[ -d /etc/ssh-nonfree ] && find /etc/ssh-nonfree | cpio --quiet -pdumLB $CHROOTDIR
	[ -f /etc/pam.d/ssh ] && cp /etc/pam.d/ssh $CHROOTDIR/etc/pam.d
	[ -f /etc/pam.d/ssh-nonfree ] && cp /etc/pam.d/ssh-nonfree $CHROOTDIR/etc/pam.d
	[ -f /etc/pam.d/login ] && cp /etc/pam.d/login $CHROOTDIR/etc/pam.d
	[ -f /etc/pam.d/su ] && cp /etc/pam.d/su $CHROOTDIR/etc/pam.d
	[ -f /etc/pam.d/cvs ] && cp /etc/pam.d/cvs $CHROOTDIR/etc/pam.d
	[ -f /etc/pam.d/other ] && cp /etc/pam.d/other $CHROOTDIR/etc/pam.d
	cp /lib/libpam* $CHROOTDIR/lib
	
	cp /lib/libnss_files* $CHROOTDIR/lib
	cp /lib/security/* $CHROOTDIR/lib/security
	cp /etc/security/*.conf $CHROOTDIR/etc/security

	# Libnss-ldap related stuffs
	for binary in \
	    /usr/bin/ldapsearch ; do
	  if [ -x "$binary" ] ; then
	      #echo "$binary"
	      ldd $binary | cut -d" " -f3
	  fi
	done \
	    | sort -u \
	    | cpio --quiet -pdumVLB $CHROOTDIR
	
	cp /etc/libnss-ldap.conf $CHROOTDIR/etc
	#cp -r /etc/ldap $CHROOTDIR/etc
	cp /lib/libnss_ldap* $CHROOTDIR/lib
	cp /usr/lib/libnss_ldap* $CHROOTDIR/usr/lib
	cp /usr/lib/libdb* $CHROOTDIR/usr/lib
	cp /usr/lib/libssl* $CHROOTDIR/usr/lib
	cp /usr/lib/libcrypto* $CHROOTDIR/usr/lib

	# Libpam-ldap
	[ -f /etc/ldap.secret ] && cp /etc/ldap.secret $CHROOTDIR/etc && chmod 600 /etc/ldap.secret

	# Now this never change
	cat > $CHROOTDIR/etc/passwd <<-FIN
root:x:0:0:Root:/:/bin/bash
nobody:x:65534:65534:nobody:/:/bin/false
FIN
	cat > $CHROOTDIR/etc/shadow <<-FIN
root:*:11142:0:99999:7:::
nobody:*:11142:0:99999:7:::
FIN
	cat > $CHROOTDIR/etc/group <<-FIN
root:x:0
nogroup:x:65534:
FIN

	;;

    *)
	echo "Usage: $0 {configure}"
	exit 1
	;;

esac
