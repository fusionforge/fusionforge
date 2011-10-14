#!/bin/sh
. tests/scripts/common-functions

export FORGE_HOME=/usr/share/gforge
export DIST=squeeze
get_config $@
prepare_workspace
destroy_vm $@
start_vm_if_not_keeped $@

# Build 3rd-party 
make -C 3rd-party -f Makefile.deb BUILDRESULT=$BUILDRESULT LOCALREPODEB=$WORKSPACE/build/debian BUILDDIST=$DIST DEBMIRROR=$DEBMIRROR botclean botbuild

# Build fusionforge
make -f Makefile.debian BUILDRESULT=$WORKSPACE/build/packages LOCALREPODEB=$WORKSPACE/build/debian rsqueeze

# Transfer preseeding
cat tests/preseed/* | sed s/@FORGE_ADMIN_PASSWORD@/$FORGE_ADMIN_PASSWORD/ | ssh root@$HOST "LANG=C debconf-set-selections"

# Setup debian repo
ssh root@$HOST "echo \"deb $DEBMIRROR $DIST main\" > /etc/apt/sources.list"
ssh root@$HOST "echo \"deb $DEBMIRRORSEC $DIST/updates main\" > /etc/apt/sources.list.d/security.list"

ssh root@$HOST "echo \"deb file:/debian $DIST main\" >> /etc/apt/sources.list"
scp -r $WORKSPACE/build/debian root@$HOST:/ 
gpg --export --armor | ssh root@$HOST "apt-key add -"
sleep 5
ssh root@$HOST "apt-get update"

# Install fusionforge
ssh root@$HOST "UCF_FORCE_CONFFNEW=yes DEBIAN_FRONTEND=noninteractive LANG=C apt-get -y --force-yes install rsync postgresql-contrib fusionforge-full"
echo "Set forge admin password"
ssh root@$HOST "/usr/share/gforge/bin/forge_set_password $FORGE_ADMIN_USERNAME $FORGE_ADMIN_PASSWORD"
ssh root@$HOST "LANG=C a2dissite default ; LANG=C invoke-rc.d apache2 reload"
ssh root@$HOST "(echo [core];echo use_ssl=no) > /etc/gforge/config.ini.d/zzz-buildbot.ini"

# Dump database
echo "Dump freshly installed database"
ssh root@$HOST "su - postgres -c \"pg_dumpall\" > /root/dump"

# Stop cron
echo "Stop cron daemon"
ssh root@$HOST "invoke-rc.d cron stop" || true

# Install selenium tests
ssh root@$HOST mkdir $FORGE_HOME/tests
cp 3rd-party/selenium/selenium-server.jar tests/
rsync -a --delete tests/ root@$HOST:$FORGE_HOME/tests/

ssh root@$HOST "cat > $FORGE_HOME/tests/config/phpunit" <<-EOF
HUDSON_URL=$HUDSON_URL
JOB_NAME=$JOB_NAME
EOF

# Run tests
retcode=0
echo "Run phpunit test on $HOST in $FORGE_HOME"
if xterm -e "sh -c exit" 2>/dev/null
then
        ssh -X root@$HOST "$FORGE_HOME/tests/scripts/phpunit.sh DEBDebian60Tests.php" || retcode=$?
        rsync -av root@$HOST:/var/log/ $WORKSPACE/reports/
else
        echo "No display is available, NOT RUNNING TESTS"
        retcode=2
fi

stop_vm_if_not_keeped $@
exit $retcode

