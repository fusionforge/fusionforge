#!/bin/bash
# Configure local PostgreSQL server
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

database_name=$(forge_get_config database_name)
database_user=$(forge_get_config database_user)


# Create default configuration files if needed
if [ -e /etc/redhat-release ]; then
    service postgresql initdb >/dev/null
    chkconfig postgresql on
fi

pg_hba=$(ls /etc/postgresql/*/*/pg_hba.conf /var/lib/pgsql/data/pg_hba.conf 2>/dev/null | tail -1)
pg_conf=$(ls /etc/postgresql/*/*/postgresql.conf /var/lib/pgsql/data/postgresql.conf 2>/dev/null | tail -1)

if [ -z "$pg_hba" ]; then
    echo "Cannot find pg_hba.conf"
    exit 1
fi


# Configure connection
# Preprend configuration block
if ! grep -q '^### BEGIN FUSIONFORGE BLOCK' $pg_hba; then
    sed -i -e '1ecat' $pg_hba <<-EOF
	### BEGIN FUSIONFORGE BLOCK -- DO NOT EDIT
	### END FUSIONFORGE BLOCK -- DO NOT EDIT
	EOF
fi
# Replace configuration block
sed -i -e '/^### BEGIN FUSIONFORGE BLOCK/,/^### END FUSIONFORGE BLOCK/ { ' -e 'ecat' -e 'd }' $pg_hba <<EOF
### BEGIN FUSIONFORGE BLOCK -- DO NOT EDIT
# user which is used by libnss to access the DB (see /etc/nss-pgsql.conf)
local $database_name ${database_user}_nss trust
local $database_name list ident
local $database_name ${database_user}_mta md5
# multi-host configuration
host  $database_name ${database_user}_nss 0.0.0.0/0 trust
host  $database_name all 0.0.0.0/0 md5
### END FUSIONFORGE BLOCK -- DO NOT EDIT
EOF

# Multi-host connection
if ! grep -q '^listen_addresses\b' $pg_conf; then
    echo "listen_addresses='0.0.0.0'" >> $pg_conf
fi

if ! service postgresql status >/dev/null; then
    service postgresql start
else
    service postgresql reload
fi
if [ -x /bin/systemctl ]; then
    sleep 5  # systemd's postgresql init scripts is stupidly async
    # if you have a better way that works across distros...
fi
