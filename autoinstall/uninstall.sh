#!/bin/bash
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

# Aggressive desinstall for testing a clean re-install

if [ -e /etc/debian_version ]; then
    aptitude purge ~nforge ~npostgres ~nnss-pgsql ~napache2 ~nphp ~npostfix ~nexim4

elif [[ ! -z `cat /etc/os-release | grep "SUSE"` ]]; then
    zypper remove -y 'fusionforge*' postgresql-server
    rm -f /etc/cron.d/fusionforge-*
else
    yum remove -y 'fusionforge*' postgresql
    rm -f /etc/cron.d/fusionforge-*
fi
service postgresql stop
rm -rf /usr/share/fusionforge /usr/local/share/fusionforge /etc/fusionforge /var/lib/fusionforge
rm -rf /root/dump /var/lib/postgresql*/ /var/lib/pgsql*/
