#! /bin/sh

apt-get install -y xorg nodm xfce4 tango-icon-theme
sed -i -e 's/^NODM_ENABLED=.*/NODM_ENABLED=true/' -e 's/^NODM_USER=.*/NODM_USER=root/' /etc/default/nodm
/etc/init.d/nodm restart
