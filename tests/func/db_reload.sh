#! /bin/sh

echo "Cleaning up the database"
if type invoke-rc.d 
then
	invoke-rc.d apache2 stop
else
	service httpd stop
fi

invoke-rc.d postgresql restart
su - postgres -c "dropdb -e gforge"
echo "Executing: pg_restore -C -d template1 < /root/dump"
su - postgres -c "pg_restore -C -d template1" < /root/dump

if type invoke-rc.d 
then
	invoke-rc.d apache2 start
else
	service httpd start
fi
