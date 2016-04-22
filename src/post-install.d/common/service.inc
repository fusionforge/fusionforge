# -*- shell-script -*-
# 
# Copyright (C) 2015  Inria (Sylvain Beucler)
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

# Debian-specific behavior for Policy Nazis
# https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=789778
if [ -e /etc/debian_version ]; then
    service () {
        invoke-rc.d $@
        ret=$?
        if [ $ret -ne 0 ]; then
            # Work-around invoke-rc.d's silence on error..
            echo "invoke-rc.d: error $ret running '$@'"
            return $ret
        fi
    }
fi

# Tests:
# set -e
# service idontexist start || echo OK
# service cron restart && echo OK
