#!/bin/bash

inetdname=inetd
if [ $# != 1 ]
then
	echo "Usage: $0 {configure|purge}"
	exit 1
else
	if [ "$1" = "configure" ]
	then
		# Setup our own CVS
		echo "Modifying inetd for cvs server"
		echo "CVS usual config is changed for sourceforge one"
		# To easily support xinetd but don't now if it's like inetd
		if ! grep -q "#Sourceforge comment#" /etc/${inetdname}.conf ; then
		    	perl -pi -e "s/^cvspserver/#Sourceforge comment#cvspserver/" /etc/${inetdname}.conf
	    		echo "cvspserver	stream	tcp	nowait.400	root	/usr/sbin/tcpd	/usr/lib/sourceforge/bin/cvs-pserver" >> /etc/${inetdname}.conf
			#update-inetd --add  "cvspserver stream tcp nowait.400 root /usr/sbin/tcpd /usr/lib/sourceforge/bin/cvs-pserver"
	    		/etc/init.d/${inetdname} restart
		fi
	else
		if [ "$1" = "purge" ]
		then
			echo "Purging inetd for cvs server"
		    	perl -pi -e "s/^#Sourceforge comment#cvspserver/cvspserver/" /etc/${inetdname}.conf
		    	perl -pi -e "s:.*sourceforge/bin/cvs-pserver\n::" /etc/${inetdname}.conf
			# A little bit too violent
			# This remove all line containing cvspserver
			#update-inetd --remove cvspserver 
		else
			echo "Usage: $0 {configure|purge}"
			exit 1
		fi
	fi
fi
