#!/bin/sh
. tests/scripts/common-functions
. tests/scripts/common-vm

set -e

get_config

export FORGE_HOME=/usr/share/gforge
export DIST=wheezy
export HOST=$1
#export FILTER="func/PluginsMediawiki/mediawikiTest.php"
export FILTER="DEBDebian70Tests.php"
#export FILTER="func/PluginsMoinMoin/moinmoinTest.php"

prepare_workspace

CHECKOUTPATH=$(pwd)

COWBUILDERCONFIG=$BUILDERDIR/config/$DIST.config

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

# Build out of the source tree
. $COWBUILDERCONFIG
CHANGEFILE=${BUILDRESULT}/${PKGNAME}_${SMAJOR}${MINOR}_${ARCH}.changes
cd $CHECKOUTPATH
rm -rf $BUILDPLACE/$PKGNAME-$MAJOR
cp -r src/ $BUILDPLACE/$PKGNAME-$MAJOR
cd $BUILDPLACE/$PKGNAME-$MAJOR
dch -b -v $MAJOR$MINOR -D UNRELEASED "This is $DIST-$ARCH autobuild"
sed -i -e "1s/UNRELEASED/$DIST/" debian/changelog
pdebuild --configfile $COWBUILDERCONFIG --buildresult $BUILDRESULT

cd $BUILDRESULT
REPOPATH=$WORKSPACE/build/debian

[ ! -d $REPOPATH ] || rm -r $REPOPATH
mkdir -p $REPOPATH/conf
DEFAULTKEY=buildbot@$(hostname -f)
SIGNKEY=${DEBEMAIL:-$DEFAULTKEY}
cat > $REPOPATH/conf/distributions <<EOF
Codename: $DIST
Suite: $DIST
Components: main
UDebComponents: main
Architectures: amd64 i386 source
Origin: buildbot.fusionforge.org
Description: FusionForge autobuilt repository
SignWith: $SIGNKEY
EOF

reprepro -Vb $REPOPATH include $DIST $CHANGEFILE

rm ${BUILDPLACE}/${PKGNAME}_${SMAJOR}${MINOR}*
rm -rf $BUILDPLACE/$PKGNAME-$MAJOR

cd $BUILDRESULT
cat $CHANGEFILE | sed '1,/^Checksums-Sha1:/d;/^[[:alnum:]]/,$d' | awk '{print $3}' | xargs rm
rm $CHANGEFILE

cd $CHECKOUTPATH

destroy_vm -t debian7 $HOST
start_vm_if_not_keeped -t debian7 $HOST
setup_debian_3rdparty_repo

# Transfer preseeding
cat tests/preseed/* | sed s/@FORGE_ADMIN_PASSWORD@/$FORGE_ADMIN_PASSWORD/ | ssh root@$HOST "LANG=C debconf-set-selections"

# Setup debian repo
export DEBMIRROR DEBMIRRORSEC
ssh root@$HOST "echo \"deb $DEBMIRROR $DIST main\" > /etc/apt/sources.list"
ssh root@$HOST "echo \"deb $DEBMIRRORSEC $DIST/updates main\" > /etc/apt/sources.list.d/security.list"
ssh root@$HOST "apt-get update"
ssh root@$HOST "UCF_FORCE_CONFFNEW=yes DEBIAN_FRONTEND=noninteractive LANG=C apt-get -o debug::pkgproblemresolver=true -y --force-yes dist-upgrade"

ssh root@$HOST "echo \"deb $DEBMIRROR jessie main\" >> /etc/apt/sources.list.d/jessie.list"
ssh root@$HOST "apt-get update"
ssh root@$HOST "UCF_FORCE_CONFFNEW=yes DEBIAN_FRONTEND=noninteractive LANG=C apt-get -o debug::pkgproblemresolver=true -y --force-yes install loggerhead libapache2-mod-wsgi"
ssh root@$HOST "rm /etc/apt/sources.list.d/jessie.list"

ssh root@$HOST "echo \"deb $DEBMIRROR $DIST main\" > /etc/apt/sources.list"
ssh root@$HOST "apt-get update"

ssh root@$HOST "echo \"deb file:/debian $DIST main\" >> /etc/apt/sources.list"
scp -r $WORKSPACE/build/debian root@$HOST:/ 
gpg --export --armor | ssh root@$HOST "apt-key add -"
sleep 5
ssh root@$HOST "apt-get update"

# Install fusionforge
ssh root@$HOST "UCF_FORCE_CONFFNEW=yes DEBIAN_FRONTEND=noninteractive LANG=C apt-get -o debug::pkgproblemresolver=true -y --force-yes install rsync postgresql-contrib fusionforge-full nscd"
echo "Set forge admin password"
ssh root@$HOST "/usr/share/gforge/bin/forge_set_password $FORGE_ADMIN_USERNAME $FORGE_ADMIN_PASSWORD"
ssh root@$HOST "LANG=C a2dissite default ; LANG=C invoke-rc.d apache2 reload"
ssh root@$HOST "(echo [core];echo use_ssl=no) > /etc/gforge/config.ini.d/zzz-buildbot.ini"
ssh root@$HOST "(echo [moinmoin];echo use_frame=no) >> /etc/gforge/config.ini.d/zzz-buildbot.ini"
ssh root@$HOST "(echo [mediawiki];echo unbreak_frames=yes) >> /etc/gforge/config.ini.d/zzz-buildbot.ini"
ssh root@$HOST "[ -e /var/lib/gforge/.bazaar/bazaar.conf ] && sed -i -e s,https://,http://,g /var/lib/gforge/.bazaar/bazaar.conf"
ssh root@$HOST "service nscd restart"

# Dump database
echo "Dump freshly installed database"
ssh root@$HOST "su - postgres -c \"pg_dumpall\" > /root/dump"

# Stop cron
echo "Stop cron daemon"
ssh root@$HOST "invoke-rc.d cron stop" || true

# Install selenium
ssh root@$HOST "apt-get -o debug::pkgproblemresolver=true -y install selenium"

# Install selenium tests
ssh root@$HOST "[ -d $FORGE_HOME ] || mkdir -p $FORGE_HOME"
rsync -a --delete tests/ root@$HOST:$FORGE_HOME/tests/

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
