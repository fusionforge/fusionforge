#!/bin/bash
# Call all DB post-install scripts in order
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

source_path=$(forge_get_config source_path)

case "$1" in
    configure)
	$source_path/post-install.d/db/server.sh configure
	$source_path/post-install.d/db/populate.sh
	;;
    remove)
	$source_path/post-install.d/db/server.sh remove
	;;
    # no purge) because we don't want to remove *data* (not conf) automatically
    *)
	echo "Usage: $0 {configure|remove}"
	exit 1
	;;
esac
