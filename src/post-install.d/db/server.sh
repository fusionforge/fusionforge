#!/bin/bash
# Configure local PostgreSQL server
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

database_name=$(forge_get_config database_name)
database_user=$(forge_get_config database_user)

case "$1" in
    configure)
	# Create default configuration files if needed
	if [ -e /etc/redhat-release ]; then
	    if type postgresql-setup >/dev/null 2>&1; then
		postgresql-setup initdb >/dev/null || true
	    else
		service postgresql initdb >/dev/null || true  # deprecated in Fedora
	    fi
	    chkconfig postgresql on
	fi
	if [ -e /etc/SuSE-release ]; then
	    service postgresql start  # creates initial db
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
	sed -i -e '/^### BEGIN FUSIONFORGE BLOCK/,/^### END FUSIONFORGE BLOCK/ { ' -e 'ecat' -e 'd }' $pg_hba <<-EOF
		### BEGIN FUSIONFORGE BLOCK -- DO NOT EDIT
		# single-host configuration
		local $database_name ${database_user}_nss     trust
		local $database_name ${database_user}_mta     md5
		local $database_name ${database_user}_ssh_akc md5
		# multi-host configuration
		host  $database_name ${database_user}_nss     0.0.0.0/0 trust
		host  $database_name ${database_user}_mta     0.0.0.0/0 md5
		host  $database_name ${database_user}_ssh_akc 0.0.0.0/0 md5
		host  $database_name all 0.0.0.0/0 md5
		### END FUSIONFORGE BLOCK -- DO NOT EDIT
		EOF

	# Multi-host connection
	restart=0
	if ! grep -q '^listen_addresses\b' $pg_conf; then
	    echo "listen_addresses='0.0.0.0'" >> $pg_conf
	    restart=1
	fi

	if [ $restart = 1 ] || ! service postgresql status >/dev/null; then
	    service postgresql restart
	else
	    service postgresql reload
	fi
	;;
    
    remove)
	if [ -e "$pg_hba" ]; then
	    sed -i -e '/^### BEGIN FUSIONFORGE BLOCK/,/^### END FUSIONFORGE BLOCK/d' $pg_hba
	fi
	;;

    *)
	echo "Usage: $0 {configure|remove}"
	exit 1
	;;
esac
