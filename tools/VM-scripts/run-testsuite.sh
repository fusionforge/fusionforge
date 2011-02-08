#! /bin/sh

export CONFIG_PHP=/root/fusionforge/tests/func/config.php.ffsandbox

# Run the phpunit + Selenium functional tests
cd /root/fusionforge/tests
if [ "$*" = "" ] ; then
    phpunit --verbose SeleniumTests.php
else
    for i in $* ; do
	phpunit --verbose $i
    done
fi

# If available, run the JUnit OSLC provider test suite
if [ -d /root/fusionforge/src/plugins/oslc/tests ]; then
    cd /root/fusionforge/src/plugins/oslc/tests
    ./setup-provider-test.sh
    ./run-provider-tests.sh
fi
