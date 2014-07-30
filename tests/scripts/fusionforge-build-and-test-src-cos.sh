#!/bin/sh -e
. tests/scripts/common-functions
. tests/scripts/common-vm

get_config

export FORGE_HOME=/opt/gforge
export HOST=$1
export FILTER="TarCentosTests.php"

case $HOST in
    centos5.local)
	VM=centos5
	;;
    centos6.local)
	VM=centos6
	;;
    *)
	VM=centos6
	;;
esac	

prepare_workspace
destroy_vm -t $VM $HOST
start_vm_if_not_keeped -t $VM $HOST

setup_redhat_3rdparty_repo

#[ ! -e $HOME/doxygen-1.6.3/bin/doxygen ] || make build-doc DOCSDIR=$WORKSPACE/apidocs DOXYGEN=$HOME/doxygen-1.6.3/bin/doxygen
#make BUILDRESULT=$WORKSPACE/build/packages buildtar
#make -f Makefile.rh BUILDRESULT=$WORKSPACE/build/packages src

setup_dag_repo $@
if [ $VM = centos6 ] ; then
    setup_epel_repo $@
fi

echo "Create $FORGE_HOME if necessary"
ssh root@$HOST "[ -d $FORGE_HOME ] || mkdir -p $FORGE_HOME"

echo "Sync code on root@$HOST:$FORGE_HOME"
rsync -a --delete src/ root@$HOST:$FORGE_HOME/

echo "Run Install on $HOST"
ssh root@$HOST "$FORGE_HOME/install-ng --auto --reinit"

echo "Dump freshly installed database"
ssh root@$HOST "su - postgres -c \"pg_dumpall\" > /root/dump"

config_path=$(ssh root@$HOST forge_get_config config_path)

echo "Set use_ssl=no"
ssh root@$HOST "(echo [core];echo use_ssl=no;echo use_fti=no) > $config_path/config.ini.d/zzz-zbuildbot.ini"
ssh root@$HOST "(echo [moinmoin];echo use_frame=no) >> $config_path/config.ini.d/zzz-buildbot.ini"
ssh root@$HOST "(echo [mediawiki];echo unbreak_frames=yes) >> $config_path/config.ini.d/zzz-buildbot.ini"

#  Install a fake sendmail to catch all outgoing emails.
# ssh root@$HOST "perl -spi -e s#/usr/sbin/sendmail#/opt/tests/scripts/catch_mail.php# $config_path/local.inc"

echo "Stop cron daemon"
ssh root@$HOST "service crond stop" || true

if [ $VM = centos6 ] ; then
    ssh root@$HOST "yum -y --enablerepo=epel install php-phpunit-PHPUnit-Selenium"
    ssh root@$HOST "yum -y remove mod_ssl ; service httpd restart"
fi

# Install selenium
ssh root@$HOST "yum -y install selenium"

# Install selenium tests
rsync -a --delete tests/ root@$HOST:$FORGE_HOME/tests/

# Transfer hudson config
ssh root@$HOST "cat > $FORGE_HOME/tests/config/phpunit" <<-EOF
HUDSON_URL=$HUDSON_URL
JOB_NAME=$JOB_NAME
EOF

# Run tests
retcode=0
echo "Run phpunit test on $HOST in $FORGE_HOME"
ssh root@$HOST "$FORGE_HOME/tests/func/vncxstartsuite.sh $FILTER" || retcode=$?
rsync -av root@$HOST:/var/log/ $WORKSPACE/reports/
scp root@$HOST:/tmp/gforge-*.log $WORKSPACE/reports/

stop_vm_if_not_keeped -t $VM $@
exit $retcode
