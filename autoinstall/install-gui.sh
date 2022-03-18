#!/bin/bash
#
# This file is part of FusionForge. FusionForge is free software;
# you can redistribute it and/or modify it under the terms of the
# GNU General Public License as published by the Free Software
# Foundation; either version 2 of the Licence, or (at your option)
# any later version.
#
# FusionForge is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with FusionForge; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

set -e
. $(dirname $0)/common-backports

if [ -e /etc/debian_version ]; then
	apt-get install -y xorg nodm xfce4 gnome-icon-theme
	sed -i -e 's/^NODM_ENABLED=.*/NODM_ENABLED=true/' -e 's/^NODM_USER=.*/NODM_USER=root/' /etc/default/nodm
	/etc/init.d/nodm restart
elif [[ ! -z `cat /etc/os-release | grep 'SUSE'`]]; then
	suse_check_release
	suse_install_rpms patterns-openSUSE-xfce_basis
else
	yum -y groupinstall 'X Window system'
	yum -y --enablerepo=epel groupinstall xfce
	yum -y install xfce4-terminal
	systemctl set-default graphical.target
	systemctl isolate graphical.target
fi
