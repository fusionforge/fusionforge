#!/bin/bash
# Configure local PostgreSQL server

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
