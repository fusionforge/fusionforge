#! /bin/sh -e

# Setup Env
relativepath=`dirname $0`
absolutesourcepath=`cd $relativepath/../..; pwd`
cd $absolutesourcepath
BUILDERDIR=$(./tests/scripts/builder_get_config.sh BUILDERDIR)

# Build selenium
make -C 3rd-party/selenium -f Makefile.rpm

