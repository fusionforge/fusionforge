#! /bin/sh

# Reinitialize contents of the database to pass new tests (using the backup made in from /root/dump)

# define some convenience functions

is_db_up () {
    echo "select count(*) from users;" | su - postgres -c "psql $database" > /dev/null 2>&1
}

start_database () {

    echo "Starting the database"
    if type invoke-rc.d 2>/dev/null
    then
	invoke-rc.d postgresql start
    else
	service postgresql start
    fi

    echo "Waiting for database to be up..."
    i=0
    while [ $i -lt 10 ] && ! is_db_up ; do
        echo "...not yet ($(date))..."
        i=$(( $i + 1 ))
        sleep 5
    done
    if is_db_up ; then
        echo "...OK"
    else
        echo "... FAIL: database still down?"
    fi
}

stop_database () {

    echo "Stopping the database"
    if type invoke-rc.d 2>/dev/null
    then
	invoke-rc.d postgresql stop
    else
	service postgresql stop
    fi

    echo "Waiting for database to be down..."
    i=0
    while [ $i -lt 10 ] && is_db_up ; do
        echo "...not yet ($(date))..."
        i=$(( $i + 1 ))
        sleep 5
    done
    if ! is_db_up ; then
        echo "...OK"
    else
        echo "... FAIL: database still up?"
    fi
}

start_apache () {

    echo "Starting apache"
    if type invoke-rc.d 2>/dev/null
    then
    	invoke-rc.d apache2 start
    else
    	service httpd start
    fi
}

stop_apache () {

    echo "Stopping apache"
    if type invoke-rc.d 2>/dev/null
    then
	invoke-rc.d apache2 stop
    else
	service httpd stop
    fi
}

# Now the main program

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

stop_apache

stop_database

# If the backup is there, restore it (it should now have been created by install.sh)
if [ -d /var/lib/postgresql.backup ]; then

    echo "Restore database from files backup (/var/lib/postgresql.backup/)"
    rm -rf /var/lib/postgresql
    cp -a --reflink=auto /var/lib/postgresql.backup /var/lib/postgresql

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
        echo "Perform files backup to /var/lib/postgresql.backup/"
        cp -a --reflink=auto /var/lib/postgresql /var/lib/postgresql.backup

    else
 	# TODO: reinit the db from scratch and create the dump
 	echo "Couldn't restore the database: No /root/dump found"
 	exit 2
    fi
fi

start_database

start_apache

echo "Flushing/restarting nscd"
rm -f /var/cache/nscd/* || true
if type invoke-rc.d 2>/dev/null
then
    invoke-rc.d unscd restart || invoke-rc.d nscd restart || true
else
    service unscd restart || service nscd restart || true
fi
