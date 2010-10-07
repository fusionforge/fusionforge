#!/bin/sh -x

export CONFIG_PHP=func/config.php.buildbot
export SELENIUM_RC_HOST=192.168.0.204
export SELENIUM_RC_DIR=$WORKSPACE/reports
export SELENIUM_RC_URL=${HUDSON_URL}job/$JOB_NAME/ws/reports
export FFORGE_RPM_REPO=${HUDSON_URL}job/$JOB_NAME/ws/build/packages
export HOST=centos52.local
export DB_NAME=gforge
export CONFIGURED=true

export CURDIR=`pwd`
export WORKSPACE=${WORKSPACE:-$CURDIR}

[ ! -d $WORKSPACE/build ] || rm -fr $WORKSPACE/build
[ ! -d $WORKSPACE/reports ] || rm -fr $WORKSPACE/reports
mkdir -p $WORKSPACE/build/packages $WORKSPACE/reports/coverage

make -f Makefile.rh BUILDRESULT=$WORKSPACE/build/packages all

cp gforge/rpm-specific/fusionforge.repo $WORKSPACE/build/packages/fusionforge.repo
sed -i "s#http://fusionforge.org/#${HUDSON_URL}#" $WORKSPACE/build/packages/fusionforge.repo
sed -i "s#baseurl = .*#baseurl = $FFORGE_RPM_REPO/#" $WORKSPACE/build/packages/fusionforge.repo

cd tests
phpunit --log-junit $WORKSPACE/reports/phpunit-selenium.xml RPMCentos52Tests.php

cd ..
cp $WORKSPACE/reports/phpunit-selenium.xml $WORKSPACE/reports/phpunit-selenium.xml.org
xalan -in $WORKSPACE/reports/phpunit-selenium.xml.org -xsl fix_phpunit.xslt -out $WORKSPACE/reports/phpunit-selenium.xml
