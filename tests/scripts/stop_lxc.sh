#! /bin/sh

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
sudo /usr/bin/lxc-stop -n $HOST
sudo /usr/bin/lxc-destroy -n $HOST
