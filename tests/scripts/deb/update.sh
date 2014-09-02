#!/bin/bash

# First, make sure the Debian distro is up-to-date
aptitude update
aptitude -y dist-upgrade

set -e

# Then update the checked-out sources of FusionForge
cd /usr/src/fusionforge/
git pull
