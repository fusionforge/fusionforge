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

CHROOTDIR=/var/lib/sourceforge/chroot

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
	    /bin/bash \
	    /bin/chgrp ; do
	  echo "$binary"
	  ldd $binary | cut -d" " -f3
	done \
	    | sort -u \
	    | cpio --quiet -pdumVLB $CHROOTDIR

	# Create devices files
	[ -c $CHROOTDIR/dev/null ] || mknod $CHROOTDIR/dev/null c 1 3
	[ -c $CHROOTDIR/dev/urandom ] || mknod $CHROOTDIR/dev/urandom c 1 9
	[ -c $CHROOTDIR/dev/console ] || mknod $CHROOTDIR/dev/console c 5 1
	# To get uid/gid
	# Maybe ldap later
	cat > $CHROOTDIR/etc/nsswitch.conf <<-FIN
passwd:         files
group:          files
shadow:         files
FIN
	# Copy miscellaneous files
	find /etc/ssh | cpio --quiet -pdumLB $CHROOTDIR
	cp /etc/pam.d/ssh $CHROOTDIR/etc/pam.d
	cp /etc/pam.d/login $CHROOTDIR/etc/pam.d
	cp /etc/pam.d/su $CHROOTDIR/etc/pam.d
	cp /lib/libpam* $CHROOTDIR/lib
	
	cp /lib/libnss_files* $CHROOTDIR/lib
	cp /lib/security/* $CHROOTDIR/lib/security
	cp /etc/security/*.conf $CHROOTDIR/etc/security
	;;

    *)
	echo "Usage: $0 {configure}"
	exit 1
	;;

esac
