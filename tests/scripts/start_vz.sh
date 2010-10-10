#!/bin/sh

VZHOST=$1
if sudo /root/start_vz.sh centos-5-x86 "$1"
then
	echo "VM Started"
else
	pwd
	. ../openvz/config.default
	if [ -f ../openvz/config.`hostname` ]
	then
		. ../openvz/config.`hostname`
	fi
	ARCH=`dpkg-architecture -qDEB_BUILD_ARCH`
	sudo /usr/sbin/vzctl create $VEIDCEN --private $VZPRIVATEDIR/$VEIDCEN --ostemplate centos-$CENTVERS-$ARCH-minimal
	sudo /usr/sbin/vzctl start $VEIDCEN
	VZHOST=$IPCENTOSBASE.$VEIDCEN
	export VZHOST
	sudo /usr/sbin/vzctl set $VEIDCEN --ipadd $IPCENTOSBASE.$VEIDCEN --save
        sudo /usr/sbin/vzctl set $VEIDCEN --nameserver $IPCENTOSDNS --save
fi

ssh -o 'StrictHostKeyChecking=no' "root@$VZHOST" uname -a
ret=$?

if [ $ret -ne 0 ];then
	sleep 10;
	ssh -o 'StrictHostKeyChecking=no' "root@$VZHOST" uname -a
fi

sleep 1
