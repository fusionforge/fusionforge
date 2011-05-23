#!/bin/sh

scriptdir=`dirname $0`
absolutedir=`cd $scriptdir;pwd`
plugindir=`dirname $absolutedir`

LINKS=$plugindir/packaging/links/plugin-mediawiki
cat $LINKS | while read src dest
do
	if [ ! -e /$dest ]
	then
		echo "Symlinking /$dest -> /$src"
		ln -s /$src /$dest
	fi
done
