#!/bin/bash
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
. $(dirname $0)/common-backports

# Install FusionForge packages
if [ -e /etc/debian_version ]; then
    export DEBIAN_FRONTEND=noninteractive
    export UCF_FORCE_CONFFNEW=yes
    export LANG=C
    APT="apt-get -y -o Dpkg::Options::=--force-confnew"
    backports_deb
    if dpkg-query -s fusionforge >/dev/null 2>&1; then
	# Already installed, upgrading
	$APT dist-upgrade
    else
	# Initial installation
	$APT install fusionforge
	
	# Additional components for testsuite
	$APT install fusionforge-shell \
	    fusionforge-plugin-scmgit fusionforge-plugin-scmsvn fusionforge-plugin-scmbzr \
	    fusionforge-plugin-mediawiki fusionforge-plugin-moinmoin \
	    fusionforge-plugin-blocks locales-all
    fi
else
    yum install -y make
    backports_rpm
    if rpm -q fusionforge >/dev/null ; then
	yum upgrade -y
    else
	# Initial installation
	yum install -y fusionforge fusionforge-shell \
	    fusionforge-plugin-scmgit fusionforge-plugin-scmsvn \
	    fusionforge-plugin-mediawiki \
	    fusionforge-plugin-blocks fusionforge-plugin-online_help
    fi
fi

# Dump clean DB
if [ ! -e /root/dump ]; then $(dirname $0)/../tests/func/db_reload.sh --backup; fi
