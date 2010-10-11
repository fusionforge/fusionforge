#!/bin/sh

VZHOST=$1
if sudo /root/start_vz.sh centos-5-x86 "$1"
then
	echo "VM Started"
else
	sudo /usr/sbin/vzctl create $VEID --private $VZPRIVATEDIR/$VEID --ostemplate $VZTEMPLATE
	sudo /usr/sbin/vzctl start $VEID
	VZHOST=$IPBASE.$VEID
	export VZHOST
	sudo /usr/sbin/vzctl set $VEID --ipadd $IPBASE.$VEID --save
        sudo /usr/sbin/vzctl set $VEID --nameserver $IPDNS --save
fi

ssh -o 'StrictHostKeyChecking=no' "root@$VZHOST" uname -a
ret=$?

if [ $ret -ne 0 ];then
	sleep 10;
	ssh -o 'StrictHostKeyChecking=no' "root@$VZHOST" uname -a
fi

sleep 1
