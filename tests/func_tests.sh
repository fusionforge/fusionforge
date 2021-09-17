#!/bin/bash
# Install and start Selenium in background, pass configuration, and
# run PHPUnit functional testsuite
#
# Copyright (C) 2011  Olivier Berger - Institut Telecom
# Copyright (C) 2014, 2015  Inria (Sylvain Beucler)
# Copyright 2020, Franck Villaume - TrivialDev
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

fix_httpd_itk() {
	case $INSTALL_OS in
		centos*)
			if [[ `rpm -qi httpd-itk | grep Release | awk '{print $3}'` != '1.el7' ]]; then
				echo 'WARNING: WORKAROUND for docker/lxc. Downgrade httpd-itk.'
				echo 'TODO: check for newer version. Debian not impacted.'
				curl https://kojipkgs.fedoraproject.org//packages/httpd-itk/2.4.7.04/1.el7/x86_64/httpd-itk-2.4.7.04-1.el7.x86_64.rpm -o /tmp/httpd-itk-2.4.7.04-1.el7.x86_64.rpm
				yum downgrade -y /tmp/httpd-itk-2.4.7.04-1.el7.x86_64.rpm
				rm -f /tmp/httpd-itk-2.4.7.04-1.el7.x86_64.rpm
				service httpd restart || true
			fi
		;;
	esac
}

install_selenium() {
	# Selenium dependencies and test dependencies
	# psmisc for db_reload.sh:killall
	# rsyslog to get e.g. sshd error log
	if [ -e /etc/debian_version ]; then
		apt-get -y install wget firefox-esr net-tools
		if grep -q ^8 /etc/debian_version; then
		    apt-get -y install rsyslog patch composer
		    apt-get -y install -t jessie-backports openjdk-8-jdk
		else
		    apt-get -y install php-curl unzip composer psmisc rsyslog default-jre patch
		fi
	else
		yum -y install wget firefox java-1.8.0-openjdk
		yum --enablerepo=epel install -y psmisc net-tools patch php-cli php-zip unzip
		pushd $(mktemp -d)
		php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
		php composer-setup.php --install-dir=/usr/local/bin --filename=composer
		popd
	fi
	mkdir -p /usr/local/share/php
	pushd /usr/local/share/php
	composer --no-plugins --no-scripts require phpunit/phpunit
	if grep -q ^9 /etc/debian_version >/dev/null 2>&1 || grep "7\." /etc/centos-release >/dev/null 2>&1; then
		composer --no-plugins --no-scripts require phpunit/phpunit-selenium
	else
		composer --no-plugins --no-scripts require phpunit/phpunit-selenium:dev-master
	fi
	popd

	# Install selenium (no packaged version available)
	SELENIUMMAJOR=3
	SELENIUMMINOR=141
	SELENIUMMICRO=59
	SELENIUMURL=http://selenium-release.storage.googleapis.com/$SELENIUMMAJOR.$SELENIUMMINOR/selenium-server-standalone-$SELENIUMMAJOR.$SELENIUMMINOR.$SELENIUMMICRO.jar
	mkdir -p /usr/share/selenium/
	http_proxy=$PROXY wget -v -c $SELENIUMURL \
		-O /usr/share/selenium/selenium-server.jar

	# Install GeckoDriver
	GECKODRIVERMAJOR=0
	GECKODRIVERMINOR=30
	GECKODRIVERMICRO=0
	GECKODRIVERURL=https://github.com/mozilla/geckodriver/releases/download/v$GECKODRIVERMAJOR.$GECKODRIVERMINOR.$GECKODRIVERMICRO/geckodriver-v$GECKODRIVERMAJOR.$GECKODRIVERMINOR.$GECKODRIVERMICRO-linux64.tar.gz
	mkdir -p /usr/share/geckodriver/
	rm -f /usr/share/geckodriver/geckodriver
	http_proxy=$PROXY wget -c $GECKODRIVERURL \
                -O /usr/share/geckodriver/geckodriver.tar.gz

	tar -zxf /usr/share/geckodriver/geckodriver.tar.gz -C /usr/share/geckodriver/
	chmod +x /usr/share/geckodriver/geckodriver

	# Add alias to /etc/hosts
	if ! grep -q ^$(hostname -i) /etc/hosts ; then
		echo $(hostname -i) $(hostname -f) $(hostname)>> /etc/hosts
	fi
	grep -q "^$(hostname -i).*$(forge_get_config scm_host)" /etc/hosts || sed -i -e "s/^$(hostname -i).*/& $(forge_get_config scm_host)/" /etc/hosts

	#fix https://github.com/giorgiosironi/phpunit-selenium/issues/427
	for i in /usr/share/*/PHPUnit/Extensions/Selenium2TestCase/Element.php /usr/local/share/php/vendor/phpunit/phpunit-selenium/PHPUnit/Extensions/Selenium2TestCase/Element.php ; do
	    if [ -e "$i" ] ; then
	patch -N "$i" <<'EOF' || true
--- Element.php.dist 2014-11-02 09:23:27.000000000 +0000
+++ Element.php     2019-01-15 15:00:44.034513685 +0000
@@ -77,10 +77,21 @@
             PHPUnit\Extensions\Selenium2TestCase_URL $parentFolder,
             PHPUnit\Extensions\Selenium2TestCase_Driver $driver)
     {
+        $key = false;
         if (!isset($value['ELEMENT'])) {
-            throw new InvalidArgumentException('Element not found.');
+            foreach ($value as $lKey => $val) {
+                if (substr($lKey,0,7) === "element") {
+                    $key = $lKey;
+                    break;
+                }
+            }
+            if (! $key) {
+                throw new InvalidArgumentException('Element not found.');
+            }
+        } else {
+            $key = "ELEMENT";
         }
-        $url = $parentFolder->descend($value['ELEMENT']);
+        $url = $parentFolder->descend($value[$key]);
         return new self($driver, $url);
     }

EOF
	    fi
	done
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

fix_httpd_itk

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
define ('URL', 'https://'.HOST.'/');

// WSDL of the forges SOAP API
// define ('WSDL_URL', URL.'soap/index.php?wsdl');
define ('WSDL_URL', 'http://'.HOST.'/soap/index.php?wsdl');
EOF

echo "Starting Selenium"
killall -9 java || true
timeout=60
export PATH=/usr/share/geckodriver:/usr/lib/iceweasel:/usr/lib/firefox-esr:/usr/lib64/firefox:$PATH
export LANG=C
java -Dwebdriver.gecko.driver=/usr/share/geckodriver/geckodriver -jar /usr/share/selenium/selenium-server.jar &
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

if [ -x /usr/local/share/php/vendor/bin/phpunit ] ; then
	phpunit=/usr/local/share/php/vendor/bin/phpunit
else
	phpunit=phpunit
fi

# For some reason PHPunit thinks all methods are tests
# …hence the --filter ::test to enforce that
# otherwise shared methods such as login() and logout() are run like tests and fail
timeout 2h $phpunit --filter ::test --verbose --debug --stop-on-failure --log-junit $SELENIUM_RC_DIR/phpunit-selenium.xml $testname || retcode=$?

set +x
echo "phpunit returned with code $retcode"

set +e
kill $pid
killall -9 firefox-bin  # debian
killall -9 firefox      # centos
killall -9 java         # kill java stuffs
exit $retcode
