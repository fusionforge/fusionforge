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
