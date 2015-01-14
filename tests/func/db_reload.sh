#! /bin/sh
# Reinitialize contents of the database to pass new tests (using the backup made in from /root/dump)
# define some convenience functions

is_db_up () {
    # 'service postgresql status' is not reliable enough
    # Also postgresql processes and control tools have too many names, esp. across distos
    # Note: database shutdown might not be completed yet, use CHECKPOINT to reduce the risk
    echo "SELECT COUNT(*) FROM users;" | su - postgres -c "psql $database" > /dev/null 2>&1
}

is_db_down () {
    pgdir=/var/lib/postgresql
    if [ -e /etc/redhat-release ]; then pgdir=/var/lib/pgsql; fi
    ! (echo "SELECT COUNT(*) FROM users;" | su - postgres -c "psql $database" > /dev/null 2>&1 \
	|| find $pgdir -type f -name *.pid -size -10c | grep -q .)
}

stop_apache () {

    echo "Stopping apache"
    service $(forge_get_config apache_service) stop
}

stop_database () {
    if [ "$1" = "--force" ]; then
	# We don't care about data integrity, we're resetting it
	killall -9 postgres
    fi

    echo "Stopping the database"
    service postgresql stop

    echo "Waiting for database to be down..."
    i=0
    while [ $i -lt 50 ] && ! is_db_down ; do
        echo "...not yet ($(date))..."
        i=$(( $i + 1 ))
        sleep 1
    done
    if is_db_down ; then
        echo "...OK"
    else
        echo "... FAIL: database still up?"
    fi
}

start_database () {

    echo "Starting the database"
    service postgresql start

    echo "Waiting for database to be up..."
    i=0
    while [ $i -lt 50 ] && ! is_db_up ; do
        echo "...not yet ($(date))..."
        i=$(( $i + 1 ))
        sleep 1
    done
    if is_db_up ; then
        echo "...OK"
    else
        echo "... FAIL: database still down?"
	ps fauxww
    fi
}

start_apache () {

    echo "Starting apache"
    service $(forge_get_config apache_service) start
}


if [ "$1" = "--reset" ]; then
    reset=1
    shift
fi
if [ "$1" = "--backup" ]; then
    backup=1
    shift
fi

# Restore the DB
if [ $# -eq 1 ]
then
	database=$1
else
	scriptdir=$(dirname $0)
	if [ -d "$scriptdir/../../src" ]
	then
		UTILS_PATH=$(cd $scriptdir/../../src ; pwd)
	else
		UTILS_PATH=$(cd $scriptdir/../.. ; pwd)
	fi
	export PATH=$PATH:$UTILS_PATH/utils:$UTILS_PATH/bin
	if type forge_get_config
	then
		database=`FUSIONFORGE_NO_PLUGINS=true forge_get_config database_name`
	else
		echo "$0: FATAL ERROR : COULD NOT FIND forge_get_config"
		exit 1 
	fi
fi
if [ "x$database" = "x" ]
then
	echo "Forge database name not found"
	exit 1
else
	echo "Forge database is $database"
fi


# Reset the DB to a clean post-install state
if [ "$reset" = 1 ]; then
    set -e
    # Reset connections
    service fusionforge-systasksd stop
    service postgresql restart
    su - postgres -c "dropdb $database"
    $(forge_get_config source_path)/post-install.d/db/db.sh configure
    forge_set_password admin myadmin
    service fusionforge-systasksd start
    exit 0
fi

# Backup the DB, so that it can be restored for the test suite
if [ "$backup" = 1 ]; then
    set -e
    su - postgres -c "pg_dumpall" > /root/dump
    su postgres -c 'psql -c CHECKPOINT'  # flush to disk
    stop_database
    pgdir=/var/lib/postgresql
    if [ -e /etc/redhat-release ]; then pgdir=/var/lib/pgsql; fi
    if [ -d $pgdir.backup ]; then
        rm -fr $pgdir.backup
    fi
    cp -a --reflink=auto $pgdir $pgdir.backup
    start_database
    exit 0
fi


stop_apache

stop_database --force

if [ -d /var/lib/postgresql ] ; then
    dbdir=/var/lib/postgresql
elif [ -d /var/lib/pgsql ] ; then
    dbdir=/var/lib/pgsql
else
    echo "Database dir not found"
    exit 1
fi

# SCM
for i in arch bzr cvs darcs git hg svn ; do
    repopath=`FUSIONFORGE_NO_PLUGINS=true forge_get_config repos_path scm$i`
    if [ -d "$repopath" ] && ls $repopath | grep -q .. ; then
	echo "Removing $i repositories"
	rm -rf $repopath/*
    fi
done
# Wikis
rm -rf $(forge_get_config data_path)/plugins/mediawiki/projects/*
rm -rf $(forge_get_config data_path)/plugins/moinmoin/wikidata/project*
# Conf
rm -f $(forge_get_config config_path)/config.ini.d/zzz-buildbot-*
# SSH
rm -rf $(forge_get_config homedir_prefix) #no trailing slash
rm -rf $(forge_get_config groupdir_prefix) #no trailing slash
# Too risky
#rm -f ~/.ssh/id_rsa ~/.ssh/id_rsa.pub ~/.ssh/known_hosts

# If the backup is there, restore it (it should now have been created by install.sh)
if [ -d $dbdir.backup ]; then

    echo "Restore database from files backup ($dbdir.backup/)"
    rm -rf $dbdir
    cp -a --reflink=auto $dbdir.backup $dbdir

    pg_conf=$(ls /etc/postgresql/*/*/postgresql.conf /var/lib/pgsql/data/postgresql.conf 2>/dev/null | tail -1)

    for i in fsync synchronous_commit full_page_writes ; do
	if ! grep -q "^$i\b" $pg_conf; then
	    echo "$i = off" >> $pg_conf
	fi
    done
else
    # We will restore from the dump, then perform a backup so that it's there next time
    sleep 3
    start_database

    # install.sh should have created it, if not, then nothing much we can do
    if [ -f /root/dump ]
    then
        echo "Dropping database $database"
        su - postgres -c "dropdb -e $database"

 	echo "Restore database from dump file: psql -f- < /root/dump"
 	su - postgres -c "psql -f-" < /root/dump > /var/log/pg_restore.log 2>/var/log/pg_restore.err

        # Perform a file backup which will now be faster to restore, next time (align with new install.sh behaviour)
        stop_database
        echo "Perform files backup to $dbdir.backup/"
        cp -a --reflink=auto $dbdir $dbdir.backup

    else
 	# TODO: reinit the db from scratch and create the dump
 	echo "Couldn't restore the database: No /root/dump found"
 	exit 2
    fi
fi

start_database

start_apache

set -x

if [ -x /usr/sbin/nscd ]; then
    echo "Flushing/restarting nscd"
    nscd -i passwd && nscd -i group
fi
echo "nscd flushed, going on with tests"

# We may have changed plugins.plugin_id, need to reload the systasksd
service fusionforge-systasksd restart
