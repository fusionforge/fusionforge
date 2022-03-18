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

# Wrapper to run the testsuite in a headless X server

if [ -z "$1" ]; then
	echo "$(basename $0): run the testsuite in a headless X server"
	echo "Usage: $0 [params]"
	echo "Ex: $0 deb/debian"
	exit 1
fi

# Check vncserver
if ! type vncserver 2>/dev/null
then
	echo "Installing vncserver"
	if type yum >/dev/null 2>&1
	then
		yum install -y tigervnc-server
	fi
	if type apt-get >/dev/null 2>&1
	then
		if apt show tigervnc-standalone-server > /dev/null ; then
			echo "Installing tigervnc-standalone-server and tigervnc-common"
			apt-get -y install tigervnc-standalone-server tigervnc-common
		else
			echo "Installing legacy vnc4server"
			apt-get -y install vnc4server
		fi
		(echo '$localhost = "no";'
		echo '1;') >> /etc/vnc.conf
		apt-get -y install xfonts-base xterm icewm
	fi
	if ! type vncserver 2>/dev/null
	then
		exit 1
	fi
fi

# Setup vnc password - otherwise vncserver prompts it
vncpasswd <<EOF >/dev/null
password
password
EOF

vncserver -xstartup /usr/bin/xterm :1
DISPLAY=:1 $(dirname $0)/func_tests.sh $@
retcode=$?
vncserver -kill :1 || retcode=$?

exit $retcode
