#!/bin/bash
# Build FusionForge .rpm packages
#
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

# Build .rpm packages
set -e

# Install build dependencies
yum install -y gettext tar bzip2 rpm-build  createrepo
yum install -y php-cli  # rpm/gen_spec.sh

# Build package
cd /usr/src/fusionforge/src/
version="$(make version)"
snapshot="+$(date +%Y%m%d%H%M)"
rpm/gen_spec.sh $version $snapshot
make dist
mkdir -p ../build/SOURCES/ ../build/SPECS/
mv fusionforge-$(make version).tar.bz2 ../build/SOURCES/fusionforge-$version$snapshot.tar.bz2
chown -h root: ../build/SOURCES/fusionforge-$version$snapshot.tar.bz2
cp fusionforge.spec ../build/SPECS/
rpmbuild ../build/SPECS/fusionforge.spec --define "_topdir $(pwd)/../build" -ba

(cd ../build/RPMS/ && createrepo .)
cat <<EOF > /etc/yum.repos.d/local.repo
[local]
name=local
baseurl=file:///usr/src/fusionforge/build/RPMS/
enabled=1
gpgcheck=0
EOF
