#!/bin/bash

# Build .rpm packages
set -e

# Install build dependencies
yum install -y gettext tar bzip2 rpm-build  createrepo
yum install -y php-cli  # rpm/gen_spec.sh

# Build package
cd /usr/src/fusionforge/src/
version="$(make version)"
snapshot="+$(date +%Y%m%d%H%M)"
rpm/gen_spec.sh $version $snapshot
make dist
mkdir -p ../build/SOURCES/ ../build/SPECS/
mv fusionforge-$(make version).tar.bz2 ../build/SOURCES/fusionforge-$version$snapshot.tar.bz2
chown -h root: ../build/SOURCES/fusionforge-$version$snapshot.tar.bz2
cp fusionforge.spec ../build/SPECS/
rpmbuild ../build/SPECS/fusionforge.spec --define "_topdir $(pwd)/../build" -ba

(cd ../build/RPMS/ && createrepo .)
cat <<EOF > /etc/yum.repos.d/local.repo
[local]
name=local
baseurl=file:///usr/src/fusionforge/build/RPMS/
enabled=1
gpgcheck=0
EOF
