#!/bin/bash -x
# Run FusionForge's PHPUnit+Selenium testsuite
#
# Copyright (C) 2011  Olivier Berger - Institut Telecom
# Copyright (C) 2014  Inria (Sylvain Beucler)
#
# This file is part of FusionForge. FusionForge is free software;
# you can redistribute it and/or modify it under the terms of the
# GNU General Public License as published by the Free Software
# Foundation; either version 2 of the Licence, or (at your option)
# any later version.
#
# FusionForge is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with FusionForge; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

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

service crond stop || true

# Test dependencies (EPEL)
yum install -y php-phpunit-PHPUnit php-phpunit-PHPUnit-Selenium

# Install a fake sendmail to catch all outgoing emails.
#perl -spi -e s#/usr/sbin/sendmail#$FORGE_HOME/tests/scripts/catch_mail.php# $config_path/config.ini.d/defaults.ini

# Now, start the functionnal test suite using phpunit and selenium
/usr/src/fusionforge/tests/scripts/phpunit.sh rpm/centos
