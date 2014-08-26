#!/bin/bash

# This script runs the preferred functionnal test suite, using phpunit
# and Selenium, which will test the Web interface of FusionForge in a
# controlled Firefox browser.

set -e
export DEBIAN_FRONTEND=noninteractive

# Build an unofficial package for selenium and install it
if ! dpkg-query -s selenium >/dev/null 2>&1 ; then
    version=2.35.0
    mkdir -p /usr/share/selenium/
    apt-get install -y wget
    wget -c http://selenium.googlecode.com/files/selenium-server-standalone-$version.jar \
	-O /usr/share/selenium/selenium-server.jar

    # Selenium dependencies
    apt-get -y install default-jre iceweasel
fi

# Ensure tested components are installed
UCF_FORCE_CONFFNEW=yes apt-get install -y fusionforge fusionforge-shell fusionforge-plugin-scmsvn fusionforge-plugin-scmbzr fusionforge-plugin-mediawiki fusionforge-plugin-moinmoin

service cron stop

config_path=$(forge_get_config config_path)
(echo [mediawiki]; echo unbreak_frames=yes) > $config_path/config.ini.d/zzz-buildbot.ini

# Test dependencies
apt-get -y install phpunit phpunit-selenium

# Now, start the functionnal test suite using phpunit and selenium
/usr/src/fusionforge/tests/scripts/phpunit.sh deb/debian
