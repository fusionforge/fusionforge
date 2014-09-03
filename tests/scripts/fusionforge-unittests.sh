#!/bin/sh
. tests/scripts/common-functions

set -e

get_config
prepare_workspace

# apt-get install pcregrep
cd tests; phpunit --log-junit $WORKSPACE/reports/phpunit.xml --coverage-clover $WORKSPACE/reports/coverage/clover.xml --coverage-html $WORKSPACE/reports/coverage/ AllTests.php
cp $WORKSPACE/reports/phpunit.xml $WORKSPACE/reports/phpunit.xml.org; xalan -in $WORKSPACE/reports/phpunit.xml.org -xsl fix_phpunit.xslt -out $WORKSPACE/reports/phpunit.xml
