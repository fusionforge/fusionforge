#!/bin/sh

if [ $(ps ax | grep -v grep | grep "job-server.pl" | wc -l) -eq 0 ]
then
        echo "job-server.pl Service not running, relauching."
        nohup `dirname $0`/job-server.pl &
fi
