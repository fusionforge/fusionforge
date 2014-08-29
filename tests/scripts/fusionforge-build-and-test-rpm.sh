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
    centos6.local)
	VM=centos6
	;;
    *)
	VM=centos6
	;;
esac	

destroy_vm_if_not_keeped -t $VM $HOST
start_vm_if_not_keeped -t $VM $HOST

ssh root@$HOST "yum install -y rsync"
rsync -av --delete src tests root@$HOST:/usr/src/fusionforge/
ssh root@$HOST "/usr/src/fusionforge/tests/scripts/rpm/build.sh"
ssh root@$HOST "/usr/src/fusionforge/tests/scripts/rpm/install.sh"

# Run tests
retcode=0
echo "Run phpunit test on $HOST in $FORGE_HOME"
ssh root@$HOST "/usr/src/fusionforge/tests/func/vncxstartsuite.sh /usr/src/fusionforge/tests/scripts/rpm/run-testsuite.sh" || retcode=$?

rsync -av root@$HOST:/var/log/ $WORKSPACE/reports/

stop_vm_if_not_keeped -t $VM $@
exit $retcode
