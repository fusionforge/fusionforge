#!/bin/bash

# Selenium dependencies
aptitude -y install default-jre iceweasel

# Build selenium
aptitude -y install cowbuilder
mkdir -p ~/builder/cow/
DISTROLIST=wheezy /usr/src/fusionforge/tests/scripts/manage-cowbuilder.sh

mkdir -p /usr/src/build/debian/conf/
aptitude -y install reprepro
echo -e "Codename: wheezy\nArchitectures: amd64 source\nComponents: main" > /usr/src/build/debian/conf/distributions
cd /usr/src/fusionforge/3rd-party/selenium
make
dpkg -i /usr/src/build/debian/pool/main/s/selenium/selenium_*_all.deb

(echo [mediawiki]; echo unbreak_frames=yes) > /etc/gforge/config.ini.d/zzz-buildbot.ini

# Test dependencies
aptitude -y install php5-cli phpunit phpunit-selenium

## If available, install the JUnit OSLC provider test suite
#if [ -d src/plugins/oslc/tests ]; then
#    cd /usr/src/fusionforge/src/plugins/oslc/tests
#    ./setup-provider-test.sh
#fi

/usr/src/fusionforge/tests/scripts/phpunit.sh DEBDebian70Tests.php
