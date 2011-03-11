#!/bin/sh -e

export CURDIR=`pwd`
export WORKSPACE=${WORKSPACE:-$CURDIR}

export CONFIG_PHP=func/config.php.buildbot
export SELENIUM_RC_HOST=${SELENIUM_RC_HOST:-`hostname -i`}
export SELENIUM_RC_DIR=$WORKSPACE/reports

# get config 
. tests/config/default
if [ -f tests/config/`hostname` ] ; then . tests/config/`hostname`; fi

export VEID=$VEIDSRC

export LXCTEMPLATE=$LXCCOSTEMPLATE

export IPBASE=$IPCOSBASE
export IPDNS=$IPCOSDNS
export IPMASK=$IPCOSMASK
export IPGW=$IPCOSGW

ARCH=`dpkg-architecture -qDEB_BUILD_ARCH`
export VZTEMPLATE=centos-$COSVERS-$ARCH-minimal
export VZPRIVATEDIR
export HOST=centos5src.local
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
	export FFORGE_RPM_REPO=http://`hostname -f`$BASEDIR/build/packages
else
	export SELENIUM_RC_URL=${HUDSON_URL}job/$JOB_NAME/ws/reports
	export FFORGE_RPM_REPO=${HUDSON_URL}job/$JOB_NAME/ws/build/packages
	export VZTEMPLATE=centos-5-x86
fi
export DB_NAME=fforge
export CONFIGURED=true

export BUILDRESULT=$WORKSPACE/build/packages

[ ! -d $WORKSPACE/build/packages ] || rm -fr $WORKSPACE/build/packages
[ ! -d $WORKSPACE/reports ] || rm -fr $WORKSPACE/reports
[ ! -d $WORKSPACE/apidocs ] || rm -fr $WORKSPACE/apidocs
mkdir -p $WORKSPACE/build/packages $WORKSPACE/reports/coverage $WORKSPACE/apidocs

[ ! -e $HOME/doxygen-1.6.3/bin/doxygen ] || make build-doc DOCSDIR=$WORKSPACE/apidocs DOXYGEN=$HOME/doxygen-1.6.3/bin/doxygen
make BUILDRESULT=$WORKSPACE/build/packages buildtar

cp src/rpm-specific/fusionforge.repo $WORKSPACE/build/packages/fusionforge.repo
sed -i "s#http://fusionforge.org/#${HUDSON_URL}#" $WORKSPACE/build/packages/fusionforge.repo
sed -i "s#baseurl = .*#baseurl = $FFORGE_RPM_REPO/#" $WORKSPACE/build/packages/fusionforge.repo

if $KEEPVM
then
	echo "Destroying vm $HOST"
	(cd tests/scripts ; sh ./stop_vm.sh $HOST)
fi
(cd tests/scripts ; ./start_vm.sh $HOST)
scp -r tests root@$HOST:/root
if [ "x$BUILDRESULT" != "x" ]
then
	scp $BUILDRESULT/fusionforge-*.tar.bz2 root@$HOST:
else
	scp ../build/packages/fusionforge-*.tar.bz2 root@$HOST:
fi
ssh root@$HOST 'tar jxf fusionforge-*.tar.bz2'
[ ! -e "/tmp/timedhosts.txt" ] || scp -p /tmp/timedhosts.txt root@$HOST:/var/cache/yum/timedhosts.txt
ssh root@$HOST 'cd fusionforge-*; FFORGE_RPM_REPO=http://buildbot.fusionforge.org/job/fusionforge-trunk-build-and-test-rpm/ws/build/packages/ FFORGE_DB=fforge FFORGE_USER=gforge FFORGE_ADMIN_USER=ffadmin FFORGE_ADMIN_PASSWORD=ffadmin ./install.sh centos52.local'
scp -p root@$HOST:/var/cache/yum/timedhosts.txt /tmp/timedhosts.txt || true
ssh root@$HOST "(echo [core];echo use_ssl=no) > /etc/gforge/config.ini.d/zzz-builbot.ini"
ssh root@$HOST "cd /root/tests/func; CONFIGURED=true CONFIG_PHP=config.php.buildbot DB_NAME=$DB_NAME php db_reload.php"
ssh root@$HOST "su - postgres -c \"pg_dump -Fc $DB_NAME\" > /root/dump"
#  Install a fake sendmail to catch all outgoing emails.
# ssh root@$HOST "perl -spi -e s#/usr/sbin/sendmail#/opt/tests/scripts/catch_mail.php# /etc/gforge/local.inc"
ssh root@$HOST "service crond stop" || true

if $REMOTESELENIUM
then
	echo "Run phpunit test on $HOST"
else
	cd tests
	phpunit --log-junit $WORKSPACE/reports/phpunit-selenium.xml TarCentos52Tests.php
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

