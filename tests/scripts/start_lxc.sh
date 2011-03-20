#! /bin/sh

# You need to allow current user to sudo on /usr/bin/lxc-create /usr/bin/lxc-start /usr/lib/lxc/templates/lxc-debian6.postinst
configdir=`dirname $0`/../config
lxcdir=`dirname $0`/../lxc

if [ -z "$LXCTEMPLATE" ]
then
	. $configdir/default
	if [ -f $configdir/`hostname` ] ; then . $configdir/`hostname`; fi
fi

if [ -z "$HOST" ]
then
	if [ -z "$1" ]
	then
		echo "usage : $0 <hostname>"
		exit 1
	else
		HOST=$1
		# When HOST is given as an arg, I have to guesse which data to take
		# depending on the host name
		case $HOST in
			deb*src*)
				LXCTEMPLATE=$LXCDEBTEMPLATE
				IPBASE=$IPDEBBASE
				VEID=$VEIDSEB
				IPMASK=$IPDEBMASK
				IPGW=$IPDEBGW
				;;
			deb*)
				LXCTEMPLATE=$LXCDEBTEMPLATE
				IPBASE=$IPDEBBASE
				VEID=$VEIDDEB
				IPMASK=$IPDEBMASK
				IPGW=$IPDEBGW
				;;
			centos*src*)
				LXCTEMPLATE=$LXCCOSTEMPLATE
				IPBASE=$IPCOSBASE
				VEID=$VEIDSRC
				IPMASK=$IPCOSMASK
				IPGW=$IPCOSGW
				;;
			centos*)
				LXCTEMPLATE=$LXCCOSTEMPLATE
				IPBASE=$IPCOSBASE
				VEID=$VEIDCOS
				IPMASK=$IPCOSMASK
				IPGW=$IPCOSGW
				;;
			fgdeb*)
				LXCTEMPLATE=$LXCDEBTEMPLATE
				VEID=""
				;;
			fgcos*)
				LXCTEMPLATE=$LXCCOSTEMPLATE
				VEID=""
				;;
		esac
	fi
fi

if [ ! -e /usr/lib/lxc/templates/lxc-$LXCTEMPLATE ]
then 
	echo "/usr/lib/lxc/templates/lxc-$LXCTEMPLATE not found"
	echo "you need to install template"
	echo "run: (cd $lxcdir ; sudo make)"
else
	sudo /usr/bin/lxc-create -n $HOST -f $lxcdir/config.$LXCTEMPLATE -t $LXCTEMPLATE
fi

if [ ! -e /usr/lib/lxc/templates/lxc-$LXCTEMPLATE.postinst ]
then
	echo "/usr/lib/lxc/templates/lxc-$LXCTEMPLATE.postinst not found"
	echo "you need to install template"
	echo "run: (cd $lxcdir ; sudo make)"
else
	if [ "x$VEID" != "x" ]
	then 
		sudo /usr/lib/lxc/templates/lxc-$LXCTEMPLATE.postinst \
		-p /var/lib/lxc/$HOST -n $HOST \
		--address=$IPBASE.$VEID \
		--netmask=$IPMASK \
		--gateway=$IPGW \
		--pubkey=$SSHPUBKEY
	else
		sudo /usr/lib/lxc/templates/lxc-$LXCTEMPLATE.postinst \
		-p /var/lib/lxc/$HOST -n $HOST \
		--pubkey=$SSHPUBKEY
	fi
	sudo /usr/bin/lxc-start -n $HOST -d
fi

ssh -o 'StrictHostKeyChecking=no' "root@$HOST" uname -a
ret=$?
for loop in 1 2 3 4 5 6 7 8 9
do

	if [ $ret -ne 0 ];then
		echo -n $loop
		sleep 10;
		ssh -o 'StrictHostKeyChecking=no' "root@$HOST" uname -a
		ret=$?
	fi
done

#ssh -X root@$IPBASE.$VEID "LANG=C java -jar selenium-server.jar -interactive &"

sleep 1
exit $ret
