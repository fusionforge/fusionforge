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

service cron stop || true

# Test dependencies
# psmisc for db_reload.sh:killall
apt-get -y install phpunit phpunit-selenium patch psmisc
patch -N /usr/share/php/PHPUnit/Extensions/SeleniumTestCase.php <<'EOF' || true
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
/usr/src/fusionforge/tests/scripts/phpunit.sh $@
