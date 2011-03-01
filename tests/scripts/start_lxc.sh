#! /bin/sh -x

sudo LANG=C MIRROR=$DEBMIRROR SUITE=$DIST /usr/bin/lxc-create -n $HOST -f ../lxc/config.$LXCTEMPLATE -t $LXCTEMPLATE
cd ../lxc ; sudo ./lxc-debian6.postinst $HOST
sudo LANG=C /usr/bin/lxc-start -n $HOST -d
