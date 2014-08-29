#!/bin/bash -e
# Backup the DB, so that it can be restored for the test suite
# Usually called from install.sh right after the first install (clean DB)

if [ ! -e /root/dump ]; then
    forge_set_password admin myadmin
    su - postgres -c "pg_dumpall" > /root/dump
    service postgresql stop
    pgdir=/var/lib/postgresql
    if [ -e /etc/redhat-release ]; then pgdir=/var/lib/pgsql; fi
    if [ -d $pgdir.backup ]; then
        rm -fr $pgdir.backup
    fi
    cp -a --reflink=auto $pgdir $pgdir.backup
    service postgresql start
fi
