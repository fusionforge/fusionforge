#!/bin/sh

sudo /root/start_vz.sh centos-5-i386-default-5.2-20081107 "$1"

rm -f /home/albot/.ssh/known_hosts

ssh -o 'StrictHostKeyChecking=no' root@centos52 uname -a
ret=$?

if [ $ret -ne 0 ];then
	sleep 10;
	ssh -o 'StrictHostKeyChecking=no' root@centos52 uname -a
fi

sleep 1
