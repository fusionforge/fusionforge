#!/bin/bash
# Start systasksd on boot
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

case "$1" in
    configure)
	if [ -x /sbin/chkconfig ]; then
	    chkconfig fusionforge-systasksd on
	else
	    update-rc.d fusionforge-systasksd defaults
	fi
	service fusionforge-systasksd start
	;;

    remove)
	if [ -x /sbin/chkconfig ]; then
	    chkconfig fusionforge-systasksd off
	else
	    update-rc.d fusionforge-systasksd remove
	fi
	;;

    purge)
	;;

    *)
	echo "Usage: $0 {configure|remove}"
	exit 1
	;;
esac
