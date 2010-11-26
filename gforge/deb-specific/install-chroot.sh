#! /bin/sh
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
	    lib64 \
	    lib64/security \
	    dev \
	    var \
	    var/run \
	    var/run/sshd \
	    var/run/postgresql \
	    var/lib \
	    var/lib/gforge \
	    cvsroot \
	    home \
	    home/users \
	    home/groups
	  do
	  [ -d $CHROOTDIR/$dir ] || mkdir $CHROOTDIR/$dir
	done
	install -d -m 1777 $CHROOTDIR/tmp
	[ -L $CHROOTDIR/var/lib/gforge/chroot ] && rm $CHROOTDIR/var/lib/gforge/chroot
	[ -d $CHROOTDIR/var/lib/gforge/chroot ] && rmdir $CHROOTDIR/var/lib/gforge/chroot
	ln -s ../../../ $CHROOTDIR/var/lib/gforge/chroot
	
	# Copy needed binaries
	# For testing /bin/ls /bin/su
	# Maybe needed /bin/chgrp
	# Could be restricted /bin/bash
	# TODO: remove unneeded stuff from that list
	for binary in \
	    /usr/sbin/sshd \
	    /usr/bin/cvs \
	    /usr/bin/svnserve \
	    /bin/ls \
	    /bin/sh \
	    /bin/bash \
	    /bin/chgrp \
	    /lib/security/pam_pgsql.so \
	    /lib64/security/pam_pgsql.so ; do
	  if [ -e "$binary" ] ; then
	      echo "$binary"
	      ldd $binary | awk '/=>/ { print $3 }' | grep ^/
	      ldd $binary | awk '{ print $1 }' | grep ^/
	  fi
	done \
	    | sort -u \
	    | cpio --quiet -pdumVLB $CHROOTDIR

	# cvs extra
	cp /lib/ld-linux*.so.* $CHROOTDIR/lib
	# sshd extras
	# pthread cancel
	cp /lib/libgcc_s* $CHROOTDIR/lib
	
	# nss extras
	# /lib/libnss_pgsql ?
	cp /lib/libcom_err* $CHROOTDIR/lib

	# Create devices files
	[ -c $CHROOTDIR/dev/null ] || mknod $CHROOTDIR/dev/null c 1 3 || true
	[ -c $CHROOTDIR/dev/urandom ] || mknod $CHROOTDIR/dev/urandom c 1 9 || true
	[ -c $CHROOTDIR/dev/console ] || mknod $CHROOTDIR/dev/console c 5 1 || true
	# For /dev/log
	if [ -e /etc/default/syslogd ] \
	    && [ ! -e /etc/rsyslog.conf ] \
	    && ! grep -q "^SYSLOGD.*/var/lib/gforge/chroot/dev/log.*" /etc/default/syslogd ; then 
		echo '######################################################################################################'
		echo 'WARNING: you must have SYSLOGD="-p /dev/log -a /var/lib/gforge/chroot/dev/log" in /etc/default/syslogd'
		echo 'To have cvs pserver running correctly'
		echo '######################################################################################################'
	fi

	
	# To get uid/gid
	# Maybe ldap later
	cat > $CHROOTDIR/etc/nsswitch.conf <<-FIN
