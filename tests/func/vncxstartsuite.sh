#! /bin/sh

# Check vncserver
if ! type vncserver 2>/dev/null
then
	echo "Installing vncserver"
	if type yum 2>/dev/null
	then
		yum install -y tigervnc-server
	fi
	if type apt-get 2>/dev/null
	then
		apt-get -y install vnc4server
	fi
	if ! type vncserver 2>/dev/null
	then
		exit 1
	fi
fi

if type apt-get 2>/dev/null ; then
    apt-get -y install xfonts-base
fi

# Setup vnc password - otherwise vncserver prompts it
vncpasswd <<EOF
password
password
EOF

# Setup ssh key and parameters
cd
mkdir -p .ssh
if ! [ -e .ssh/id_rsa.pub ] ; then
    ssh-keygen -f .ssh/id_rsa -N ''
    cat .ssh/id_rsa.pub >> .ssh/authorized_keys
fi
if ! [ -e .ssh/config ] || ! grep -q StrictHostKeyChecking .ssh/config ; then
    echo StrictHostKeyChecking no >> .ssh/config
fi

vncserver :1
DISPLAY=:1 $@
retcode=$?
vncserver -kill :1 || retcode=$?

exit $retcode
