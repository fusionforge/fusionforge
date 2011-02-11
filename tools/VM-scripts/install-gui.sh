#! /bin/sh

apt-get install -y nodm xfce4
sed -i -e 's/^NODM_ENABLED=.*/NODM_ENABLED=true/' -e 's/^NODM_USER=.*/NODM_USER=root/' /etc/default/nodm
/etc/init.d/nodm restart
