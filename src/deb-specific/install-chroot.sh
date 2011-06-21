#! /bin/sh
#
# Set up a size-reduced chroot of the system for use in FusionForge
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian),
# Thorsten Glaser

set -e

if [ $(id -u) != 0 ] ; then
	echo "You must be root to run this, please enter passwd"
	echo "+ sudo $0 $*"
	exec sudo "$0" "$@"
fi

CHROOTDIR=$(/usr/share/gforge/bin/forge_get_config chroot)

case $1 in
configure)
	echo "Installing chroot environnement at $CHROOTDIR"
	test -d "$CHROOTDIR" || install -d -m 755 "$CHROOTDIR"
	test -d "$CHROOTDIR" || exit 1
	for dir in \
	    bin \
	    cvsroot \
	    dev \
	    etc \
	    etc/pam.d \
	    etc/security \
	    home \
	    home/groups \
	    home/users \
	    lib \
	    lib/security \
	    lib64 \
	    lib64/security \
	    usr \
	    usr/bin \
	    usr/lib \
	    var \
	    var/lib \
	    var/lib/gforge \
	    var/run \
	    var/run/postgresql \
	    var/run/sshd \
	    ; do
		test -d "$CHROOTDIR/$dir" || mkdir "$CHROOTDIR/$dir"
	done
	rm -rf "$CHROOTDIR/tmp"
	install -d -m 1777 "$CHROOTDIR/tmp"
	[ -L "$CHROOTDIR/var/lib/gforge/chroot" ] && rm "$CHROOTDIR/var/lib/gforge/chroot"
	[ -d "$CHROOTDIR/var/lib/gforge/chroot" ] && rmdir "$CHROOTDIR/var/lib/gforge/chroot"
	ln -s ../../.. "$CHROOTDIR/var/lib/gforge/chroot"

	# Copy needed binaries
	# For testing /bin/ls /bin/su
	# Maybe needed /bin/chgrp
	# Could be restricted /bin/bash
	# TODO: remove unneeded stuff from that list
	for binary in \
	    /bin/bash \
	    /bin/chgrp \
	    /bin/ls \
	    /bin/sh \
	    /lib/security/pam_pgsql.so \
	    /lib64/security/pam_pgsql.so \
	    /usr/bin/cvs \
	    /usr/bin/svnserve \
	    /usr/sbin/sshd \
	    ; do
		if [ -e "$binary" ]; then
			echo "$binary"
			ldd "$binary" | awk '/=>/ { print $3 }' | grep '^/'
			ldd "$binary" | awk '{ print $1 }' | grep '^/'
		fi
	done \
	    | sort -u \
	    | cpio --quiet -pdumVLB "$CHROOTDIR/"

	for i in \
	    /etc/nss-pgsql-root.conf \
	    /etc/nss-pgsql.conf \
	    /etc/pam.d/common* \
	    /etc/pam.d/cvs \
	    /etc/pam.d/login \
	    /etc/pam.d/other \
	    /etc/pam.d/ssh \
	    /etc/pam.d/ssh-nonfree \
	    /etc/pam.d/su \
	    /etc/security/*.conf \
	    /lib/ld-linux*.so.* \
	    /lib/libcom_err* \
	    /lib/libgcc_s* \
	    /lib/libnss_files* \
	    /lib/libnss_pgsql* \
	    /lib/libpam* \
	    /lib/security/* \
	    /usr/lib/libcom_err* \
	    /usr/lib/libcrypto* \
	    /usr/lib/libdb* \
	    /usr/lib/libk5crypto* \
	    /usr/lib/libkrb5* \
	    /usr/lib/libnss_pgsql* \
	    /usr/lib/libpq* \
	    /usr/lib/libssl* \
	    ; do
		test -e "$i" || continue
		cp "$i" $CHROOTDIR/"$i"
	done

	# Create devices files
	[ -c "$CHROOTDIR/dev/null" ] || mknod "$CHROOTDIR/dev/null" c 1 3 || true
	[ -c "$CHROOTDIR/dev/urandom" ] || mknod "$CHROOTDIR/dev/urandom" c 1 9 || true
	[ -c "$CHROOTDIR/dev/console" ] || mknod "$CHROOTDIR/dev/console" c 5 1 || true
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
	cat >"$CHROOTDIR/etc/nsswitch.conf" <<-FIN
		passwd:		files pgsql
		group:		files pgsql
		shadow:		files pgsql
FIN
	# Copy miscellaneous files
	[ -d /etc/ssh ] && find /etc/ssh | cpio --quiet -pdumLB "$CHROOTDIR/"
	[ -d /etc/ssh-nonfree ] && find /etc/ssh-nonfree | cpio --quiet -pdumLB "$CHROOTDIR/"

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
	[ -f /usr/lib/libcom_err.so ] && cp /usr/lib/libcom_err.so "$CHROOTDIR/usr/lib/libcom_err.so.2"



	# Now this never change
	cat >"$CHROOTDIR/etc/passwd" <<-FIN
root:x:0:0:Root:/:/bin/bash
nobody:x:65534:65534:nobody:/:/bin/false
FIN
	getent passwd sshd | sed "s:$CHROOTDIR::g" >>"$CHROOTDIR/etc/passwd"
	getent passwd scm-gforge | sed "s:$CHROOTDIR::g" >>"$CHROOTDIR/etc/passwd"
	getent passwd anonscm-gforge | sed "s:$CHROOTDIR::g" >>"$CHROOTDIR/etc/passwd"
	cat >"$CHROOTDIR/etc/shadow" <<-FIN
root:*:11142:0:99999:7:::
nobody:*:11142:0:99999:7:::
FIN
	cat >"$CHROOTDIR/etc/group" <<-FIN
root:x:0
nogroup:x:65534:
FIN
getent group anonscm-gforge >>"$CHROOTDIR/etc/group"

	;;

*)
	echo "Usage: $0 {configure}"
	exit 1
	;;

esac
