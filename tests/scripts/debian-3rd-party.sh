#! /bin/sh

set -e

# Setup Env
relativepath=`dirname $0`
absolutesourcepath=`cd $relativepath/../..; pwd`
cd $absolutesourcepath
BUILDERDIR=$(./tests/scripts/builder_get_config.sh BUILDERDIR)
REPOPATH=$(./tests/scripts/builder_get_config.sh REPOPATH)

DISTS="wheezy jessie"

[ ! -d $REPOPATH/debian ] || rm -r $REPOPATH/debian
mkdir -p $REPOPATH/debian/conf
DEFAULTKEY=buildbot@$(hostname -f)
SIGNKEY=${DEBEMAIL:-$DEFAULTKEY}

:> $REPOPATH/debian/conf/distributions
for DIST in $DISTS ; do
    cat >> $REPOPATH/debian/conf/distributions <<EOF
Codename: $DIST
Suite: $DIST
Components: main
UDebComponents: main
Architectures: amd64 i386 source
Origin: buildbot.fusionforge.org
Description: FusionForge 3rd-party autobuilt repository
SignWith: $SIGNKEY

EOF
done

# Build selenium packages for Wheezy+Jessie
for DIST in $DISTS ; do
    make -C 3rd-party/selenium DIST=$DIST
done

# Write key
gpg --export --armor > ${REPOPATH}/debian/key

