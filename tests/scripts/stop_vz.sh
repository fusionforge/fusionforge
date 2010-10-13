#!/bin/sh

USEVZCTL=${USEVZCTL:-false}
if ! $USEVZCTL
then
	echo "Using /root/stop_vz.sh"
	sudo /root/stop_vz.sh "$1"
else
	sudo /usr/sbin/vzctl stop $VEID
	sudo /usr/sbin/vzctl destroy $VEID
fi
