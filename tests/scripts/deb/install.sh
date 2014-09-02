#! /bin/sh
# Install FusionForge packages from build.sh + dependencies
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

#set -x
set -e
export DEBIAN_FRONTEND=noninteractive

# fusionforge-plugin-scmbzr depends on loggerhead (>= 1.19~bzr477~),
# but wheezy only has 1.19~bzr461-1, so we need to manually "Backport"
# a more recent dependency
if grep -q ^7 /etc/debian_version && ! dpkg-query -s loggerhead >/dev/null 2>&1 ; then
    # install loggerhead with its dependencies
    # we need gdebi to make sure dependencies are installed too (simple dpkg -i won't)
    apt-get -y install gdebi-core wget
    wget -c http://snapshot.debian.org/archive/debian/20121107T152130Z/pool/main/l/loggerhead/loggerhead_1.19%7Ebzr477-1_all.deb
    gdebi --non-interactive loggerhead_1.19~bzr477-1_all.deb
fi

# Install locales-all which is a Recommends and not a Depends
if ! dpkg -l locales-all | grep -q ^ii ; then
    apt-get -y install locales-all
fi

# Install FusionForge packages
apt-get update
if dpkg-query -s fusionforge >/dev/null 2>&1; then
    # Already installed, upgrading
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive apt-get -y dist-upgrade
else
    # Initial installation
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive apt-get -y install fusionforge

    # Additional components for testsuite
    UCF_FORCE_CONFFNEW=yes apt-get install -y fusionforge-shell \
	fusionforge-plugin-scmgit fusionforge-plugin-scmsvn fusionforge-plugin-scmbzr \
	fusionforge-plugin-mediawiki fusionforge-plugin-moinmoin \
	fusionforge-plugin-blocks
fi

# Dump clean DB
if [ ! -e /root/dump ]; then $(dirname $0)/../../func/db_reload.sh --backup; fi
