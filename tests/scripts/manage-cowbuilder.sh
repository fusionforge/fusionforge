#! /bin/sh

# Setup Env
relativepath=`dirname $0`
absolutetestspath=`cd $relativepath/..; pwd`
cd $absolutetestspath

echo "Read config from tests/config/default"
. config/default
if [ -f config/`hostname` ]
then
	echo "Read config from config/`hostname`"
	. config/`hostname`
fi
BUILDERDIR=${BUILDERDIR:-$HOME/builder/}

# Prepare and/or update cowbuilder caches
DISTROLIST=${DISTROLIST:-"squeeze wheezy"}

[ -d $BUILDERDIR/config ] || mkdir $BUILDERDIR/config

for DIST in $DISTROLIST ; do
    COWBUILDERCONFIG=$BUILDERDIR/config/$DIST.config

    cat > $COWBUILDERCONFIG <<EOF
PDEBUILD_PBUILDER=cowbuilder
BASEPATH=$BUILDERDIR/cow/base-$DIST-amd64.cow
BUILDPLACE=$BUILDERDIR/buildplace
BUILDRESULT=$BUILDERDIR/result
APTCACHEHARDLINK="no"
APTCACHE="/var/cache/pbuilder/aptcache"
PBUILDERROOTCMD="sudo HOME=${HOME}"
EOF
   
    if [ -d $BUILDERDIR/cow/base-$DIST-amd64.cow ] ; then
	sudo cowbuilder --update --configfile $COWBUILDERCONFIG --debootstrapopts --variant=buildd
    else
	sudo cowbuilder --create --distribution $DIST --configfile $COWBUILDERCONFIG --mirror $DEBMIRROR --debootstrapopts --variant=buildd
    fi
done
