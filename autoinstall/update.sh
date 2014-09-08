#!/bin/bash

# First, make sure the distro is up-to-date
if [ -e /etc/debian_version ]; then
    aptitude update
    aptitude -y dist-upgrade
else
    yum upgrade
fi

set -e

# Then update the checked-out sources of FusionForge
cd /usr/src/fusionforge/
git pull
