#!/bin/sh
# Run the syntax and unit tests (i.e. not func/)
. $(dirname $0)/common-functions

set -ex

get_config
prepare_workspace

# apt-get install pcregrep moreutils xalan
cd tests/
phpunit --configuration buildbot-configuration-phpunit.xml \
	--log-junit $WORKSPACE/reports/phpunit.xml \
	code_and_unit_tests.php
