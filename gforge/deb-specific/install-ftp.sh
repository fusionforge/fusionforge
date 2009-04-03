#! /bin/sh
# 
# Configure Proftpd for FusionForge
# Christian Bayle, Roland Mas

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

FTPROOT=/var/lib/gforge/chroot/ftproot
GRPHOME=/var/lib/gforge/chroot/home/groups
FTPCONF=/etc/proftpd/proftpd.conf

case "$1" in
    configure-files)
	cp -a ${FTPCONF} ${FTPCONF}.gforge-new
        #
	# This initialize FTP
	#
	if ! grep -q "^Include /etc/gforge/sf-proftpd.conf" ${FTPCONF}.gforge-new ; then
	    perl -pi -e "s/^/#SF#/" ${FTPCONF}.gforge-new
	    echo "### Previous lines commented by GForge install" >> ${FTPCONF}.gforge-new
	    echo "### Next lines inserted by GForge install" >> ${FTPCONF}.gforge-new
	    echo "ServerType standalone" >>${FTPCONF}.gforge-new
	    echo "Include /etc/gforge/sf-proftpd.conf" >> ${FTPCONF}.gforge-new
	fi
	;;

    configure)
	adduser --quiet --system --group --home $FTPROOT sfftp
	mkdir -p $FTPROOT/pub
	if [ ! -f $FTPROOT/welcome.msg ] ; then
		cat >$FTPROOT/welcome.msg<<-FIN
Welcome, archive user %U@%R !

The local time is: %T

This is an experimental FTP server.  If have any unusual problems,
please report them via e-mail to <root@%L>.
FIN
	fi
	#invoke-rc.d proftpd restart
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
	cp -a ${FTPCONF} ${FTPCONF}.gforge-new
	if grep -q "### Next lines inserted by GForge install" ${FTPCONF}.gforge-new ; then
	    perl -pi -e "s/### Previous lines commented by GForge install\n//"  ${FTPCONF}.gforge-new
	    perl -pi -e "s/### Next lines inserted by GForge install\n//" ${FTPCONF}.gforge-new
	    perl -pi -e "s:^Include /etc/gforge/sf-proftpd.conf\n::" ${FTPCONF}.gforge-new
	    perl -pi -e "s:^ServerType standalone\n::" ${FTPCONF}.gforge-new
	    perl -pi -e "s/^#SF#//" ${FTPCONF}.gforge-new
	fi
	;;

    purge)
	#invoke-rc.d proftpd restart
	rm -rf $FTPROOT
	deluser --quiet sfftp || true
	;;

    *)
	echo "Usage: $0 {configure|configure-files|update|purge|purge-files}"
	exit 1
	;;

esac
