#! /bin/sh
# This script will build the Debian packages to be tested
#
# Copyright (C) 2011  Roland Mas
# Copyright (C) 2011  Olivier Berger - Institut Telecom
# Copyright (C) 2014  Inria (Sylvain Beucler)
#
# This file is part of FusionForge. FusionForge is free software;
# you can redistribute it and/or modify it under the terms of the
# GNU General Public License as published by the Free Software
# Foundation; either version 2 of the Licence, or (at your option)
# any later version.
#
# FusionForge is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with FusionForge; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

set -e
export DEBIAN_FRONTEND=noninteractive

# Install build dependencies
apt-get -y install mini-dinstall dput devscripts fakeroot
apt-get -y install build-essential \
     $(grep Build-Depends /usr/src/fusionforge/src/debian/control.in | sed -e 's/Build-Depends: //' -e 's/(.*)//')
apt-get -y install php5-cli  # debian/gen_control.sh


# Populate a local Debian packages repository for APT managed with mini-dinstall
#rm -rf /usr/src/debian-repository
mkdir -p /usr/src/debian-repository

cat >/root/.mini-dinstall.conf <<EOF
[DEFAULT]
archivedir = /usr/src/debian-repository
archive_style = flat

verify_sigs = 0

generate_release = 1
release_signscript = /usr/src/fusionforge/tests/scripts/deb/mini-dinstall-sign.sh

max_retry_time = 3600
mail_on_success = false

[local]
EOF

export GNUPGHOME=/usr/src/gnupg
if [ ! -e $GNUPGHOME ]; then
    mkdir -m 700 $GNUPGHOME
    # Quick 'n Dirty hack to get entropy on VMs
    # https://bugs.launchpad.net/ubuntu/+source/gnupg/+bug/706011
    # (don't do this for a public repo!)
    apt-get install -y rng-tools
    echo HRNGDEVICE=/dev/urandom >> /etc/default/rng-tools
    service rng-tools restart
    gpg --batch --gen-key <<EOF
      Key-Type: RSA
      Key-Length: 2048
      Subkey-Type: RSA
      Subkey-Length: 2048
      Name-Real: FusionForge
      Expire-Date: 0
      %commit
EOF
fi
gpg --export FusionForge -a > /usr/src/debian-repository/key.asc
apt-key add /usr/src/debian-repository/key.asc

mini-dinstall -b

# Configure debian package building tools so as to use the local repo
if [ ! -f /root/.dput.cf ]; then
    cat > /root/.dput.cf <<EOF
[local]
fqdn = localhost
incoming = /usr/src/debian-repository/mini-dinstall/incoming 
method = local
run_dinstall = 0
allow_unsigned_uploads = yes
post_upload_command = mini-dinstall -b
allowed_distributions = local
EOF
fi

if [ ! -f /root/.devscripts ]; then
    cat > /root/.devscripts <<EOF
DEBRELEASE_UPLOADER=dput
DEBUILD_DPKG_BUILDPACKAGE_OPTS=-i
EOF
fi

# Finally, build the FusionForge packages
cd /usr/src/fusionforge/src
f=$(mktemp)
cp debian/changelog $f
version=$(dpkg-parsechangelog | sed -n 's/^Version: \([0-9.]\+\(\~rc[0-9]\)\?\).*/\1/p')+$(date +%Y%m%d%H%M)
debian/rules debian/control  # re-gen debian/control
dch --newversion $version-1 --distribution local --force-distribution "Autobuilt."
make dist
mv fusionforge-$(make version).tar.bz2 ../fusionforge_$version.orig.tar.bz2
debuild -us -uc -tc  # using -tc so 'git status' is readable

# Install built packages into the local repo
debrelease -f local
mv $f debian/changelog

# Declare the repo so that packages become installable
echo 'deb file:///usr/src/debian-repository local/' > /etc/apt/sources.list.d/local.list
apt-get update
