#!/bin/sh
FTPROOT=/var/lib/sourceforge/chroot/ftproot
GRPHOME=/var/lib/sourceforge/chroot/home/groups
if [ $# != 1 ] 
then 
	$0 default
else
	target=$1
	if [  $(id -u) != 0 -a  "x$target" != "xlist" ] #-a "x$target" != "xclean"  ]
	then
	        echo "You must be root to run this, please enter passwd"
	        su -c "$0 $target"
	else
		case "$target" in
			default|configure)
			        do_config=$(grep ^do_config= /etc/sourceforge/sourceforge.conf | cut -d= -f2-)
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
    			        if [ "$do_config" = "true" ] ; then
				    if ! grep -q "^Include /etc/sourceforge/sf-proftpd.conf" /etc/proftpd.conf ; then
				    
                			perl -pi -e "s/^/#SF#/" /etc/proftpd.conf
	    				echo "### Previous lines commented by Sourceforge install" >> /etc/proftpd.conf
	    				echo "### Next lines inserted by Sourceforge install" >> /etc/proftpd.conf
					echo "ServerType standalone" >>/etc/proftpd.conf
	    				echo "Include /etc/sourceforge/sf-proftpd.conf" >> /etc/proftpd.conf
				    fi
				    /etc/init.d/proftpd restart
				fi
				;;
			update)
				(cd $GRPHOME; ls)| while read group
				do
					if [ ! -d $FTPROOT/pub/$group ]
					then
						gid=`ls -lnd $GRPHOME/$group | xargs | cut -d" " -f4`
						install -o sfftp -g $gid -m 2775 -d $FTPROOT/pub/$group
					fi
				done
				;;
			purge)
			        do_config=$(grep ^do_config= /etc/sourceforge/sourceforge.conf | cut -d= -f2-)
    			        if [ "$do_config" = "true" ] ; then
				    if grep -q "### Next lines inserted by Sourceforge install" /etc/proftpd.conf
					then
	    				perl -pi -e "s/### Previous lines commented by Sourceforge install\n//"  /etc/proftpd.conf
                			perl -pi -e "s/### Next lines inserted by Sourceforge install\n//" /etc/proftpd.conf
                			perl -pi -e "s:^Include /etc/sourceforge/sf-proftpd.conf\n::" /etc/proftpd.conf
                			perl -pi -e "s:^ServerType standalone\n::" /etc/proftpd.conf
                			perl -pi -e "s/^#SF#//" /etc/proftpd.conf
				    fi
				    /etc/init.d/proftpd restart
				fi
				rm -rf $FTPROOT
				deluser --quiet sfftp || true
				;;
		esac
	fi
fi
