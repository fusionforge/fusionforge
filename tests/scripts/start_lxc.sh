#! /bin/sh

# You need to allow current user to sudo on /usr/bin/lxc-create /usr/bin/lxc-start /usr/lib/lxc/templates/lxc-debian6.postinst
configdir=`dirname $0`/../config
lxcdir=`dirname $0`/../lxc

if [ -z "$LXCDEBTEMPLATE" ]
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
	fi
fi

sudo /usr/bin/lxc-create -n $HOST -f $lxcdir/config.$LXCDEBTEMPLATE -t $LXCDEBTEMPLATE
sudo /usr/lib/lxc/templates/lxc-$LXCDEBTEMPLATE.postinst -p /var/lib/lxc/$HOST -n $HOST --address=$IPDEBBASE.$VEIDDEB --netmask=$IPDEBMASK --gateway=$IPDEBGW --pubkey=$SSHPUBKEY --hostkeydir=$HOSTKEYDIR
sudo /usr/bin/lxc-start -n $HOST -d

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

sleep 1
