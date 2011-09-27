#!/bin/sh -ex
. tests/scripts/common-functions

export FORGE_HOME=/usr/share/gforge
export DIST=squeeze
get_config $@
prepare_workspace
destroy_vm $@
start_vm_if_not_keeped $@

make -f Makefile.debian BUILDRESULT=$WORKSPACE/build/packages LOCALREPODEB=$WORKSPACE/build/debian clean
(cd 3rd-party/php-mail-mbox ; dpkg-source -x php-mail-mbox_0.6.3-1coclico1.dsc)
(cd 3rd-party/php-mail-mbox ; make -f Makefile.debian BUILDRESULT=$WORKSPACE/build/packages LOCALREPODEB=$WORKSPACE/build/debian rsqueeze)
(cd 3rd-party/selenium ; make getselenium)
make -f Makefile.debian BUILDRESULT=$WORKSPACE/build/packages LOCALREPODEB=$WORKSPACE/build/debian rsqueeze

# Transfer preseeding
cat tests/preseed/* | sed s/@FORGE_ADMIN_PASSWORD@/$FORGE_ADMIN_PASSWORD/ | ssh root@$HOST "LANG=C debconf-set-selections"

# Setup debian repo
ssh root@$HOST "echo \"deb $DEBMIRROR $DIST main\" > /etc/apt/sources.list"
ssh root@$HOST "echo \"deb $DEBMIRRORSEC $DIST/updates main\" > /etc/apt/sources.list.d/security.list"

# Temporary hack to grab libjs-jquery-tipsy from unstable until it reaches testing/backports
if [ $DIST = squeeze ] ; then
    ssh root@$HOST "echo \"deb $DEBMIRROR unstable main\" >> /etc/apt/sources.list"
    ssh root@$HOST "cat >> /etc/apt/apt.conf.d/unstable" <<EOF
APT::Default-Release "$DIST";
EOF
    ssh root@$HOST "cat >> /etc/apt/preferences.d/unstable" <<EOF
Package: *
Pin: release a=unstable
Pin-Priority: 50

EOF
    ssh root@$HOST "apt-get update"
    ssh root@$HOST "apt-get install -y --force-yes -t unstable libjs-jquery-tipsy"
fi

ssh root@$HOST "echo \"deb file:/debian $DIST main\" >> /etc/apt/sources.list"
scp -r $WORKSPACE/build/debian root@$HOST:/ 
gpg --export --armor | ssh root@$HOST "apt-key add -"
sleep 5
ssh root@$HOST "apt-get update"
ssh root@$HOST "UCF_FORCE_CONFFNEW=yes DEBIAN_FRONTEND=noninteractive LANG=C apt-get -y --force-yes install rsync postgresql-contrib fusionforge-full"
echo "Set forge admin password"
ssh root@$HOST "/usr/share/gforge/bin/forge_set_password $FORGE_ADMIN_USERNAME $FORGE_ADMIN_PASSWORD"
ssh root@$HOST "LANG=C a2dissite default ; LANG=C invoke-rc.d apache2 reload"
ssh root@$HOST "(echo [core];echo use_ssl=no) > /etc/gforge/config.ini.d/zzz-builbot.ini"
#ssh root@$HOST "su - postgres -c \"pg_dump -Fc $DB_NAME\" > /root/dump"
ssh root@$HOST "su - postgres -c \"pg_dumpall\" > /root/dump"
ssh root@$HOST "invoke-rc.d cron stop" || true

ssh root@$HOST mkdir $FORGE_HOME/tests
cp 3rd-party/selenium/selenium-server.jar tests/
rsync -a --delete tests/ root@$HOST:$FORGE_HOME/tests/

ssh root@$HOST "cat > $FORGE_HOME/tests/config/phpunit" <<-EOF
HUDSON_URL=$HUDSON_URL
JOB_NAME=$JOB_NAME
EOF

retcode=0
echo "Run phpunit test on $HOST in $FORGE_HOME"
if xterm -e "sh -c exit" 2>/dev/null
then
        ssh -X root@$HOST "$FORGE_HOME/tests/scripts/phpunit.sh DEBDebian60Tests.php" || retcode=$?
        rsync -av root@$HOST:/var/log/ $WORKSPACE/reports/log/
else
        echo "No display is available, NOT RUNNING TESTS"
        retcode=2
fi

stop_vm_if_not_keeped $@
exit $retcode

