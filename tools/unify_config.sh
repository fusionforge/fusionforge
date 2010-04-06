#! /bin/sh

# Usage: unify_config.sh <old variable> <new variable> [ <section> ]
# Example: unify_config.sh sys_default_domain web_host

old=$1
new=$2
sect=$3

if [ "$sect" = "" ] ; then
    newstr="forge_get_config('$new')"
else
    newstr="forge_get_config('$new', '$sect')"
fi

find_files () {
    ack-grep -l --php $old | grep -v www/include/pre.php
}

find_files | xargs perl -pi -e"s/(\\s*global .*)\\\$$old, */\\1/"
find_files | xargs perl -pi -e"s/(\\s*global .*)\\\$$old *;//"
find_files | xargs perl -pi -e"s,\\\$GLOBALS\['$old'\](?"\!"\\s*=),$newstr,g"
find_files | xargs perl -pi -e"s,\\\$GLOBALS\[$old\](?"\!"\\s*=),$newstr,g"
find_files | xargs perl -pi -e"s,\\\$$old(?"\!"\\s*=),$newstr,g"
