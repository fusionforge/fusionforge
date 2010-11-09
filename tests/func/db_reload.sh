#! /bin/sh

invoke-rc.d apache2 stop
invoke-rc.d postgresql restart
su - postgres -c "dropdb gforge"
su - postgres -c "pg_restore -C -d template1" < /root/dump
invoke-rc.d apache2 start
