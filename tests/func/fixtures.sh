#!/bin/bash
# Reinitialize the system to base or fixture'd state (database, SCM
# repos, plugins data...) to pass new tests

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
    if [ "$1" = "--force" ]; then
	# We don't care about data integrity, avoid default lengthy 'graceful-stop'
	# (not -9, otherwise `ipcs -s` entries pile up)
	killall $(forge_get_config apache_service)
    fi
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
        echo "OK"
    else
        echo "FAIL: database still up?"
    fi

    # Work-around http://bugs.debian.org/759725
    if [ -f /etc/debian_version -a -x /bin/systemctl ]; then
        sleep 1  # bleh
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
if [ "$1" = "--exists" ]; then
    exists=1
    shift
fi

if [ $# -eq 1 ]
then
    fixture=$1
else
    fixture='base'
fi

pgdir=/var/lib/postgresql
if [ -e /etc/redhat-release ]; then pgdir=/var/lib/pgsql; fi
if [ ! -d $pgdir ]; then
    echo "Database dir not found"
    exit 1
fi

database=$(forge_get_config database_name)
if [ "x$database" = "x" ]
then
    echo "Forge database name not found"
    exit 1
fi

# Check if requested DB fixture exists
if [ "$exists" = 1 ]; then
    if [ -d $pgdir.backup-$fixture ]; then
	exit 0
    else
	exit 1
    fi
fi

# Reset the DB to a clean post-install state
if [ "$reset" = 1 ]; then
    set -e
    # Reset connections
    stop_apache --force || true
    service fusionforge-systasksd stop
    service postgresql restart
    su - postgres -c "dropdb $database" || true
    $(forge_get_config source_path)/post-install.d/db/db.sh configure
    forge_set_password admin 'my_Admin7'
    service fusionforge-systasksd start
    start_apache
    rm -rf $pgdir.backup-*/
    exit 0
fi

# Backup the DB, so that it can be restored for the test suite
if [ "$backup" = 1 ]; then
    set -e
    stop_apache --force || true  # work-around systemd's "Job for apache2.service canceled."
    su - postgres -c 'psql -c CHECKPOINT'  # flush to disk
    stop_database
    rm -fr $pgdir.backup-$fixture/*
    # support /var/lib/pgsql as a symlink to tmpfs
    mkdir -p $(readlink -f $pgdir.backup-$fixture)
    cp -a --reflink=auto $pgdir/* $pgdir.backup-$fixture/
    start_database
    start_apache
    exit 0
fi


# Else, restore clean state

service fusionforge-systasksd stop
stop_apache --force
stop_database --force

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

# If the backup is there, restore it
if [ -d $pgdir.backup-$fixture/ ]; then
    echo "Restore database from files backup ($pgdir.backup-$fixture/)"
    rm -rf $pgdir/*
    cp -a --reflink=auto $pgdir.backup-$fixture/* $pgdir/

    pg_conf=$(ls /etc/postgresql/*/*/postgresql.conf /var/lib/pgsql/data/postgresql.conf 2>/dev/null | tail -1)

    for i in fsync synchronous_commit full_page_writes ; do
	if ! grep -q "^$i\b" $pg_conf; then
	    echo "$i = off" >> $pg_conf
	fi
    done
else
    echo "Couldn't restore the database: $pgdir.backup-$fixture/ not found"
    exit 2
fi

if [ -x /usr/sbin/nscd ]; then
    echo "Flushing/restarting nscd"
    nscd -i passwd && nscd -i group
fi

start_database
start_apache
service fusionforge-systasksd start
