#!/bin/sh
# syntax and unit tests (i.e. not func/)
. $(dirname $0)/common-functions

set -ex

get_config
prepare_workspace

# apt-get install pcregrep moreutils xalan
cd tests
# phpunit --log-junit $WORKSPACE/reports/phpunit.xml \
#	--coverage-clover $WORKSPACE/reports/coverage/clover.xml \
#	--coverage-html $WORKSPACE/reports/coverage/ \
#	code_and_unit_tests.php
phpunit --log-junit $WORKSPACE/reports/phpunit.xml \
	code_and_unit_tests.php
cp $WORKSPACE/reports/phpunit.xml $WORKSPACE/reports/phpunit.xml.org
xalan -in $WORKSPACE/reports/phpunit.xml.org -xsl unit/fix_phpunit.xslt \
      -out $WORKSPACE/reports/phpunit.xml
