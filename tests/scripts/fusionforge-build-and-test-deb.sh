#!/bin/sh

set -e
set -x

. tests/scripts/common-functions
. tests/scripts/common-vm

get_config

export FORGE_HOME=/usr/share/gforge
export DIST=wheezy
export HOST=$1

prepare_workspace
destroy_vm -t debian7 $HOST
start_vm_if_not_keeped -t debian7 $HOST

CHECKOUTPATH=$(pwd)

COWBUILDERBASE=/var/lib/jenkins/builder/
COWBUILDERCONFIG=$COWBUILDERBASE/config/$DIST.config

cd $CHECKOUTPATH/src
PKGNAME=$(dpkg-parsechangelog | awk '/^Source:/ { print $2 }')
PKGVERS=$(dpkg-parsechangelog | awk '/^Version:/ { print $2 }')
MAJOR=${PKGVERS%-*}
SMAJOR=${MAJOR#*:}
MINOR=${PKGVERS##*-}
if [ -d $CHECKOUTPATH/.svn ] ; then
    MINOR=-$MINOR+svn$(svn info | awk '/^Revision:/ { print $2 }')
elif [ -d $CHECKOUTPATH/.bzr ] ; then
    MINOR=-$MINOR+bzr$(bzr revno)
elif [ -d $CHECKOUTPATH/.git ] ; then
    MINOR=-$MINOR+git$(git describe --always)
else
    MINOR=-$MINOR+$(TZ=UTC date +%Y%m%d%H%M%S)
fi
ARCH=$(dpkg-architecture -qDEB_BUILD_ARCH)

dch -b -v $MAJOR$MINOR -D UNRELEASED "This is $DIST-$ARCH autobuild"
sed -i -e "1s/UNRELEASED/$DIST/" debian/changelog
pdebuild --configfile $COWBUILDERCONFIG

CHANGEFILE=${PKGNAME}_$SMAJOR${MINOR}_$ARCH.changes

cd $BUILDRESULT
REPOPATH=$WORKSPACE/build/debian

rm -r $REPOPATH
mkdir -p $REPOPATH/conf
cat > $REPOPATH/conf/distributions <<EOF
Codename: $DIST
Suite: $DIST
Components: main
UDebComponents: main
Architectures: amd64 i386 source
Origin: buildbot.fusionforge.org
Description: FusionForge autobuilt repository
SignWith: buildbot@$(hostname -f)
EOF

reprepro -Vb $REPOPATH include $DIST $CHANGEFILE

# Build 3rd-party 
# make -C 3rd-party -f Makefile.debian BUILDRESULT=$BUILDRESULT LOCALREPODEB=$WORKSPACE/build/debian BUILDDIST=$DIST DEBMIRROR=$DEBMIRROR botclean botbuild

# Build fusionforge
# make -f Makefile.debian BUILDRESULT=$WORKSPACE/build/packages LOCALREPODEB=$WORKSPACE/build/debian rwheezy

cd $CHECKOUTPATH
# Transfer preseeding
# cat tests/preseed/* | sed s/@FORGE_ADMIN_PASSWORD@/$FORGE_ADMIN_PASSWORD/ | ssh root@$HOST "LANG=C debconf-set-selections"

# Setup debian repo
export DEBMIRROR DEBMIRRORSEC
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
make -C 3rd-party/selenium selenium-server.jar
cp 3rd-party/selenium/selenium-server.jar tests/
rsync -a --delete tests/ root@$HOST:$FORGE_HOME/tests/

ssh root@$HOST "cat > $FORGE_HOME/tests/config/phpunit" <<-EOF
HUDSON_URL=$HUDSON_URL
JOB_NAME=$JOB_NAME
EOF

# Run tests
retcode=0
echo "Run phpunit test on $HOST in $FORGE_HOME"

ssh root@$HOST "apt-get -y install vnc4server ; mkdir -p /root/.vnc"
ssh root@$HOST "cat > /root/.vnc/xstartup ; chmod +x /root/.vnc/xstartup" <<EOF
#! /bin/bash
: > /root/phpunit.exitcode
$FORGE_HOME/tests/scripts/phpunit.sh DEBDebian70Tests.php &> /var/log/phpunit.log &
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
ssh root@$HOST "vncserver -kill :1" || retcode=$?

stop_vm_if_not_keeped -t debian7 $@
exit $retcode
