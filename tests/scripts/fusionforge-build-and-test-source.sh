#!/bin/sh -xe

export CURDIR=`pwd`
export WORKSPACE=${WORKSPACE:-$CURDIR}

export CONFIG_PHP=func/config.php.buildbot
export SELENIUM_RC_HOST=${SELENIUM_RC_HOST:-`hostname -i`}
export SELENIUM_RC_DIR=$WORKSPACE/reports
if [ "x${HUDSON_URL}" = "x" ]
then
	. tests/openvz/config.default
	if [ -f tests/openvz/config.`hostname` ] ; then . tests/openvz/config.`hostname`; fi
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
export DB_NAME=fforge
export CONFIGURED=true

export BUILDRESULT=$WORKSPACE/build/packages

[ ! -d $WORKSPACE/build/packages ] || rm -fr $WORKSPACE/build/packages
[ ! -d $WORKSPACE/reports ] || rm -fr $WORKSPACE/reports
[ ! -d $WORKSPACE/apidocs ] || rm -fr $WORKSPACE/apidocs
mkdir -p $WORKSPACE/build/packages $WORKSPACE/reports/coverage $WORKSPACE/apidocs

make build-doc DOCSDIR=$WORKSPACE/apidocs DOXYGEN=$HOME/doxygen-1.6.3/bin/doxygen
make BUILDRESULT=$WORKSPACE/build/packages buildtar

cp src/rpm-specific/fusionforge.repo $WORKSPACE/build/packages/fusionforge.repo
sed -i "s#http://fusionforge.org/#${HUDSON_URL}#" $WORKSPACE/build/packages/fusionforge.repo
sed -i "s#baseurl = .*#baseurl = $FFORGE_RPM_REPO/#" $WORKSPACE/build/packages/fusionforge.repo

cd tests
phpunit --log-junit $WORKSPACE/reports/phpunit-selenium.xml TarCentos52Tests.php

cd ..
cp $WORKSPACE/reports/phpunit-selenium.xml $WORKSPACE/reports/phpunit-selenium.xml.org
xalan -in $WORKSPACE/reports/phpunit-selenium.xml.org -xsl fix_phpunit.xslt -out $WORKSPACE/reports/phpunit-selenium.xml
