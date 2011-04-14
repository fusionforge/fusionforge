#!/bin/sh -x

export CONFIG_PHP=func/config.php.buildbot
export SELENIUM_RC_HOST=192.168.0.204
export SELENIUM_RC_DIR=$WORKSPACE/reports
export SELENIUM_RC_URL=${HUDSON_URL}job/$JOB_NAME/ws/reports
export FFORGE_RPM_REPO=${HUDSON_URL}job/fusionforge-trunk-build-rpm/ws/build/packages
export HOST=centos52.local
export DB_NAME=gforge
export CONFIGURED=true

rm -fr build/ reports/
mkdir -p build/packages reports/coverage

cp src/rpm-specific/fusionforge.repo build/packages/fusionforge.repo
sed -i "s#http://fusionforge.org/#${HUDSON_URL}#" build/packages/fusionforge.repo
sed -i "s#baseurl = .*#baseurl = $FFORGE_RPM_REPO/#" build/packages/fusionforge.repo

cd tests
phpunit --log-junit $WORKSPACE/reports/phpunit-selenium.xml RPMCentos52Tests.php

cd ..
cp $WORKSPACE/reports/phpunit-selenium.xml $WORKSPACE/reports/phpunit-selenium.xml.org
xalan -in $WORKSPACE/reports/phpunit-selenium.xml.org -xsl fix_phpunit.xslt -out $WORKSPACE/reports/phpunit-selenium.xml
