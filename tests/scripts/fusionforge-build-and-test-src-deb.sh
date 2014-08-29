#!/bin/sh
. tests/scripts/common-functions
. tests/scripts/common-vm

set -e

get_config

export HOST=$1
if [ -z "$HOST" ]; then
    echo "Usage: $0 vm_hostname"
    exit 1
fi
case $HOST in
    debian7.local)
	export DIST=wheezy
	VM=debian7
	;;
    debian8.local)
	export DIST=jessie
	VM=debian8
	;;
    *)
	export DIST=jessie
	VM=debian8
	;;
esac	

tests/scripts/start_vm -t $VM $@

# LXC post-install...
ssh root@$HOST "echo \"deb $DEBMIRRORSEC $DIST/updates main\" > /etc/apt/sources.list.d/security.list"
ssh root@$HOST "echo 'APT::Install-Recommends \"false\";' > /etc/apt/apt.conf.d/01InstallRecommends"
ssh root@$HOST "apt-get update"

ssh root@$HOST "apt-get install -y rsync"
rsync -av --delete src tests root@$HOST:/usr/src/fusionforge/
#ssh root@$HOST "/usr/src/fusionforge/tests/scripts/deb/build.sh"
ssh root@$HOST "/usr/src/fusionforge/tests/scripts/deb/install-src.sh"

# Run tests
retcode=0
echo "Run phpunit test on $HOST"
ssh root@$HOST "/usr/src/fusionforge/tests/func/vncxstartsuite.sh /usr/src/fusionforge/tests/scripts/deb/run-testsuite.sh src/debian" || retcode=$?

rsync -av root@$HOST:/var/log/ ~/reports/

#stop_vm_if_not_keeped -t $VM $@
exit $retcode
