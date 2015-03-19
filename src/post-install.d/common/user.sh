#!/bin/bash
# Create to-be-specified 'fusionforge' user
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

system_user=$(forge_get_config system_user)
data_path=$(forge_get_config data_path)

case "$1" in
    configure)
	# TODO: specify the role of this user and its permissions
	# Currently used in: plugin-scmbzr, plugin-moinmoin, ???
	if ! getent passwd $system_user >/dev/null; then
	    useradd $system_user -s /bin/false -M -d $data_path
	fi
	;;

    remove)
	;;

    purge)
	# note: can't be called from Debian's postrm - reproduced there
	userdel $system_user
	# *not* removing $data_path automatically, let's play safe
	;;

    *)
	echo "Usage: $0 {configure|purge}"
	exit 1
	;;
esac
