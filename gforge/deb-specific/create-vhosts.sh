#! /bin/sh

set -e

/usr/lib/gforge/bin/prepare-vhosts-file.pl
if [ -x /usr/sbin/apache ]; then
    invoke-rc.d apache reload
fi
if [ -x /usr/sbin/apache-ssl ]; then
    invoke-rc.d apache-ssl reload
fi
