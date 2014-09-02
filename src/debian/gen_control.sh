#!/bin/bash -e
# Generate control from control.in + ./plugins + plugin_pkg_desc.php
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

(
    cat debian/control.in
    echo
    for i in $(sed -n 's/^Package: fusionforge-plugin-//p' debian/plugins); do
	sed -n -e '/^#/d' -e "/^Package: fusionforge-plugin-$i/,/^$/p" debian/plugins \
	    | grep -v ^$ \
	    | sed 's/Depends:\(.*\)/Depends: fusionforge-common (=${source:Version}),\1, ${misc:Depends}/'
	echo "Architecture: all"
	php utils/plugin_pkg_desc.php $i deb
	echo
    done
) > debian/control
