#! /bin/sh -e

# Setup Env
relativepath=`dirname $0`
absolutesourcepath=`cd $relativepath/../..; pwd`
cd $absolutesourcepath

echo "Read config from tests/config/default"
. tests/config/default
if [ -f tests/config/`hostname` ]
then
        echo "Read config from tests/config/`hostname`"
        . tests/config/`hostname`
fi
BUILDERDIR=${BUILDERDIR:-$HOME/builder/}
DIST=wheezy
COWBUILDERCONFIG=$BUILDERDIR/config/$DIST.config

# Setup Repo
WORKDIR=$(cd $absolutesourcepath/..; pwd)
# Jenkins will set WORKSPACE
WORKSPACE=${WORKSPACE:-$WORKDIR}

# Build selenium
make -C 3rd-party/selenium COWBUILDERCONFIG=$COWBUILDERCONFIG REPOPATH=$REPOPATH rpm

