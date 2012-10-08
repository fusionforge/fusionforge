#!/bin/sh
. tests/scripts/common-functions
. tests/scripts/common-vm

export FORGE_HOME=/opt/gforge
export DIST=wheezy
#export FILTER="func/PluginsMediawiki/mediawikiTest.php"
export FILTER="DEBDebian70Tests.php"
#export FILTER="func/PluginsMoinMoin/moinmoinTest.php"

get_config $@
prepare_workspace
destroy_vm -t debian7 $@
start_vm_if_not_keeped -t debian7 $@

setup_debian_3rdparty_repo

ssh root@$HOST "apt-get update"

echo "Sync code on root@$HOST:$FORGE_HOME"
#ssh root@$HOST mkdir -p $FORGE_HOME
rsync -a --delete . root@$HOST:$FORGE_HOME

echo "Run Install on $HOST"
ssh root@$HOST "$FORGE_HOME/src/install-ng --auto --reinit"

# Dump database
echo "Dump freshly installed database"
ssh root@$HOST "su - postgres -c \"pg_dumpall\" > /root/dump"

echo "Set use_ssl=no"
ssh root@$HOST "(echo [core];echo use_ssl=no;echo use_fti=no) > /etc/gforge/config.ini.d/zzz-zbuildbot.ini"
ssh root@$HOST "(echo [moinmoin];echo use_frame=no) >> /etc/gforge/config.ini.d/zzz-buildbot.ini"
ssh root@$HOST "(echo [mediawiki];echo unbreak_frames=yes) >> /etc/gforge/config.ini.d/zzz-buildbot.ini"

# Stop cron
echo "Stop cron daemon"
ssh root@$HOST "service crond stop" || true

# Copy selenium
make -C 3rd-party/selenium selenium-server.jar
rsync -a 3rd-party/selenium/selenium-server.jar root@$HOST:$FORGE_HOME/tests/selenium-server.jar

# Transfer hudson config
ssh root@$HOST "cat > $FORGE_HOME/tests/config/phpunit" <<-EOF
HUDSON_URL=$HUDSON_URL
JOB_NAME=$JOB_NAME
EOF

# Run tests
retcode=0
echo "Run phpunit test on $HOST in $FORGE_HOME"
ssh root@$HOST "$FORGE_HOME/tests/func/vncxstartsuite.sh $FILTER"
retcode=$?
rsync -av root@$HOST:/var/log/ $WORKSPACE/reports/

stop_vm_if_not_keeped -t debian7 $@
exit $retcode
