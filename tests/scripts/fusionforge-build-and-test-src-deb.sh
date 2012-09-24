#!/bin/sh
. tests/scripts/common-functions
. tests/scripts/common-vm

export FORGE_HOME=/opt/gforge
export DIST=wheezy
#export FILTER="-filter func/PluginsMoinMoin/moinmoinTest.php"

get_config $@
prepare_workspace
#destroy_vm -t debian7 $@
start_vm_if_not_keeped -t debian7 $@

# Build 3rd-party 
# make -C 3rd-party -f Makefile.debian BUILDRESULT=$BUILDRESULT LOCALREPODEB=$WORKSPACE/build/debian BUILDDIST=$DIST DEBMIRROR=$DEBMIRROR botclean botbuild

# Setup debian repo
# ssh root@$HOST "echo \"deb $DEBMIRROR $DIST main\" > /etc/apt/sources.list"
# ssh root@$HOST "echo \"deb $DEBMIRRORSEC $DIST/updates main\" > /etc/apt/sources.list.d/security.list"
# ssh root@$HOST "echo \"deb file:/debian $DIST main\" >> /etc/apt/sources.list"
# scp -r $WORKSPACE/build/debian root@$HOST:/
# gpg --export --armor | ssh root@$HOST "apt-key add -"
# sleep 5

ssh root@$HOST "apt-get update"

echo "Sync code on root@$HOST:$FORGE_HOME"
#ssh root@$HOST mkdir -p $FORGE_HOME
rsync -a --delete . root@$HOST:$FORGE_HOME

ssh root@$HOST "cat > $FORGE_HOME/tests/config/phpunit" <<-EOF
HUDSON_URL=$HUDSON_URL
JOB_NAME=$JOB_NAME
EOF

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

# Run tests
retcode=0
echo "Run phpunit test on $HOST"

ssh root@$HOST "apt-get -y install vnc4server ; mkdir -p /root/.vnc"
ssh root@$HOST "cat > /root/.vnc/xstartup ; chmod +x /root/.vnc/xstartup" <<EOF
#! /bin/bash
: > /root/phpunit.exitcode
$FORGE_HOME/tests/scripts/phpunit.sh $FILTER DEBDebian70Tests.php &> /var/log/phpunit.log &
echo \$! > /root/phpunit.pid
wait %1
echo \$? > /root/phpunit.exitcode
EOF
ssh root@$HOST vncpasswd <<EOF
password
password
EOF
ssh root@$HOST "vncserver :1"
sleep 5
pid=$(ssh root@$HOST cat /root/phpunit.pid)
ssh root@$HOST "tail -f /var/log/phpunit.log --pid=$pid"
sleep 5
retcode=$(ssh root@$HOST cat /root/phpunit.exitcode)
rsync -av root@$HOST:/var/log/ $WORKSPACE/reports/
scp root@$HOST:/tmp/gforge-*.log $WORKSPACE/reports/
ssh root@$HOST "vncserver -kill :1" || retcode=$?

stop_vm_if_not_keeped -t debian7 $@
exit $retcode
