#! /bin/bash

for i in src/utils utils ../src/utils ../utils ; do
    if [ -e $i/filter-sql-dump.php ] ; then
	s=$i/filter-sql-dump.php
    fi
done
if [ "$s" = "" ] ; then
    echo "Couldn't find filter script..."
    exit 1
fi

diff -b -u10 <($s $1) <($s $2)
