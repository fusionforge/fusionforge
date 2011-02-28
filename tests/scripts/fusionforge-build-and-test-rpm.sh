#!/bin/sh -xe

export CURDIR=`pwd`
export WORKSPACE=${WORKSPACE:-$CURDIR}

export CONFIG_PHP=func/config.php.buildbot
export SELENIUM_RC_HOST=${SELENIUM_RC_HOST:-`hostname -i`}
export SELENIUM_RC_DIR=$WORKSPACE/reports
# get config 
. tests/config/default
if [ -f tests/config/`hostname` ] ; then . tests/config/`hostname`; fi

if [ "x${HUDSON_URL}" = "x" ]
then
	export VEID=$VEIDCEN
	export IPBASE=$IPCENTOSBASE
	export IPDNS=$IPCENTOSDNS
	ARCH=`dpkg-architecture -qDEB_BUILD_ARCH`
	export VZTEMPLATE=centos-$CENTVERS-$ARCH-minimal
	export VZPRIVATEDIR
	export DIST
	export BASEDIR=${BASEDIR:-/~`id -un`/ws}
	export SELENIUM_RC_URL=http://`hostname -f`$BASEDIR/reports
	export FFORGE_RPM_REPO=http://`hostname -f`$BASEDIR/build/packages
	export HOST=centos5.local
	export SELENIUM_RC_HOST=localhost
	export USEVZCTL=true
else
	export SELENIUM_RC_URL=${HUDSON_URL}job/$JOB_NAME/ws/reports
	export FFORGE_RPM_REPO=${HUDSON_URL}job/$JOB_NAME/ws/build/packages
	export HOST=centos52.local
	export VZTEMPLATE=centos-5-x86
fi
export DB_NAME=gforge
export CONFIGURED=true

[ ! -d $WORKSPACE/build ] || rm -fr $WORKSPACE/build
[ ! -d $WORKSPACE/reports ] || rm -fr $WORKSPACE/reports
mkdir -p $WORKSPACE/build/packages $WORKSPACE/reports/coverage

make -f Makefile.rh BUILDRESULT=$WORKSPACE/build/packages all

cp src/rpm-specific/fusionforge.repo $WORKSPACE/build/packages/fusionforge.repo
sed -i "s#http://fusionforge.org/#${HUDSON_URL}#" $WORKSPACE/build/packages/fusionforge.repo
sed -i "s#baseurl = .*#baseurl = $FFORGE_RPM_REPO/#" $WORKSPACE/build/packages/fusionforge.repo

cd tests
phpunit --log-junit $WORKSPACE/reports/phpunit-selenium.xml RPMCentos52Tests.php

cd ..
cp $WORKSPACE/reports/phpunit-selenium.xml $WORKSPACE/reports/phpunit-selenium.xml.org
xalan -in $WORKSPACE/reports/phpunit-selenium.xml.org -xsl fix_phpunit.xslt -out $WORKSPACE/reports/phpunit-selenium.xml
