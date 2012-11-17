#! /bin/sh -e

# Setup Env
relativepath=`dirname $0`
absolutesourcepath=`cd $relativepath/../..; pwd`
cd $absolutesourcepath
BUILDERDIR=$(./tests/scripts/builder_get_config.sh BUILDERDIR)
REPOPATH=$(./tests/scripts/builder_get_config.sh REPOPATH)

[ ! -d $REPOPATH/redhat ] || rm -r $REPOPATH/redhat
mkdir -p $REPOPATH/redhat

# Build redhat 3rd-party
make -C 3rd-party -f Makefile.rh BUILDRESULT=$REPOPATH/redhat

