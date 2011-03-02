#! /bin/sh

# You need to allow current user to sudo on /usr/bin/lxc-create /usr/bin/lxc-start /usr/lib/lxc/templates/lxc-debian6.postinst

if [ -z "$LXCDEBTEMPLATE" ]
then
	configdir=`dirname $0`/../config
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

sudo /usr/bin/lxc-create -n $HOST -f ../lxc/config.$LXCDEBTEMPLATE -t $LXCDEBTEMPLATE
sudo /usr/lib/lxc/templates/lxc-$LXCDEBTEMPLATE.postinst -n $HOST --address=$IPDEBBASE.$VEIDDEB --netmask=$IPDEBMASK --gateway=$IPDEBGW --pubkey=$SSHPUBKEY --hostkeydir=$HOSTKEYDIR
sudo /usr/bin/lxc-start -n $HOST -d
