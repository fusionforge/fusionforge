#! /bin/sh

TEST_ENV="$1"
# Test arg
if [ -z "$TEST_ENV" ]
then
	echo "Usage: $0 script"
	exit 1
fi

export INSTALL_METHOD=${TEST_ENV%/*}
export INSTALL_OS=${TEST_ENV#*/}

if [ -z "$INSTALL_METHOD" ] || [ -z "$INSTALL_OS" ] ; then
    echo INSTALL_METHOD and INSTALL_OS required
    echo Example: INSTALL_METHOD=src INSTALL_OS=centos $0
fi

scriptdir=$(dirname $0)
FORGE_HOME=$(cd $scriptdir/../..; pwd)
cd $FORGE_HOME

# Initialize defaults
[ ! -f tests/config/default ] || . tests/config/default
[ ! -f tests/config/phpunit ] || . tests/config/phpunit

SELENIUM_RC_DIR=/var/log
SELENIUM_RC_URL=${HUDSON_URL}job/${JOB_NAME}/ws/reports
SELENIUM_RC_HOST=`hostname -f`
HOST=`hostname -f`
# the PHP file provided through CONFIG_PHP will be loaded inside the functionnal test suite with require_once, in SeleniumRemoteSuite.php
CONFIG_PHP=func/config.php
export SELENIUM_RC_DIR SELENIUM_RC_URL SELENIUM_RC_HOST HOST DB_NAME DB_USER CONFIG_PHP

# Add definitions for the PHP functionnal test suite
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
// Command which will reload a clean database at each SeleniumTestCase start
define('DB_INIT_CMD', "$FORGE_HOME/tests/func/db_reload.sh 2>&1");

// Prefix for commands to run
define('RUN_COMMAND_PREFIX', '');

// Cronjob wrapper script location
print "Looking for forge_run_job script...\n";
if (is_executable ("$FORGE_HOME/bin/forge_run_job")) {
    print "Found in $FORGE_HOME/bin/\n";
    define('RUN_JOB_PATH', "$FORGE_HOME/bin/");
} elseif (is_executable ("$FORGE_HOME/bin/forge_run_job")) {
    print "Found in $FORGE_HOME/bin/\n";
    define('RUN_JOB_PATH', "$FORGE_HOME/bin/");
} elseif (is_executable ("$FORGE_HOME/src/bin/forge_run_job")) {
    print "Found in $FORGE_HOME/src/bin/\n";
    define('RUN_JOB_PATH', "$FORGE_HOME/src/bin/");
} else {
    print "Neither $FORGE_HOME/bin/forge_run_job, nor $FORGE_HOME/bin/forge_run_job, nor $FORGE_HOME/src/bin/forge_run_job seem to be executable, strange.\n";
    exit(1);
}

define('INSTALL_METHOD', getenv('INSTALL_METHOD'));
define('INSTALL_OS', getenv('INSTALL_OS'));

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
EOF

echo "Starting Selenium"
killall -9 java
timeout=300
PATH=/usr/lib/iceweasel:$PATH LANG=C java -jar /usr/share/selenium/selenium-server.jar -trustAllSSLCertificates -singleWindow &
pid=$!
i=0
while [ $i -lt $timeout ] && ! netstat -tnl 2>/dev/null | grep -q :4444 && kill -0 $pid 2>/dev/null; do
    sleep 1
    i=$(($i+1))
done
if [ $i = $timeout ] || ! kill -0 $pid 2>/dev/null; then
    echo "Selenium failed to start."
    netstat -tnl
    exit 1
fi

echo "Running PHPunit tests"
retcode=0
cd tests
phpunit --verbose --debug --stop-on-failure --log-junit $SELENIUM_RC_DIR/phpunit-selenium.xml $@ func_tests.php || retcode=$?
cd ..
kill $pid
# on debian
killall -9 firefox-bin
# on centos
killall -9 firefox
# kill java stuffs
killall -9 java
exit $retcode
