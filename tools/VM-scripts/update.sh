#! /bin/sh

# Authors :
#  Roland Mas
#  Olivier BERGER <olivier.berger@it-sudparis.eu>

# This script will update the system and the checked-out branch to the
# latest state to be tested.

# Prerequisite : running 'sh scripts/configure-scripts.sh' once

# First, make sure the Debian distro is up-to-date
aptitude update
aptitude -y dist-upgrade

# Make sure to add tools needed for build.sh
aptitude -y install mini-dinstall dput devscripts equivs
mk-build-deps -i /root/fusionforge/src/debian/control -t 'aptitude -y' -r

set -e

cd /root/fusionforge

# If using bzr, or git, update accordingly
if [ -d .bzr/ ] ; then
    bzr update
else
    git pull
fi

# If available, install the JUnit OSLC provider test suite
if [ -d src/plugins/oslc/tests ]; then
    cd /root/fusionforge/src/plugins/oslc/tests
    ./setup-provider-test.sh
fi

# Test dependencies
aptitude -y install php5-cli phpunit phpunit-selenium default-jre iceweasel
