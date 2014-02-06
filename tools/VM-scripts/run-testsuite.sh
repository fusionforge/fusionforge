#!/bin/bash

# Selenium dependencies
aptitude -y install default-jre iceweasel

# Build selenium
aptitude -y install cowbuilder
mkdir -p ~/builder/cow/
DISTROLIST=wheezy /root/fusionforge/tests/scripts/manage-cowbuilder.sh

mkdir -p ~/build/
aptitude -y install reprepro
echo -e "Codename: wheezy\nArchitectures: amd64 source\nComponents: main" > ~/build/debian/conf/distributions
cd /root/fusionforge/3rd-party/selenium
make


# Test dependencies
aptitude -y install php5-cli phpunit phpunit-selenium

## If available, install the JUnit OSLC provider test suite
#if [ -d src/plugins/oslc/tests ]; then
#    cd /root/fusionforge/src/plugins/oslc/tests
#    ./setup-provider-test.sh
#fi

/root/fusionforge/tests/scripts/phpunit.sh DEBDebian70Tests.php
