#! /bin/sh

set -e

su -s /bin/sh gforge -c /usr/lib/gforge/bin/prepare-vhosts-file.pl
if [ -x /usr/sbin/apache ]; then
    /usr/sbin/invoke-rc.d apache reload > /dev/null 2>&1
fi
if [ -x /usr/sbin/apache-ssl ]; then
    /usr/sbin/invoke-rc.d apache-ssl reload > /dev/null 2>&1
fi
if [ -x /usr/sbin/apache-perl ]; then
    /usr/sbin/invoke-rc.d apache-perl reload > /dev/null 2>&1
fi
