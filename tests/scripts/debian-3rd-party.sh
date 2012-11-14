#! /bin/sh -e

# Setup Env
relativepath=`dirname $0`
absolutesourcepath=`cd $relativepath/../..; pwd`
cd $absolutesourcepath
BUILDERDIR=$(./tests/scripts/builder_get_config.sh BUILDERDIR)

DIST=wheezy

REPOPATH=$(./tests/scripts/builder_get_config.sh REPOPATH)

[ ! -d $REPOPATH ] || rm -r $REPOPATH
mkdir -p $REPOPATH/conf
DEFAULTKEY=buildbot@$(hostname -f)
SIGNKEY=${DEBEMAIL:-$DEFAULTKEY}
cat > $REPOPATH/conf/distributions <<EOF
Codename: $DIST
Suite: $DIST
Components: main
UDebComponents: main
Architectures: amd64 i386 source
Origin: buildbot.fusionforge.org
Description: FusionForge 3rd-party autobuilt repository
SignWith: $SIGNKEY
EOF

# Build mediawiki
make -C 3rd-party/mediawiki
# Build selenium
make -C 3rd-party/selenium
# Write key
gpg --export --armor > ${REPOPATH}/key

