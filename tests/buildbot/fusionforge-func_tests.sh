#!/bin/bash
# Create an LXC container and launch the functional testsuite
. $(dirname $0)/common-functions

set -ex

copy_logs() {
    rsync -av -e 'ssh -o "StrictHostKeyChecking=no"' root@$HOST:/var/log/ $WORKSPACE/reports/
}
trap copy_logs ERR

start_vm() {
    HOST=$1
    if ! $KEEPVM; then
	# Destroy the VM if found
	destroy_vm $1 || true
    fi
    sudo /usr/local/sbin/lxc-wrapper start $(basename $HOST .local)

    echo "Waiting for $HOST to come up..."
    i=0
    # Done in this script rather than lxc-wrapper, because we have the SSH key
    while [ $i -lt 10 ] && ! ssh -o 'StrictHostKeyChecking=no' root@$HOST uname -a >/dev/null; do
	sleep 10
	i=$(($i+1))
	echo -n .
    done
    if [ $i -lt 10 ] ; then
	echo " OK"
    else
	echo " FAIL"
	exit 1
    fi
}

stop_vm() {
    HOST=$1
    if ! $KEEPVM; then
	sudo /usr/local/sbin/lxc-wrapper stop $(basename $HOST .local)
    fi
}

destroy_vm() {
    HOST=$1
    sudo /usr/local/sbin/lxc-wrapper destroy $(basename $HOST .local)
}


setup_epel_repo() {
    if [ -z "$HOST" ] ; then  echo "HOST undefined" ;exit 1; fi
    # EPEL REPO
    if [ ! -z "$EPEL_REPO" ] ; then
        echo "Installing specific EPEL REPO $EPEL_REPO"
	ssh -o 'StrictHostKeyChecking=no' root@$HOST "cat > /etc/yum.repos.d/epel.repo" <<-EOF
# Name: EPEL RPM Repository for Red Hat Enterprise \$releasever - epel
# URL: http://fedoraproject.org/wiki/EPEL
[epel]
name=Extra Packages for Enterprise Linux \$releasever - \$basearch 
baseurl=$EPEL_REPO/\$releasever/\$basearch
#mirrorlist=http://mirrors.fedoraproject.org/mirrorlist?repo=epel\$releasever&arch=\$basearch
failovermethod=priority
enabled=0
gpgcheck=0
EOF
    else
        echo "Installing standard EPEL REPO"
	ssh -o 'StrictHostKeyChecking=no' root@$HOST yum install -y epel-release
    fi
}

setup_epel_testing_repo() {
    if [ -z "$HOST" ] ; then  echo "HOST undefined" ;exit 1; fi
    # EPEL Testing REPO
    echo "Installing EPEL Testing REPO"
    ssh -o 'StrictHostKeyChecking=no' root@$HOST "cat > /etc/yum.repos.d/epel-testing.repo" <<-EOF
# Name: EPEL RPM Repository for Red Hat Enterprise Testing \$releasever - epel
# URL: http://fedoraproject.org/wiki/EPEL
[epel-testing]
name=Extra Packages for Enterprise Linux Testing \$releasever - \$basearch 
mirrorlist=http://mirrors.fedoraproject.org/mirrorlist?repo=testing-epel\$releasever&arch=\$basearch
failovermethod=priority
enabled=0
gpgcheck=0
EOF
}


get_config
prepare_workspace

export HOST=$1
shift
if [ -z "$HOST" ]; then
    echo "Usage: $0 vm_hostname"
    exit 1
fi
case $HOST in
    debian9.local)
	export DIST=stretch
	VM=debian9
	INSTALL_OS=debian
	;;
    debian10.local)
	export DIST=buster
	VM=debian10
	INSTALL_OS=debian
	;;
    debian11.local)
	export DIST=bullseye
	VM=debian11
	INSTALL_OS=debian
	;;
    centos7.local)
	VM=centos7
	INSTALL_OS=centos
	;;
    centos8.local)
	VM=centos8
	INSTALL_OS=centos
	;;
    *)
	export DIST=stretch
	VM=debian9
	INSTALL_OS=debian
	;;
esac

INSTALL_METHOD=$1
case $INSTALL_METHOD in
    src)
	shift
	;;
    deb)
	shift
	;;
    rpm)
	shift
	;;
    *)
	INSTALL_METHOD=src
	;;
esac

start_vm $HOST

# LXC post-install...
if [ $INSTALL_OS == "debian" ]; then
    ssh -o 'StrictHostKeyChecking=no' root@$HOST "echo \"deb $DEBMIRRORSEC $DIST/updates main\" > /etc/apt/sources.list.d/security.list"
    ssh -o 'StrictHostKeyChecking=no' root@$HOST "echo 'APT::Install-Recommends \"false\";' > /etc/apt/apt.conf.d/01InstallRecommends"
    ssh -o 'StrictHostKeyChecking=no' root@$HOST "apt-get update" || true
fi

if [ $INSTALL_OS == "debian" ]; then
    ssh -o 'StrictHostKeyChecking=no' root@$HOST "apt-get install -y rsync haveged"
else
    ssh -o 'StrictHostKeyChecking=no' root@$HOST "yum install -y rsync"
    setup_epel_repo
    ssh -o 'StrictHostKeyChecking=no' root@$HOST "yum --enablerepo=epel install -y haveged"
fi
rsync -e "ssh -o StrictHostKeyChecking=no" -av --delete autoinstall src tests root@$HOST:/usr/src/fusionforge/
if [ $INSTALL_METHOD = "src" ]; then
    ssh -o 'StrictHostKeyChecking=no' root@$HOST "/usr/src/fusionforge/autoinstall/install-src.sh"
else
    ssh -o 'StrictHostKeyChecking=no' root@$HOST "/usr/src/fusionforge/autoinstall/build.sh"
    ssh -o 'StrictHostKeyChecking=no' root@$HOST "/usr/src/fusionforge/autoinstall/install.sh"
fi

# Run tests
retcode=0
echo "Run phpunit test on $HOST"
echo "export JOB_URL=$JOB_URL" | ssh -o 'StrictHostKeyChecking=no' root@$HOST tee -a .bashrc
  ssh -o 'StrictHostKeyChecking=no' root@$HOST "TESTGLOB=$TESTGLOB /usr/src/fusionforge/tests/func_tests-xvnc.sh $INSTALL_METHOD/$INSTALL_OS $*" || retcode=$?

copy_logs

if [ $retcode = 0 ] ; then
    case $INSTALL_METHOD in
	deb)
	    rsync -e "ssh -o StrictHostKeyChecking=no" -av --delete root@$HOST:/usr/src/debian-repository/local/ $WORKSPACE/packages/
	    ;;
	rpm)
	    rsync -e "ssh -o StrictHostKeyChecking=no" -av --delete root@$HOST:/usr/src/fusionforge/build/RPMS/ $WORKSPACE/packages/
	    ;;
    esac
fi

stop_vm $HOST
exit $retcode
