#!/bin/bash
# Install and start Selenium in background, pass configuration, and
# run PHPUnit functional testsuite
#
# Copyright (C) 2011  Olivier Berger - Institut Telecom
# Copyright (C) 2014, 2015  Inria (Sylvain Beucler)
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

set -ex
export DEBIAN_FRONTEND=noninteractive

if [ -z "$1" ]; then
    set +x
    echo "Usage:"
    echo "  $0 src/debian"
    echo "  $0 deb/debian"
    echo "  $0 src/centos"
    echo "  $0 rpm/centos"
    exit 1
fi

export INSTALL_METHOD=${1%/*}
export INSTALL_OS=${1#*/}
shift

case $INSTALL_METHOD in
    rpm|src|deb) ;;
    *)	echo "Unknown install method"
	exit 1 ;;
esac

case $INSTALL_OS in
    debian*|centos*) ;;
    *)	echo "Unknown install OS"
	exit 1 ;;
esac


install_selenium() {
    # Selenium dependencies and test dependencies
    # psmisc for db_reload.sh:killall
    # rsyslog to get e.g. sshd error log
    if [ -e /etc/debian_version ]; then
	apt-get -y install wget default-jre iceweasel
	apt-get -y install phpunit phpunit-selenium patch psmisc patch rsyslog
    else
	yum -y install wget firefox
	if yum list java-1.7.0-openjdk >/dev/null 2>&1 ; then
	    yum install -y java-1.7.0-openjdk
	else
	    yum install -y java-1.6.0
	fi
	yum --enablerepo=epel install -y php-phpunit-PHPUnit php-phpunit-PHPUnit-Selenium psmisc patch net-tools
    fi
    
    # Install selenium (no packaged version available)
    SELENIUMMAJOR=2
    SELENIUMMINOR=53
    SELENIUMMICRO=0
    SELENIUMURL=http://selenium-release.storage.googleapis.com/$SELENIUMMAJOR.$SELENIUMMINOR/selenium-server-standalone-$SELENIUMMAJOR.$SELENIUMMINOR.$SELENIUMMICRO.jar
    mkdir -p /usr/share/selenium/
    http_proxy=$PROXY wget -c $SELENIUMURL \
	      -O /usr/share/selenium/selenium-server.jar
    
    # Add alias to /etc/hosts
    if ! grep -q ^$(hostname -i) /etc/hosts ; then
	echo $(hostname -i) $(hostname -f) $(hostname)>> /etc/hosts
    fi
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
}

# Mitigate testsuite timeouts, cf.
# http://lists.fusionforge.org/pipermail/fusionforge-general/2015-November/002955.html
fixup_nss() {
    conf=''
    case $INSTALL_OS in
        debian*)
            if ! grep -q '^export PGPASSFILE' /etc/apache2/envvars; then
                echo 'export PGPASSFILE=' >> /etc/apache2/envvars
            fi
            ;;
        centos*)
            if ! grep -q '^PGPASSFILE' /etc/sysconfig/httpd; then
                echo 'PGPASSFILE=' >> /etc/sysconfig/httpd
            fi
            ;;
    esac
}

fixup_nss

install_selenium

service cron stop || true

# Reset the database and repos to post-install/pristine state
$(dirname $0)/func/fixtures.sh --reset
$(dirname $0)/func/fixtures.sh --backup


HOST=$(hostname -f)
FORGE_HOME=$(cd $(dirname $0)/..; pwd)

SELENIUM_RC_DIR=/var/log
SELENIUM_RC_HOST=$HOST
# URL for screenshots - cf. http://buildbot.fusionforge.org/env-vars.html
SELENIUM_RC_URL=${JOB_URL}ws/reports
# config.php will be loaded inside the functionnal test suite with
# require_once, in SeleniumForge.php
export SELENIUM_RC_DIR SELENIUM_RC_URL SELENIUM_RC_HOST HOST

# Add definitions for the PHP functionnal test suite
cat <<-EOF >$(dirname $0)/func/config.php
<?php
// Host where selenium-rc is running
define ('SELENIUM_RC_HOST', getenv('SELENIUM_RC_HOST'));
define ('SELENIUM_RC_DIR', getenv('SELENIUM_RC_DIR'));

// The forge's hostname
define ('HOST', getenv('HOST'));

// Base URL where FusionForge is installed
define ('ROOT', '');

define('INSTALL_METHOD', getenv('INSTALL_METHOD'));
define('INSTALL_OS', getenv('INSTALL_OS'));

//
// DON'T MODIFY BELOW THIS LINE UNLESS YOU KNOW WHAT YOU DO
//

// These are deduced from the previous definitions.

// URL to access the application
define ('URL', 'http://'.HOST.'/');

// WSDL of the forges SOAP API
define ('WSDL_URL', URL.'soap/index.php?wsdl');
EOF

echo "Starting Selenium"
killall -9 java || true
timeout=60
PATH=/usr/lib/iceweasel:/usr/lib64/firefox:$PATH LANG=C java -jar /usr/share/selenium/selenium-server.jar -trustAllSSLCertificates -singleWindow &
pid=$!
i=0
while [ $i -lt $timeout ] && ! netstat -tnl 2>/dev/null | grep -q :4444 && kill -0 $pid 2>/dev/null; do
    echo "Waiting for Selenium..."
    sleep 1
    i=$(($i+1))
done
if [ $i = $timeout ]; then
    echo "Selenium failed to start listener… lacking entropy? Trying again."
    find / > /dev/null 2> /dev/null &
    i=0
    while [ $i -lt $timeout ] && ! netstat -tnl 2>/dev/null | grep -q :4444 && kill -0 $pid 2>/dev/null; do
	echo "Waiting for Selenium..."
	sleep 1
	i=$(($i+1))
    done
fi
if [ $i = $timeout ] || ! kill -0 $pid 2>/dev/null; then
    echo "Selenium failed to start!"
    netstat -tnl
    kill -9 $pid
    exit 1
fi

echo "Running PHPunit tests"
retcode=0
set -x
cd $(dirname $0)/
# Override test through parameter, useful when launching tests through buildbot/*.sh (e.g. SSH)
# Use the TESTGLOB environment variable otherwise
testname="func_tests.php"
if [ -n "$1" ] ; then
    testname="$1"
fi

phpunit --verbose --debug --stop-on-failure --log-junit $SELENIUM_RC_DIR/phpunit-selenium.xml $testname || retcode=$?

set +x
echo "phpunit returned with code $retcode"

set +e
kill $pid
killall -9 firefox-bin  # debian
killall -9 firefox      # centos
killall -9 java         # kill java stuffs
exit $retcode
