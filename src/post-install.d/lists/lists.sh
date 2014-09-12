#!/bin/bash -e
# Configure Mailman
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
	if grep -q ^7 /etc/debian_version 2>/dev/null; then
	    # Fix http://bugs.debian.org/603904
	    # was: drwxrws--- 2 list www-data
	    # now: drwxrws--- 2 www-data list
	    chown www-data:list /var/lib/mailman/archives/private
	    chmod 2770 /var/lib/mailman/archives/private
	fi
	# Managed by mailman, but referencing it to document where it is:
	# echo "Use 'mmsitepass' to set the Mailman master password"
	# echo "Cf. /var/lib/mailman/data/adm.pw"
	;;
    *)
	echo "Usage: $0 {configure}"
	exit 1
	;;
esac
