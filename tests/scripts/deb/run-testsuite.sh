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

service cron stop || true

config_path=$(forge_get_config config_path)
(echo [mediawiki]; echo unbreak_frames=yes) > $config_path/config.ini.d/zzz-buildbot.ini

# Test dependencies
apt-get -y install phpunit phpunit-selenium patch psmisc
# psmisc for db_reload.sh:killall
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
