#! /bin/sh

if [ $# -ge 1 ]
then
        testsuite=$1
	shift
else
        echo "You must give the testsuite to run :"
	echo "	- DEBDebian70Tests.php"
	echo "	- RPMCentosTests.php"
	echo "	- TarCentosTests.php"
fi
if [ "x$testsuite" = "x" ]
then
        echo "Forge test suite not found"
        exit 1
fi

scriptdir=$(dirname $0)
FORGE_HOME=$(cd $scriptdir/../..; pwd)
cd $FORGE_HOME
[ ! -f tests/config/default ] || . tests/config/default
[ ! -f tests/config/phpunit ] || . tests/config/phpunit
SELENIUM_RC_DIR=/var/log
SELENIUM_RC_URL=${HUDSON_URL}job/${JOB_NAME}/ws/reports
SELENIUM_RC_HOST=`hostname -f`
HOST=`hostname -f`
CONFIG_PHP=func/config.php
export SELENIUM_RC_DIR SELENIUM_RC_URL SELENIUM_RC_HOST HOST DB_NAME DB_USER CONFIG_PHP

cat <<-EOF >tests/func/config.php
<?php
// Host where selenium-rc is running
define ('SELENIUM_RC_HOST', getenv('SELENIUM_RC_HOST'));
define ('SELENIUM_RC_DIR', getenv('SELENIUM_RC_DIR'));

// The forge's hostname
define ('HOST', getenv('HOST'));

// Base URL where FusionForge is installed
define ('ROOT', '');

// Define locations
define('HOME_FORGE', '$FORGE_HOME/src');

// Database connection parameters.
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASSWORD', '@@FFDB_PASS@@');
define('DB_INIT_CMD', "$FORGE_HOME/tests/func/db_reload.sh >>/var/log/db_reload_selenium.log 2>&1");

// this should be an existing user of the forge together with its password
// (the password should be different from 'myadmin')
define ('FORGE_ADMIN_USERNAME', '$FORGE_ADMIN_USERNAME');
define ('FORGE_ADMIN_PASSWORD', '$FORGE_ADMIN_PASSWORD');
define ('FORGE_OTHER_PASSWORD', '$FORGE_OTHER_PASSWORD');

// Where CLI is installed
define ('CLI_CMD', '$FORGE_HOME/acde/tools/gforge-cli/gforge.php');

// Where Java CLI is installed
define ('JAGOSI_CMD', '$FORGE_HOME/acde/tools/gforge-java-cli/');

// Enter true when file is configured.
define('CONFIGURED', getenv('CONFIGURED'));

//
// DON'T MODIFY BELOW THIS LINE UNLESS YOU KNOW WHAT YOU DO
//

// These are deduced from the previous definitions.

// URL to access the application
define ('URL', 'http://'.HOST.'/');

// WSDL of the forges SOAP API
define ('WSDL_URL', URL.'soap/index.php?wsdl');
?>
EOF

echo "Starting Selenium"
killall -9 java
PATH=/usr/lib/iceweasel:$PATH LANG=C java -jar $FORGE_HOME/tests/selenium-server.jar -singleWindow >/dev/null &
i=0
timeout=200
while [ $i -lt $timeout ] && ! netstat -tnl 2>/dev/null | grep -q :4444 ; do
    sleep 1
    i=$(($i+1))
done
if [ $i = $timeout ] ; then
    echo "Selenium failed to start within $timeout seconds"
    exit 1
fi

echo "Running PHPunit tests"
retcode=0
cd tests
phpunit --verbose --log-junit $SELENIUM_RC_DIR/phpunit-selenium.xml $@ $testsuite || retcode=$?
cd ..
# on debian
killall -9 firefox-bin
# on centos
killall -9 firefox
# kill java stuffs
killall -QUIT java
exit $retcode
