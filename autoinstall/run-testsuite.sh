#!/bin/bash
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
export DEBIAN_FRONTEND=noninteractive

if [ -z "$1" ]; then
    echo "Usage:"
    echo "  $0 src/debian"
    echo "  $0 deb/debian"
    echo "  $0 src/centos"
    echo "  $0 rpm/centos"
    exit 1
fi

# Selenium dependencies and test dependencies
# psmisc for db_reload.sh:killall
# rsyslog to get e.g. sshd error log
if [ -e /etc/debian_version ]; then
    apt-get -y install wget default-jre iceweasel
    apt-get -y install phpunit phpunit-selenium patch psmisc patch rsyslog
else
    yum -y install wget firefox java-1.6.0
    yum install -y php-phpunit-PHPUnit php-phpunit-PHPUnit-Selenium psmisc patch
fi

# Install selenium (no packaged version available)
SELENIUMMAJOR=2
SELENIUMMINOR=42
SELENIUMMICRO=2
SELENIUMURL=http://selenium-release.storage.googleapis.com/$SELENIUMMAJOR.$SELENIUMMINOR/selenium-server-standalone-$SELENIUMMAJOR.$SELENIUMMINOR.$SELENIUMMICRO.jar
mkdir -p /usr/share/selenium/
http_proxy=$PROXY wget -c $SELENIUMURL \
    -O /usr/share/selenium/selenium-server.jar

service cron stop || true

# Add alias to /etc/hosts
grep -q "^$(hostname -i).*$(forge_get_config scm_host)" /etc/hosts || sed -i -e "s/^$(hostname -i).*/& $(forge_get_config scm_host)/" /etc/hosts
 
# Fix screenshot default black background (/usr/share/{php,pear}) (fix available upstream)
patch -N /usr/share/*/PHPUnit/Extensions/SeleniumTestCase.php <<'EOF' || true
--- /usr/share/php/PHPUnit/Extensions/SeleniumTestCase.php-dist	2014-02-10 19:48:34.000000000 +0000
+++ /usr/share/php/PHPUnit/Extensions/SeleniumTestCase.php	2014-09-01 10:09:38.823051288 +0000
@@ -1188,7 +1188,7 @@
             !empty($this->screenshotUrl)) {
             $filename = $this->getScreenshotPath() . $this->testId . '.png';
 
-            $this->drivers[0]->captureEntirePageScreenshot($filename);
+            $this->drivers[0]->captureEntirePageScreenshot($filename, 'background=#CCFFDD');
 
             return 'Screenshot: ' . $this->screenshotUrl . '/' .
                    $this->testId . ".png\n";
EOF

# Now, start the functionnal test suite using phpunit and selenium
$(dirname $0)/../tests/func/phpunit-selenium.sh $@
