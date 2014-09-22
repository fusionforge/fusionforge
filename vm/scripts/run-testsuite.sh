#!/bin/bash

set -e

# Selenium dependencies
aptitude -y install default-jre iceweasel

if ! dpkg --status selenium >/dev/null 2>&1; then
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
fi

config_path=$(forge_get_config config_path)

(echo [mediawiki]; echo unbreak_frames=yes) > $config_path/config.ini.d/zzz-buildbot.ini
(echo [moinmoin]; echo use_frame=no) >> $config_path/config.ini.d/zzz-buildbot.ini
cp ~/.ssh/id_rsa.pub ~/.ssh/authorized_keys2

# Test dependencies
aptitude -y install php5-cli phpunit phpunit-selenium

## If available, install the JUnit OSLC provider test suite
#if [ -d src/plugins/oslc/tests ]; then
#    cd /usr/src/fusionforge/src/plugins/oslc/tests
#    ./setup-provider-test.sh
#fi

/usr/src/fusionforge/tests/scripts/phpunit.sh DEBDebian70Tests.php
