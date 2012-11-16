#! /bin/sh -e

# Setup Env
relativepath=`dirname $0`
absolutesourcepath=`cd $relativepath/../..; pwd`
cd $absolutesourcepath
BUILDERDIR=$(./tests/scripts/builder_get_config.sh BUILDERDIR)
REPOPATH=$(./tests/scripts/builder_get_config.sh REPOPATH)

DIST=wheezy

[ ! -d $REPOPATH/debian ] || rm -r $REPOPATH/debian
mkdir -p $REPOPATH/debian/conf
DEFAULTKEY=buildbot@$(hostname -f)
SIGNKEY=${DEBEMAIL:-$DEFAULTKEY}
cat > $REPOPATH/debian/conf/distributions <<EOF
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
gpg --export --armor > ${REPOPATH}/debian/key

