#! /bin/sh
# 
# $Id$
#
# Configure Proftpd for Sourceforge
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

FTPROOT=/var/lib/sourceforge/chroot/ftproot
GRPHOME=/var/lib/sourceforge/chroot/home/groups

case "$1" in
    configure)
	adduser --quiet --system --group --home $FTPROOT sfftp
	mkdir -p $FTPROOT/pub
	cat >$FTPROOT/welcome.msg<<-FIN
Welcome, archive user %U@%R !

The local time is: %T

This is an experimental FTP server.  If have any unusual problems,
please report them via e-mail to <root@%L>.
FIN
        #
	# This initialize FTP
	#
	if ! grep -q "^Include /etc/sourceforge/sf-proftpd.conf" /etc/proftpd.conf ; then
	    perl -pi -e "s/^/#SF#/" /etc/proftpd.conf
	    echo "### Previous lines commented by Sourceforge install" >> /etc/proftpd.conf
	    echo "### Next lines inserted by Sourceforge install" >> /etc/proftpd.conf
	    echo "ServerType standalone" >>/etc/proftpd.conf
	    echo "Include /etc/sourceforge/sf-proftpd.conf" >> /etc/proftpd.conf
	fi
	/etc/init.d/proftpd restart
	;;

    update)
	(cd $GRPHOME; ls) | while read group ; do
	    if [ ! -d $FTPROOT/pub/$group ] ; then
		install -o sfftp -g $group -m 2775 -d $FTPROOT/pub/$group
	    fi
	done
	;;
    
    purge)
	if grep -q "### Next lines inserted by Sourceforge install" /etc/proftpd.conf ; then
	    perl -pi -e "s/### Previous lines commented by Sourceforge install\n//"  /etc/proftpd.conf
	    perl -pi -e "s/### Next lines inserted by Sourceforge install\n//" /etc/proftpd.conf
	    perl -pi -e "s:^Include /etc/sourceforge/sf-proftpd.conf\n::" /etc/proftpd.conf
	    perl -pi -e "s:^ServerType standalone\n::" /etc/proftpd.conf
	    perl -pi -e "s/^#SF#//" /etc/proftpd.conf
	fi
	/etc/init.d/proftpd restart
	rm -rf $FTPROOT
	deluser sfftp || true
	;;

    *)
	echo "Usage: $0 {configure|update|purge}"
	exit 1
	;;

esac
