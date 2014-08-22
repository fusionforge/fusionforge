#!/bin/bash

# Build .rpm packages

# Install build dependencies
yum install -y gettext tar bzip2 rpm-build  createrepo

# Build package
cd /usr/src/fusionforge/src/
mkdir -p ../build/SOURCES/
TAR_PREFIX=fusionforge-$(make version)
make dist && mv $TAR_PREFIX.tar.bz2 ..
ln -nfs ../../$TAR_PREFIX.tar.bz2 ../build/SOURCES/
chown -h root: ../build/SOURCES/$TAR_PREFIX.tar.bz2
chown root: ../$TAR_PREFIX.tar.bz2 # srpm
#chown root: fusionforge.spec
cp fusionforge.spec ../build/SPECS/ && rpmbuild ../build/SPECS/fusionforge.spec --define "_topdir $(pwd)/../build" -ba

(cd ../build/RPMS/ && createrepo .)
cat <<EOF > /etc/yum.repos.d/local.repo
[local]
name=local
baseurl=file:///usr/src/fusionforge/build/RPMS/
enabled=1
gpgcheck=0
EOF
