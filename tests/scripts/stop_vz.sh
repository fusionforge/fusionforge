#!/bin/sh

if sudo /root/stop_vz.sh "$1"
then
	echo "VM removed"
else
	pwd
	. ../openvz/config.default
	if [ -f ../openvz/config.`hostname` ]
	then
		. ../openvz/config.`hostname`
	fi
	echo "Run the folowing to destroy test server :"
	echo "sudo /usr/sbin/vzctl stop $VEIDCEN"
	echo "sudo /usr/sbin/vzctl destroy $VEIDCEN"
fi
