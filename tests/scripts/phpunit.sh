#! /bin/sh

set -x

if [ $# -ge 1 ]
then
        testsuite=$1
	shift
else
        echo "You must give the testsuite to run :"
	echo "	- DEBDebian60Tests.php"
	echo "	- RPMCentos52Tests.php"
	echo "	- TarCentos52Tests.php"
fi
if [ "x$testsuite" = "x" ]
then
        echo "Forge test suite not found"
        exit 1
fi

WORKSPACE=/root
[ ! -f tests/config/default ] || . tests/config/default
[ ! -f $WORKSPACE/config/phpunit ] || . $WORKSPACE/config/phpunit
SELENIUM_RC_DIR=/var/log
SELENIUM_RC_URL=${HUDSON_URL}job/${JOB_NAME}/ws/reports
SELENIUM_RC_HOST=`hostname -f`
HOST=`hostname -f`
CONFIG_PHP=func/config.php
export SELENIUM_RC_DIR WORKSPACE SELENIUM_RC_URL SELENIUM_RC_HOST HOST DB_NAME DB_USER CONFIG_PHP

cat <<-EOF >tests/func/config.php
<?php
// Host where selenium-rc is running
define ('SELENIUM_RC_HOST', getenv('SELENIUM_RC_HOST'));
define ('SELENIUM_RC_DIR', getenv('SELENIUM_RC_DIR'));

// The forge's hostname
define ('HOST', getenv('HOST'));

// Base URL where FusionForge is installed
define ('ROOT', '');

// Database connection parameters.
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASSWORD', '@@FFDB_PASS@@');
define('DB_INIT_CMD', "/root/tests/func/db_reload.sh >>/var/log/db_reload_selenium.log 2>>/var/log/db_reload_selenium.errlog");

// this should be an existing user of the forge together with its password
// (the password should be different from 'myadmin')
define ('FORGE_ADMIN_USERNAME', '$FORGE_ADMIN_USERNAME');
define ('FORGE_ADMIN_PASSWORD', '$FORGE_ADMIN_PASSWORD');
define ('FORGE_OTHER_PASSWORD', '$FORGE_OTHER_PASSWORD');

// Where CLI is installed
define ('CLI_CMD', '/opt/gforge/acde/tools/gforge-cli/gforge.php');

// Where Java CLI is installed
define ('JAGOSI_CMD', '/opt/gforge/acde/tools/gforge-java-cli/');

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

retcode=0
echo "This will run phpunit tests"
killall -9 java
LANG=C java -jar selenium-server.jar -browserSessionReuse -singleWindow >/dev/null &
#LANG=C java -jar selenium-server.jar -singleWindow >/dev/null &
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
