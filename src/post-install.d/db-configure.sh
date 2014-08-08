#!/bin/bash
# Configure PostgreSQL

pg_hba=$(ls /etc/postgresql/*/*/pg_hba.conf /var/lib/pgsql/data/pg_hba.conf 2>/dev/null | head -1)

if [ -z "$pg_hba" ]; then
    echo "Cannot find pg_hba.conf"
    return 1
fi

if [ -e /etc/redhat-release ]; then
    service postgresql initdb >/dev/null
    chkconfig postgresql on
fi
if ! service postgresql status >/dev/null; then
    service postgresql start
fi

# TODO: configure pg_hba.conf
