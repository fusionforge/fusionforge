#!/bin/bash

set -e
. $(dirname $0)/common-backports

if [ -e /etc/debian_version ]; then
    apt-get install -y xorg nodm xfce4 gnome-icon-theme
    sed -i -e 's/^NODM_ENABLED=.*/NODM_ENABLED=true/' -e 's/^NODM_USER=.*/NODM_USER=root/' /etc/default/nodm
    /etc/init.d/nodm restart
elif [ -e /etc/SuSE-release ]; then
    suse_check_release
    suse_install_rpms patterns-openSUSE-xfce_basis
else
    yum -y groupinstall 'X Window system'
    yum -y --enablerepo=epel groupinstall xfce
    yum -y install xfce4-terminal
    systemctl set-default graphical.target
    systemctl isolate graphical.target
fi
