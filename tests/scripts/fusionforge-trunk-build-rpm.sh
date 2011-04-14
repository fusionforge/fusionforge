#!/bin/sh -x

export FFORGE_RPM_REPO=${HUDSON_URL}job/fusionforge-trunk-build-rpm/ws/build/packages

rm -fr $WORKSPACE/build
mkdir -p $WORKSPACE/build/packages

cp source/src/rpm-specific/fusionforge.repo $WORKSPACE/build/packages/fusionforge.repo
sed -i "s#http://fusionforge.org/#${HUDSON_URL}#" $WORKSPACE/build/packages/fusionforge.repo
sed -i "s#baseurl = .*#baseurl = $FFORGE_RPM_REPO/#" $WORKSPACE/build/packages/fusionforge.repo

cd source
make -f Makefile.rh BUILDRESULT=$WORKSPACE/build/packages all
