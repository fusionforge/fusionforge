#! /bin/sh

set -e

/usr/lib/gforge/bin/prepare-vhosts-file.pl
/usr/sbin/invoke-rc.d apache reload
