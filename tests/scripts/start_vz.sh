#!/bin/sh

VZHOST=$1
USEVZCTL=${USEVZCTL:-false}
if ! $USEVZCTL
then
	echo "Using /root/start_vz.sh"
	(sudo /root/start_vz.sh $VZTEMPLATE "$1")
else
	sudo /usr/sbin/vzctl create $VEID --private $VZPRIVATEDIR/$VEID --ostemplate $VZTEMPLATE
	sudo /usr/sbin/vzctl start $VEID
	VZHOST=$IPBASE.$VEID
	export VZHOST
	sudo /usr/sbin/vzctl set $VEID --ipadd $IPBASE.$VEID --save
        sudo /usr/sbin/vzctl set $VEID --nameserver $IPDNS --save
	sudo /usr/sbin/vzctl set $VEID --hostname $HOST --save
	sudo /usr/sbin/vzctl set $VEID --privvmpages $((65536*2)):$((69632*2)) --save
fi

ssh -o 'StrictHostKeyChecking=no' "root@$VZHOST" uname -a
ret=$?

if [ $ret -ne 0 ];then
	sleep 10;
	ssh -o 'StrictHostKeyChecking=no' "root@$VZHOST" uname -a
fi

sleep 1
