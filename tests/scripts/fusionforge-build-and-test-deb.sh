#!/bin/sh -e

export CURDIR=`pwd`
export WORKSPACE=${WORKSPACE:-$CURDIR}

export CONFIG_PHP=func/config.php.buildbot
export SELENIUM_RC_HOST=${SELENIUM_RC_HOST:-`hostname -i`}
export SELENIUM_RC_DIR=$WORKSPACE/reports

# get config 
. tests/config/default
if [ -f tests/config/`hostname` ] ; then . tests/config/`hostname`; fi

export VEID=$VEIDDEB

export LXCTEMPLATE=$LXCDEBTEMPLATE

export IPBASE=$IPDEBBASE
export IPDNS=$IPDEBDNS
export IPMASK=$IPDEBMASK
export IPGW=$IPDEBGW

ARCH=`dpkg-architecture -qDEB_BUILD_ARCH`
export VZTEMPLATE=debian-$DEBVERS-$ARCH-minimal
export VZPRIVATEDIR
export HOST=debian6.local
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
scp -r tests root@$HOST:/root
scp 3rd-party/selenium/binary/selenium-server-current/selenium-server.jar root@$HOST:/root
ssh root@$HOST "cat /root/tests/preseed/* | LANG=C debconf-set-selections"
if [ "x$DEBMIRROR" != "x" ]
then
	ssh root@$HOST "echo \"deb $DEBMIRROR $DIST main\" > /etc/apt/sources.list"
fi
if [ "x$DEBMIRRORSEC" != "x" ]
then
	ssh root@$HOST "echo \"deb $DEBMIRRORSEC $DIST/updates main\" > /etc/apt/sources.list.d/security.list"
fi
ssh root@$HOST "echo \"deb file:/debian $DIST main\" >> /etc/apt/sources.list"
scp -r $WORKSPACE/build/debian root@$HOST:/ 
gpg --export --armor | ssh root@$HOST "apt-key add -"
sleep 5
ssh root@$HOST "apt-get update"
ssh root@$HOST "UCF_FORCE_CONFFNEW=yes DEBIAN_FRONTEND=noninteractive LANG=C apt-get -y --force-yes install postgresql-contrib fusionforge-plugin-forumml fusionforge-full"
ssh root@$HOST "LANG=C a2dissite default ; LANG=C invoke-rc.d apache2 reload ; LANG=C touch /tmp/fusionforge-use-pfo-rbac"
ssh root@$HOST "(echo [core];echo use_ssl=no) > /etc/gforge/config.ini.d/zzz-builbot.ini"
ssh root@$HOST "su - postgres -c \"pg_dump -Fc $DB_NAME\" > /root/dump"
ssh root@$HOST "invoke-rc.d cron stop" || true

if $REMOTESELENIUM
then
	echo "Run phpunit test on $HOST"
	ssh -X root@$HOST "tests/scripts/phpunit.sh DEBDebian60Tests.php" 
else
	cd tests
	phpunit --log-junit $WORKSPACE/reports/phpunit-selenium.xml DEBDebian60Tests.php
	cd .. 
	if [ "x$SELENIUM_RC_DIR" != "x" ]
	then
		scp -r root@$HOST:/var/log $SELENIUM_RC_DIR
	fi
	cp $WORKSPACE/reports/phpunit-selenium.xml $WORKSPACE/reports/phpunit-selenium.xml.org
	xalan -in $WORKSPACE/reports/phpunit-selenium.xml.org -xsl fix_phpunit.xslt -out $WORKSPACE/reports/phpunit-selenium.xml
fi
if $KEEPVM 
then
	echo "Keeping vm $HOST alive"
else
	(cd tests/scripts ; sh ./stop_vm.sh $HOST)
fi

