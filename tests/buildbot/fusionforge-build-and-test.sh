#!/bin/bash
. $(dirname $0)/common-functions
. $(dirname $0)/common-vm

set -ex

get_config
prepare_workspace

export HOST=$1
if [ -z "$HOST" ]; then
    echo "Usage: $0 vm_hostname"
    exit 1
fi
case $HOST in
    debian7.local)
	export DIST=wheezy
	VM=debian7
	INSTALL_OS=debian
	;;
    debian8.local)
	export DIST=jessie
	VM=debian8
	INSTALL_OS=debian
	;;
    centos5.local)
	VM=centos5
	INSTALL_OS=centos
	;;
    centos6.local)
	VM=centos6
	INSTALL_OS=centos
	;;
    centos7.local)
	VM=centos7
	INSTALL_OS=centos
	;;
    *)
	export DIST=jessie
	VM=debian8
	INSTALL_OS=debian
	;;
esac

INSTALL_METHOD=$2
if [ -z "$INSTALL_METHOD" ]; then INSTALL_METHOD='src'; fi

destroy_vm_if_not_kept -t $VM $HOST
start_vm_if_not_kept -t $VM $HOST

# LXC post-install...
if [ $INSTALL_OS == "debian" ]; then
    ssh root@$HOST "echo \"deb $DEBMIRRORSEC $DIST/updates main\" > /etc/apt/sources.list.d/security.list"
    ssh root@$HOST "echo 'APT::Install-Recommends \"false\";' > /etc/apt/apt.conf.d/01InstallRecommends"
    ssh root@$HOST "apt-get update"
fi

if [ $INSTALL_OS == "debian" ]; then
    ssh root@$HOST "apt-get install -y rsync"
else
    ssh root@$HOST "yum install -y rsync"
fi
rsync -av --delete autoinstall src tests root@$HOST:/usr/src/fusionforge/
if [ $INSTALL_METHOD = "src" ]; then
    ssh root@$HOST "/usr/src/fusionforge/autoinstall/install-src.sh"
else
    ssh root@$HOST "/usr/src/fusionforge/autoinstall/build.sh"
    ssh root@$HOST "/usr/src/fusionforge/autoinstall/install.sh"
fi

# Run tests
retcode=0
echo "Run phpunit test on $HOST"
#ssh root@$HOST "TESTGLOB='func/50_PluginsScmBzr/*' /usr/src/fusionforge/autoinstall/vnc-run-testsuite.sh /usr/src/fusionforge/autoinstall/run-testsuite.sh $INSTALL_METHOD/$INSTALL_OS" || retcode=$?
ssh root@$HOST "/usr/src/fusionforge/autoinstall/vnc-run-testsuite.sh /usr/src/fusionforge/autoinstall/run-testsuite.sh $INSTALL_METHOD/$INSTALL_OS" || retcode=$?

rsync -av root@$HOST:/var/log/ $WORKSPACE/reports/

stop_vm_if_not_kept -t $VM $@
exit $retcode
