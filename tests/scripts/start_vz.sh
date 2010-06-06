#!/bin/sh

sudo /root/start_vz.sh centos-5-x86 "$1"

ssh -o 'StrictHostKeyChecking=no' "root@$1" uname -a
ret=$?

if [ $ret -ne 0 ];then
	sleep 10;
	ssh -o 'StrictHostKeyChecking=no' "root@$1" uname -a
fi

sleep 1
