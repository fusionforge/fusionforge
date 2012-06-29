#! /bin/sh

# Prepare and/or update cowbuilder caches

COWBUILDERBASE=/var/lib/jenkins/builder/

for DIST in squeeze wheezy ; do
    COWBUILDERCONFIG=$COWBUILDERBASE/config/$DIST.config

    cat > $COWBUILDERCONFIG <<EOF
PDEBUILD_PBUILDER=cowbuilder
BASEPATH=$COWBUILDERBASE/cow/base-$DIST-amd64.cow
BUILDPLACE=$COWBUILDERBASE/buildplace
APTCACHEHARDLINK="no"
APTCACHE="/var/cache/pbuilder/aptcache"
PBUILDERROOTCMD="sudo HOME=${HOME}"
EOF
   
    if [ -d $COWBUILDERBASE/cow/base-$DIST-amd64.cow ] ; then
	sudo cowbuilder --update --configfile $COWBUILDERCONFIG
    else
	sudo cowbuilder --create --distribution $DIST --configfile $COWBUILDERCONFIG --mirror http://ftp.fr.debian.org/
    fi
done
