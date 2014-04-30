#!/bin/bash


# Build an unofficial package for selenium and install it
if ! dpkg -l selenium | grep -q ^ii ; then
    cd /usr/src/fusionforge/3rd-party/selenium/selenium
    debian/rules get-orig-source
    debuild --no-lintian --no-tgz-check -us -uc
    dpkg -i /usr/src/fusionforge/3rd-party/selenium/selenium_*_all.deb

    # Selenium dependencies
    aptitude -y install default-jre iceweasel

fi


# Test dependencies
aptitude -y install php5-cli phpunit phpunit-selenium

## If available, install the JUnit OSLC provider test suite
#if [ -d src/plugins/oslc/tests ]; then
#    cd /usr/src/fusionforge/src/plugins/oslc/tests
#    ./setup-provider-test.sh
#fi

/usr/src/fusionforge/tests/scripts/phpunit.sh Testsuite-deb.php
