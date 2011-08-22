#!/bin/sh -ex

export CURDIR=`pwd`
export WORKSPACE=${WORKSPACE:-$CURDIR}

export CONFIG_PHP=func/config.php.buildbot
export SELENIUM_RC_HOST=${SELENIUM_RC_HOST:-`hostname -i`}
export SELENIUM_RC_DIR=$WORKSPACE/reports

# get config 
. tests/config/default
if [ -f tests/config/`hostname` ] ; then . tests/config/`hostname`; fi
if [ ! -z "$1" ]
then
	export HOST=$1
else
	export HOST=debian6.$DNSDOMAIN
	export VEID=$VEIDDEB
fi


export LXCTEMPLATE=$LXCDEBTEMPLATE

export IPBASE=$IPDEBBASE
export IPDNS=$IPDEBDNS
export IPMASK=$IPDEBMASK
export IPGW=$IPDEBGW

ARCH=`dpkg-architecture -qDEB_BUILD_ARCH`
export VZTEMPLATE=debian-$DEBVERS-$ARCH-minimal
export VZPRIVATEDIR
export DEBMIRROR

export DIST
export VMENGINE
export SSHPUBKEY
export HOSTKEYDIR

if [ "x${HUDSON_URL}" = "x" ]
then
	export BASEDIR=${BASEDIR:-/~`id -un`/ws}
	export USEVZCTL=true
	export SELENIUM_RC_HOST=localhost
	export SELENIUM_RC_URL=http://`hostname -f`$BASEDIR/reports
	export FFORGE_DEB_REPO=http://`hostname -f`$BASEDIR/build/debian
else
	export SELENIUM_RC_URL=${HUDSON_URL}job/$JOB_NAME/ws/reports
	export FFORGE_DEB_REPO=${HUDSON_URL}job/$JOB_NAME/ws/build/debian
fi
export DB_NAME=gforge
export CONFIGURED=true

[ ! -d $WORKSPACE/build/packages ] || rm -fr $WORKSPACE/build/packages
mkdir -p $WORKSPACE/build/packages
[ ! -d $WORKSPACE/build/config ] || rm -fr $WORKSPACE/build/config
mkdir -p $WORKSPACE/build/config

# Comment out the next line when you don't want to rebuild all the time
[ ! -d $WORKSPACE/build/debian ] || rm -fr $WORKSPACE/build/debian
[ -d $WORKSPACE/build/debian ] || mkdir $WORKSPACE/build/debian
[ ! -d $WORKSPACE/reports ] || rm -fr $WORKSPACE/reports
mkdir -p $WORKSPACE/reports/coverage

make -f Makefile.debian BUILDRESULT=$WORKSPACE/build/packages LOCALREPODEB=$WORKSPACE/build/debian clean
(cd 3rd-party/php-mail-mbox ; dpkg-source -x php-mail-mbox_0.6.3-1coclico1.dsc)
(cd 3rd-party/php-mail-mbox ; make -f Makefile.debian BUILDRESULT=$WORKSPACE/build/packages LOCALREPODEB=$WORKSPACE/build/debian rsqueeze)
(cd 3rd-party/selenium ; make getselenium)
make -f Makefile.debian BUILDRESULT=$WORKSPACE/build/packages LOCALREPODEB=$WORKSPACE/build/debian rsqueeze

if $KEEPVM 
then
	echo "Destroying vm $HOST"
	(cd tests/scripts ; sh ./stop_vm.sh $HOST || true)
fi

(cd tests/scripts ; ./start_vm.sh $HOST)

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

retcode=0
# TODO: Make test dir a parameter
echo "Transfer phpunit test on $HOST"
cat > $WORKSPACE/build/config/phpunit <<-EOF
HUDSON_URL=$HUDSON_URL
JOB_NAME=$JOB_NAME
EOF
scp -r $WORKSPACE/build/config  root@$HOST:/root/
rsync -a 3rd-party/selenium/binary/selenium-server-current/selenium-server.jar root@$HOST:/root/selenium-server.jar
rsync -a --delete tests/ root@$HOST:/root/tests/

if $REMOTESELENIUM
then
	echo "Run phpunit test on $HOST"
	ssh -X root@$HOST "tests/scripts/phpunit.sh DEBDebian60Tests.php" || retcode=$?
else
	cd tests
	phpunit --log-junit $WORKSPACE/reports/phpunit-selenium.xml DEBDebian60Tests.php || retcode=$?
	cd .. 
fi
if [ "x$SELENIUM_RC_DIR" != "x" ]
then
	rsync -av root@$HOST:/var/log/ $SELENIUM_RC_DIR/
fi
cp $WORKSPACE/reports/phpunit-selenium.xml $WORKSPACE/reports/phpunit-selenium.xml.org
xalan -in $WORKSPACE/reports/phpunit-selenium.xml.org -xsl fix_phpunit.xslt -out $WORKSPACE/reports/phpunit-selenium.xml
if $KEEPVM 
then
	echo "Keeping vm $HOST alive"
else
	(cd tests/scripts ; sh ./stop_vm.sh $HOST)
fi
exit $retcode
