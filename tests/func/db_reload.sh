#! /bin/sh

echo "Cleaning up the database"
invoke-rc.d apache2 stop

invoke-rc.d postgresql restart
su - postgres -c "dropdb -e gforge"
echo "Executing: pg_restore -C -d template1 < /root/dump"
su - postgres -c "pg_restore -C -d template1" < /root/dump

invoke-rc.d apache2 start
