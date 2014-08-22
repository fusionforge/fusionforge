#! /bin/sh

# Authors :
#  Roland Mas
#  Olivier BERGER <olivier.berger@it-sudparis.eu>

# This script will update the system and the checked-out branch to the
# latest state to be tested.

# First, make sure the Debian distro is up-to-date
aptitude update
aptitude -y dist-upgrade

set -e

# Then update the checked-out sources of FusionForge
cd /usr/src/fusionforge/
# If using bzr, or git, update accordingly
if [ -d .bzr/ ] ; then
    bzr update
else
    git pull
fi
