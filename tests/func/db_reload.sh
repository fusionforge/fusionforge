#! /bin/sh

invoke-rc.d apache2 stop
su - postgres -c "dropdb gforge"
su - postgres -c "pg_restore -C -d template1" < /root/dump
invoke-rc.d apache2 start