passwd:         files pgsql 
group:          files pgsql
shadow:         files pgsql
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
	[ "`ls /etc/pam.d/common* 2>/dev/null`" ] && cp /etc/pam.d/common* $CHROOTDIR/etc/pam.d
	cp /lib/libpam* $CHROOTDIR/lib
	
	cp /lib/libnss_files* $CHROOTDIR/lib
	cp /lib/security/* $CHROOTDIR/lib/security
	cp /etc/security/*.conf $CHROOTDIR/etc/security

#	# Libnss-ldap related stuffs
#	for binary in \
#	    /usr/bin/ldapsearch ; do
#	  if [ -x "$binary" ] ; then
#	      #echo "$binary"
#	      ldd $binary | cut -d" " -f3
#	  fi
#	done \
#	    | sort -u \
#	    | cpio --quiet -pdumVLB $CHROOTDIR
#	
#	#cp -r /etc/ldap $CHROOTDIR/etc
#	[ -e /etc/libnss-ldap.conf ] && cp /etc/libnss-ldap.conf $CHROOTDIR/etc
#	[ -e /etc/libnss-pgsql.conf ] && cp /etc/libnss-pgsql.conf $CHROOTDIR/etc
#	[ "$(echo /lib/libnss_ldap*)" != "/lib/libnss_ldap*" ] && cp /lib/libnss_ldap* $CHROOTDIR/lib
#	[ "$(echo /usr/lib/libnss_ldap*)" != "/usr/lib/libnss_ldap*" ] && cp /usr/lib/libnss_ldap* $CHROOTDIR/usr/lib
#
#	# Libpam-ldap
#	[ -f /etc/ldap.secret ] && cp /etc/ldap.secret $CHROOTDIR/etc && chmod 600 /etc/ldap.secret

	# Libnss-pgsql related stuffs
	[ -e /etc/nss-pgsql.conf ] && cp /etc/nss-pgsql.conf $CHROOTDIR/etc
	[ -e /etc/nss-pgsql-root.conf ] && cp /etc/nss-pgsql-root.conf $CHROOTDIR/etc
	[ "$(echo /lib/libnss_pgsql*)" != "/lib/libnss_pgsql*" ] && cp /lib/libnss_pgsql* $CHROOTDIR/lib
	[ "$(echo /usr/lib/libnss_pgsql*)" != "/usr/lib/libnss_pgsql*" ] && cp /usr/lib/libnss_pgsql* $CHROOTDIR/usr/lib
	[ "$(echo /usr/lib/libdb*)" != "/usr/lib/libdb*" ] && cp /usr/lib/libdb* $CHROOTDIR/usr/lib
	[ "$(echo /usr/lib/libssl*)" != "/usr/lib/libssl*" ] && cp /usr/lib/libssl* $CHROOTDIR/usr/lib
	[ "$(echo /usr/lib/libcrypto*)" != "/usr/lib/libcrypto*" ] && cp /usr/lib/libcrypto* $CHROOTDIR/usr/lib

	[ "$(echo /usr/lib/libpq*)" != "/usr/lib/libpq*" ] && cp /usr/lib/libpq* $CHROOTDIR/usr/lib
	[ "$(echo /usr/lib/libkrb5*)" != "/usr/lib/libkrb5*" ] && cp /usr/lib/libkrb5* $CHROOTDIR/usr/lib
	[ "$(echo /usr/lib/libk5crypto*)" != "/usr/lib/libk5crypto*" ] && cp /usr/lib/libk5crypto* $CHROOTDIR/usr/lib
	[ "$(echo /usr/lib/libcom_err*)" != "/usr/lib/libcom_err*" ] && cp /usr/lib/libcom_err* $CHROOTDIR/usr/lib
	[ -f /usr/lib/libcom_err.so ] && cp /usr/lib/libcom_err.so $CHROOTDIR/usr/lib/libcom_err.so.2



	# Now this never change
	cat > $CHROOTDIR/etc/passwd <<-FIN
root:x:0:0:Root:/:/bin/bash
nobody:x:65534:65534:nobody:/:/bin/false
FIN
	getent passwd sshd | sed "s:$CHROOTDIR::g" >> $CHROOTDIR/etc/passwd
	getent passwd scm-gforge | sed "s:$CHROOTDIR::g" >> $CHROOTDIR/etc/passwd
	getent passwd anonscm-gforge | sed "s:$CHROOTDIR::g" >> $CHROOTDIR/etc/passwd
	cat > $CHROOTDIR/etc/shadow <<-FIN
root:*:11142:0:99999:7:::
nobody:*:11142:0:99999:7:::
FIN
	cat > $CHROOTDIR/etc/group <<-FIN
root:x:0
nogroup:x:65534:
FIN
getent group anonscm-gforge >> $CHROOTDIR/etc/group

	;;

    *)
	echo "Usage: $0 {configure}"
	exit 1
	;;

esac
