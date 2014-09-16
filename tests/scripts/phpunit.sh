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

// Database connection parameters.
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASSWORD', '@@FFDB_PASS@@');
define('DB_INIT_CMD', "$FORGE_HOME/tests/func/db_reload.sh >>/var/log/db_reload_selenium.log 2>&1");

// Prefix for commands to run
define('RUN_COMMAND_PREFIX', '');

// Cronjob wrapper script location
print "Looking for forge_run_job script...\n";
if (is_executable ("$FORGE_HOME/bin/forge_run_job")) {
    print "Found in $FORGE_HOME/bin/\n";
    define('RUN_JOB_PATH', "$FORGE_HOME/bin/");
} elseif (is_executable ("$FORGE_HOME/utils/forge_run_job")) {
    print "Found in $FORGE_HOME/utils/\n";
    define('RUN_JOB_PATH', "$FORGE_HOME/utils/");
} elseif (is_executable ("$FORGE_HOME/src/utils/forge_run_job")) {
    print "Found in $FORGE_HOME/src/utils/\n";
    define('RUN_JOB_PATH', "$FORGE_HOME/src/utils/");
} else {
    print "Neither $FORGE_HOME/bin/forge_run_job, nor $FORGE_HOME/utils/forge_run_job, nor $FORGE_HOME/src/utils/forge_run_job seem to be executable, strange.\n";
    exit(1);
}   

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
t=$(mktemp)
timeout=200
PATH=/usr/lib/iceweasel:$PATH LANG=C java -jar /usr/share/selenium/selenium-server.jar -trustAllSSLCertificates -singleWindow > $t 2>&1 &
i=0
while [ $i -lt $timeout ] && ! netstat -tnl 2>/dev/null | grep -q :4444 ; do
    sleep 1
    i=$(($i+1))
done
if [ $i = $timeout ] ; then
    echo "Selenium failed to start within $timeout seconds:"
    echo -----
    cat $t
    netstat -tnl
    echo -----
    echo "Trying again."
    PATH=/usr/lib/iceweasel:$PATH LANG=C java -jar /usr/share/selenium/selenium-server.jar -trustAllSSLCertificates -singleWindow > $t 2>&1 &
    i=0
    while [ $i -lt $timeout ] && ! netstat -tnl 2>/dev/null | grep -q :4444 ; do
	sleep 1
	i=$(($i+1))
    done
    if [ $i = $timeout ] ; then
	echo "Selenium failed to start within $timeout seconds:"
	echo -----
	cat $t
	echo -----
	echo "Giving up."
	exit 1
    fi
fi

echo "Running PHPunit tests"
retcode=0
cd tests
phpunit --verbose --stop-on-failure --log-junit $SELENIUM_RC_DIR/phpunit-selenium.xml $@ $testsuite || retcode=$?
cd ..
# on debian
killall -9 firefox-bin
# on centos
killall -9 firefox
# kill java stuffs
killall -QUIT java
exit $retcode
