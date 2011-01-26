#!/bin/sh -ex

export CURDIR=`pwd`
export WORKSPACE=${WORKSPACE:-$CURDIR}

export CONFIG_PHP=func/config.php.buildbotDEB
export SELENIUM_RC_HOST=${SELENIUM_RC_HOST:-`hostname -i`}
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
	export DIST
	export BASEDIR=${BASEDIR:-/~`id -un`/ws}
	export SELENIUM_RC_URL=http://`hostname -f`$BASEDIR/reports
	export FFORGE_DEB_REPO=http://`hostname -f`$BASEDIR/build/debian
	export HOST=debian6.local
	export SELENIUM_RC_HOST=localhost
	export USEVZCTL=true
else
	. tests/openvz/config.default
	if [ -f tests/openvz/config.`hostname` ] ; then . tests/openvz/config.`hostname`; fi
	ARCH=`dpkg-architecture -qDEB_BUILD_ARCH`
	export VZTEMPLATE=debian-$DEBVERS-$ARCH-minimal
	export VZPRIVATEDIR
	export DIST
	export SELENIUM_RC_URL=${HUDSON_URL}job/$JOB_NAME/ws/reports
	export FFORGE_DEB_REPO=${HUDSON_URL}job/$JOB_NAME/ws/build/debian
	export HOST=debian6.local
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
make -f Makefile.debian BUILDRESULT=$WORKSPACE/build/packages LOCALREPODEB=$WORKSPACE/build/debian rsqueeze

cd tests
phpunit --log-junit $WORKSPACE/reports/phpunit-selenium.xml DEBDebian60Tests.php

cd ..
cp $WORKSPACE/reports/phpunit-selenium.xml $WORKSPACE/reports/phpunit-selenium.xml.org
xalan -in $WORKSPACE/reports/phpunit-selenium.xml.org -xsl fix_phpunit.xslt -out $WORKSPACE/reports/phpunit-selenium.xml
