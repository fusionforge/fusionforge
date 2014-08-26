#!/bin/bash -x

set -e

# Build an unofficial package for selenium and install it
if ! rpm -q selenium >/dev/null ; then
    version=2.35.0
    mkdir -p /usr/share/selenium/
    yum install -y wget
    wget -c http://selenium.googlecode.com/files/selenium-server-standalone-$version.jar \
	-O /usr/share/selenium/selenium-server.jar

    # Selenium dependencies
    yum -y install firefox java-1.6.0
fi

# Ensure tested components are installed
yum install -y fusionforge fusionforge-shell fusionforge-plugin-scmsvn fusionforge-plugin-mediawiki
# fusionforge-plugin-online_help fusionforge-plugin-extratabs fusionforge-plugin-authldap fusionforge-plugin-scmgit fusionforge-plugin-blocks

service crond stop

config_path=$(forge_get_config config_path)
(echo [mediawiki]; echo unbreak_frames=yes) > $config_path/config.ini.d/zzz-buildbot.ini
#(echo [core];echo use_ssl=no) > $config_path/config.ini.d/zzz-buildbot.ini
#(echo [moinmoin];echo use_frame=no) >> $config_path/config.ini.d/zzz-buildbot.ini

# Test dependencies (EPEL)
yum install -y php-phpunit-PHPUnit php-phpunit-PHPUnit-Selenium

# Install a fake sendmail to catch all outgoing emails.
#perl -spi -e s#/usr/sbin/sendmail#$FORGE_HOME/tests/scripts/catch_mail.php# $config_path/config.ini.d/defaults.ini

# Now, start the functionnal test suite using phpunit and selenium
/usr/src/fusionforge/tests/scripts/phpunit.sh rpm/centos
