#! /bin/sh

# Usage: drop-wiki.sh <unix-name>

project=$1

wdprefix=/var/lib/gforge/plugins/mediawiki/wikidata

# Minimal sanitisation of project name
project=$(echo $project | sed s/[^-a-zA-Z0-9_]//g)
if [ -d $wdprefix/$project ] ; then
    rm -r $wdprefix/$project
fi

schema=$(echo plugin_mediawiki_$project | sed s/-/_/g)
su -s /bin/sh postgres -c "/usr/bin/psql gforge" <<-EOF
DROP SCHEMA $schema CASCADE;
EOF
