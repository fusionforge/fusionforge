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
	# Managed by mailman, but referencing it to document where it is:
	# echo "Use 'mmsitepass' to set the Mailman master password"
	# echo "Cf. /var/lib/mailman/data/adm.pw"

	# Normally defined in per-list config, but needed e.g. in default empty archives page
	lists_host=$(forge_get_config lists_host)
	sed -i -e "s/^DEFAULT_EMAIL_HOST.*/DEFAULT_EMAIL_HOST = '$lists_host'/" \
	       -e "s/^DEFAULT_URL_HOST.*/DEFAULT_URL_HOST = '$lists_host'/" \
	    /etc/mailman/mm_cfg.py
	;;
    *)
	echo "Usage: $0 {configure}"
	exit 1
	;;
esac
