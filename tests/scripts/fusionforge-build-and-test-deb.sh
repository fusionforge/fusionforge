#!/bin/sh -x

export CURDIR=`pwd`
export WORKSPACE=${WORKSPACE:-$CURDIR}

export CONFIG_PHP=func/config.php.buildbot
export SELENIUM_RC_HOST=192.168.0.204
export SELENIUM_RC_DIR=$WORKSPACE/reports
if [ "x${HUDSON_URL}" = "x" ]
then
	. tests/openvz/config.default
	if [ -f tests/openvz/config.`hostname` ] ; then . tests/openvz/config.`hostname`; fi
	export VEID=$VEIDDEB
	export IPBASE=$IPDEBBASE
	export IPDNS=$IPDEBDNS
	ARCH=`dpkg-architecture -qDEB_BUILD_ARCH`
	export VZTEMPLATE=debian-$DEBVERS-$ARCH-minimal
	export VZPRIVATEDIR
	export SELENIUM_RC_URL=http://`hostname -f`/ws/reports
	export FFORGE_RPM_REPO=http://`hostname -f`/ws/build/packages
	export HOST=debian6.local
	export SELENIUM_RC_HOST=localhost
else
	export SELENIUM_RC_URL=${HUDSON_URL}job/$JOB_NAME/ws/reports
	export FFORGE_RPM_REPO=${HUDSON_URL}job/$JOB_NAME/ws/build/packages
	export HOST=debian6.local
	export VZTEMPLATE=debian-$DEBVERS-$ARCH-minimal
fi
export DB_NAME=gforge
export CONFIGURED=true

[ ! -d $WORKSPACE/build ] || rm -fr $WORKSPACE/build
mkdir -p $WORKSPACE/build/packages $WORKSPACE/build/debian
[ ! -d $WORKSPACE/reports ] || rm -fr $WORKSPACE/reports
mkdir -p $WORKSPACE/reports/coverage

make -f Makefile.debian BUILDRESULT=$WORKSPACE/build/packages LOCALREPODEB=$WORKSPACE/build/debian rsqueeze

cd tests
phpunit --log-junit $WORKSPACE/reports/phpunit-selenium.xml DEBDebian60Tests.php

cd ..
cp $WORKSPACE/reports/phpunit-selenium.xml $WORKSPACE/reports/phpunit-selenium.xml.org
xalan -in $WORKSPACE/reports/phpunit-selenium.xml.org -xsl fix_phpunit.xslt -out $WORKSPACE/reports/phpunit-selenium.xml
