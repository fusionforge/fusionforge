#! /bin/sh

set -e

theme=$1
version=$2

rm -f gforge-theme-$theme-$version.orig.tar
rm -f gforge-theme-$theme-$version.orig.tar.gz

tar cf gforge-theme-$theme-$version.orig.tar --no-recursion gforge-theme-$theme

find gforge-theme-$theme/www/ -name CVS -type d -prune -or -print | xargs tar rf gforge-theme-$theme-$version.orig.tar --no-recursion

gzip gforge-theme-$theme-$version.orig.tar
