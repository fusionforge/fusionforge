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
    configure-files)
	cp -a /etc/proftpd.conf /etc/proftpd.conf.sourceforge-new
        #
	# This initialize FTP
	#
	if ! grep -q "^Include /etc/sourceforge/sf-proftpd.conf" /etc/proftpd.conf.sourceforge-new ; then
	    perl -pi -e "s/^/#SF#/" /etc/proftpd.conf.sourceforge-new
	    echo "### Previous lines commented by Sourceforge install" >> /etc/proftpd.conf.sourceforge-new
	    echo "### Next lines inserted by Sourceforge install" >> /etc/proftpd.conf.sourceforge-new
	    echo "ServerType standalone" >>/etc/proftpd.conf.sourceforge-new
	    echo "Include /etc/sourceforge/sf-proftpd.conf" >> /etc/proftpd.conf.sourceforge-new
	fi
	;;

    configure)
	adduser --quiet --system --group --home $FTPROOT sfftp
	mkdir -p $FTPROOT/pub
	cat >$FTPROOT/welcome.msg<<-FIN
Welcome, archive user %U@%R !

The local time is: %T

This is an experimental FTP server.  If have any unusual problems,
please report them via e-mail to <root@%L>.
FIN
	invoke-rc.d proftpd restart
	;;

    update)
	(cd $GRPHOME; ls) | while read group ; do
	    if [ ! -d $FTPROOT/pub/$group ] ; then
	    	gid=`ls -lnd $GRPHOME/$group | xargs | cut -d" " -f4`
		install -o sfftp -g $gid -m 2775 -d $FTPROOT/pub/$group
	    fi
	done
	;;
    
    purge-files)
	cp -a /etc/proftpd.conf /etc/proftpd.conf.sourceforge-new
	if grep -q "### Next lines inserted by Sourceforge install" /etc/proftpd.conf.sourceforge-new ; then
	    perl -pi -e "s/### Previous lines commented by Sourceforge install\n//"  /etc/proftpd.conf.sourceforge-new
	    perl -pi -e "s/### Next lines inserted by Sourceforge install\n//" /etc/proftpd.conf.sourceforge-new
	    perl -pi -e "s:^Include /etc/sourceforge/sf-proftpd.conf\n::" /etc/proftpd.conf.sourceforge-new
	    perl -pi -e "s:^ServerType standalone\n::" /etc/proftpd.conf.sourceforge-new
	    perl -pi -e "s/^#SF#//" /etc/proftpd.conf.sourceforge-new
	fi
	;;

    purge)
	invoke-rc.d proftpd restart
	rm -rf $FTPROOT
	deluser --quiet sfftp || true
	;;

    *)
	echo "Usage: $0 {configure|configure-files|update|purge|purge-files}"
	exit 1
	;;

esac
