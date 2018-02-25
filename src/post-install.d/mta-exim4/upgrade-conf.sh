#!/bin/bash -e
# Upgrade Exim4 configuration
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

PREVVER=${1:-0.0}

# 5.3 -> 6.0
if [ $(php -r "print version_compare('$PREVVER', '5.3.50');") -eq -1 ]; then
	if [ -e /etc/exim4/conf.d/router/01_gforge_forwards ]; then
		mv /etc/exim4/conf.d/router/01_gforge_forwards \
		/etc/exim4/conf.d/router/01_fusionforge_forwards
	fi
	sed -i '/^### Next line inserted by GForge/d' /etc/aliases

	cfgs_exim4_main=''
	cfgs_exim4_router=''
	if [ -e /etc/exim4/exim4.conf.template ]; then
		cfgs_exim4_main="$cfgs_exim4_main /etc/exim4/exim4.conf.template"
		cfgs_exim4_router="$cfgs_exim4_router /etc/exim4/exim4.conf.template"
	fi
	if [ -e /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs ]; then
		cfgs_exim4_main="$cfgs_exim4_main /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs"
		# + /etc/exim4/conf.d/router/01_fusionforge_forwards entirely generated
	fi
	if [ -e /etc/exim4/exim4.conf ]; then
		cfgs_exim4_main="$cfgs_exim4_main /etc/exim4/exim4.conf"
		cfgs_exim4_router="$cfgs_exim4_router /etc/exim4/exim4.conf"
	fi
	if [ -e /etc/exim/exim.conf ]; then
		cfgs_exim4_main="$cfgs_exim4_main /etc/exim/exim.conf"
		cfgs_exim4_router="$cfgs_exim4_router /etc/exim/exim.conf"
	fi

	for i in $cfgs_exim4_main; do
		# De-configure so it can be properly re-configured with new db auth
		sed -i $i \
			-e '/^GFORGE_DOMAINS=/d' \
			-e '/^hide pgsql_servers =/d' \
			-e '/domainlist local_domains.*/ s/:GFORGE_DOMAINS//'
	done
	for i in $cfgs_exim4_router; do
		sed -i -e 's/\(^# \(BEGIN\|END\)\) GFORGE BLOCK/\1 FUSIONFORGE BLOCK/' $i
	done
fi
