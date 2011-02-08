#! /bin/sh

aptitude update
aptitude dist-upgrade

# Make sure to add tools needed for build.sh
aptitude bzr install mini-dinstall devscripts dpatch sharutils docbook-to-man

set -e

cd /root/fusionforge

if [ -d .bzr/ ] ; then
    bzr update
else
    git pull
fi


