#!/bin/bash
# Start systasksd on boot
#
# Copyright (C) 2014, 2015  Inria (Sylvain Beucler)
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

. $(forge_get_config source_path)/post-install.d/common/service.inc

case "$1" in
    configure)
	if [ -x /sbin/chkconfig ]; then
	    chkconfig fusionforge-systasksd on
	else
	    update-rc.d fusionforge-systasksd defaults
	fi
	# not 'start' as systemd will no-op if systasksd started and exited
	service fusionforge-systasksd restart
	;;

    remove)
	service fusionforge-systasksd stop
	if [ -x /sbin/chkconfig ]; then
	    chkconfig --del fusionforge-systasksd
	else
	    update-rc.d fusionforge-systasksd remove
	fi
	;;

    purge)
	rm -f $(forge_get_config log_path)/systasksd.stdout
	rm -f $(forge_get_config log_path)/systasksd.stderr
	;;

    *)
	echo "Usage: $0 {configure|remove}"
	exit 1
	;;
esac
