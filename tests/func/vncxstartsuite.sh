#! /bin/sh

TEST_SUITE="$1"
# Test arg
if [ -z "$TEST_SUITE" ]
then
	echo "Usage : $0 <TEST_SUITE>"
	exit 1
fi

TEST_ENV="$2"
INSTALL_METHOD=${TEST_ENV%/*}
INSTALL_OS=${TEST_ENV#*/}

# Find $TEST_HOME
relativescriptpath=`dirname $0`
TEST_HOME=$(cd $relativescriptpath/..;pwd)

if [ ! -f "$TEST_HOME/$TEST_SUITE" ]
then
	echo "Test suite $TEST_SUITE not found"
	exit 2
fi

# Check vncserver
if ! type vncserver 2>/dev/null
then
	echo "vncserver is missing"
	if type yum 2>/dev/null
	then
		yum install -y vnc-server
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

[ -d "/root/.vnc" ] || mkdir /root/.vnc 

# Setup X11 to start phpunit
cat > /root/.vnc/xstartup<<EOF
#! /bin/bash
: > /root/phpunit.exitcode
INSTALL_METHOD=$INSTALL_METHOD INSTALL_OS=$INSTALL_OS $TEST_HOME/scripts/phpunit.sh $TEST_SUITE &> /var/log/phpunit.log &
echo \$! > /root/phpunit.pid
wait %1
echo \$? > /root/phpunit.exitcode
EOF
chmod +x /root/.vnc/xstartup

# Setup vnc password
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

# Start vnc server (that will start phpunit)
vncserver :1
sleep 5
pid=$(cat /root/phpunit.pid)
tail -f /var/log/phpunit.log --pid=$pid
#wait $pid
sleep 5

retcode=$(cat /root/phpunit.exitcode)
vncserver -kill :1 || retcode=$?

exit $retcode
