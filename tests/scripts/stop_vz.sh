#!/bin/sh

if sudo /root/stop_vz.sh "$1"
then
	echo "VM removed"
else
	sudo /usr/sbin/vzctl stop $VEID
	sudo /usr/sbin/vzctl destroy $VEID
fi
