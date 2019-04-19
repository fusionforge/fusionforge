#!/bin/bash
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
	    apt-get -y install xfonts-base
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

vncserver :1
DISPLAY=:1 $(dirname $0)/func_tests.sh $@
retcode=$?
vncserver -kill :1 || retcode=$?

exit $retcode
