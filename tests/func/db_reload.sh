#! /bin/sh
if [ $# -eq 1 ]
then
	database=$1
else
	export PATH=$PATH:/usr/share/gforge/bin/:/usr/share/gforge/utils:/opt/gforge/utils
	database=`FUSIONFORGE_NO_PLUGINS=true forge_get_config database_name`
fi
if [ "x$database" = "x" ]
then
	echo "Forge database name not found"
	exit 1
else
	echo "Forge database is $database"
fi

echo "Stopping apache"
if type invoke-rc.d 2>/dev/null
then
	invoke-rc.d apache2 stop
else
	service httpd stop
fi

echo "Starting the database"
if type invoke-rc.d 2>/dev/null
then
	invoke-rc.d postgresql restart
else
	service postgresql restart
fi

echo "Droping database $database"
su - postgres -c "dropdb -e $database"

if [ -f /root/dump ]
then
	echo "Restore database from dump file: psql -f- < /root/dump"
	su - postgres -c "psql -f-" < /root/dump > /var/log/pg_restore.log 2>/var/log/pg_restore.err
else
	# TODO: reinit the db from scratch and create the dump
	echo "Couldn't restore the database: No /root/dump found"
	exit 2
fi

echo "Starting apache"
if type invoke-rc.d 2>/dev/null
then
	invoke-rc.d apache2 start
else
	service httpd start
fi
