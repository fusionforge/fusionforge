#!/bin/sh
. tests/scripts/common-functions
. tests/scripts/common-vm

export FORGE_HOME=/opt/gforge
get_config $@
prepare_workspace
destroy_vm -t centos5 $@
start_vm_if_not_keeped -t centos5 $@

#[ ! -e $HOME/doxygen-1.6.3/bin/doxygen ] || make build-doc DOCSDIR=$WORKSPACE/apidocs DOXYGEN=$HOME/doxygen-1.6.3/bin/doxygen
#make BUILDRESULT=$WORKSPACE/build/packages buildtar
#make -f Makefile.rh BUILDRESULT=$WORKSPACE/build/packages src

# BUILD FUSIONFORGE REPO
echo "Build FUSIONFORGE REPO in $BUILDRESULT"
make -C 3rd-party -f Makefile.rh BUILDRESULT=$BUILDRESULT

setup_ff_repo $@
setup_dag_repo $@

echo "Sync code on root@$HOST:$FORGE_HOME"
rsync -a --delete . root@$HOST:$FORGE_HOME

ssh root@$HOST "cat > $FORGE_HOME/tests/config/phpunit" <<-EOF
HUDSON_URL=$HUDSON_URL
JOB_NAME=$JOB_NAME
EOF

echo "Run Install on $HOST"
ssh root@$HOST "$FORGE_HOME/src/install-ng --auto --reinit"

echo "Dump freshly installed database"
ssh root@$HOST "su - postgres -c \"pg_dumpall\" > /root/dump"

echo "Set use_ssl=no"
ssh root@$HOST "(echo [core];echo use_ssl=no;echo use_fti=no) > /etc/gforge/config.ini.d/zzz-zbuildbot.ini"

#  Install a fake sendmail to catch all outgoing emails.
# ssh root@$HOST "perl -spi -e s#/usr/sbin/sendmail#/opt/tests/scripts/catch_mail.php# /etc/gforge/local.inc"

echo "Stop cron daemon"
ssh root@$HOST "service crond stop" || true

# Copy selenium
make -C 3rd-party/selenium selenium-server.jar
rsync -a 3rd-party/selenium/selenium-server.jar root@$HOST:$FORGE_HOME/tests/selenium-server.jar

# Run tests
retcode=0
echo "Run phpunit test on $HOST in $FORGE_HOME"
if xterm -e "sh -c exit" 2>/dev/null
then
        ssh -X root@$HOST "$FORGE_HOME/tests/scripts/phpunit.sh TarCentosTests.php" || retcode=$?
        rsync -av root@$HOST:/var/log/ $WORKSPACE/reports/
	scp root@$HOST:/tmp/gforge-*.log $WORKSPACE/reports/
else
        echo "No display is available, NOT RUNNING TESTS"
        retcode=2
fi

stop_vm_if_not_keeped -t centos5 $@
exit $retcode
