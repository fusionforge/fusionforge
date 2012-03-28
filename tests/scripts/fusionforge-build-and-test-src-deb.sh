#!/bin/sh
. tests/scripts/common-functions
. tests/scripts/common-vm

export FORGE_HOME=/opt/gforge
export DIST=squeeze
get_config $@
prepare_workspace
destroy_vm -t debian6 $@
start_vm_if_not_keeped -t debian6 $@

# Build 3rd-party 
make -C 3rd-party -f Makefile.debian BUILDRESULT=$BUILDRESULT LOCALREPODEB=$WORKSPACE/build/debian BUILDDIST=$DIST DEBMIRROR=$DEBMIRROR botclean botbuild

# Setup debian repo
ssh root@$HOST "echo \"deb $DEBMIRROR $DIST main\" > /etc/apt/sources.list"
ssh root@$HOST "echo \"deb $DEBMIRRORSEC $DIST/updates main\" > /etc/apt/sources.list.d/security.list"

ssh root@$HOST "echo \"deb file:/debian $DIST main\" >> /etc/apt/sources.list"
scp -r $WORKSPACE/build/debian root@$HOST:/
gpg --export --armor | ssh root@$HOST "apt-key add -"
sleep 5
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

# Stop cron
echo "Stop cron daemon"
ssh root@$HOST "service crond stop" || true

# Copy selenium
rsync -a 3rd-party/selenium/selenium-server.jar root@$HOST:$FORGE_HOME/tests/selenium-server.jar

# Run tests
retcode=0
echo "Run phpunit test on $HOST"
if xterm -e "sh -c exit" 2>/dev/null
then
	ssh -X root@$HOST "$FORGE_HOME/tests/scripts/phpunit.sh DEBDebian60Tests.php" || retcode=$?
	rsync -av root@$HOST:/var/log/ $WORKSPACE/reports/
else
	echo "No display is available, NOT RUNNING TESTS"
	retcode=2
fi

stop_vm_if_not_keeped -t debian6 $@
return $retcode
