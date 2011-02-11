#! /bin/sh

# Authors :
#  Roland Mas
#  Olivier BERGER <olivier.berger@it-sudparis.eu>

# This script will build the Debian packages to be tested

# Prerequisite : running 'update.sh' and its prerequisites


# removed as the grep test below would break otherwise
#set -e

#set -x

# Pupulate the repo
rm -rf /root/debian-repository
mkdir -p /root/debian-repository
mini-dinstall -b

cd /root/fusionforge/src
f=$(mktemp)
cp debian/changelog $f

# The build is likely to fail if /tmp is too short.
# When filesystem is too much full, the boot scripts mount a tmpfs /tmp that is far too small to allow builds,
# but still gets unnoticed.
# We assume here that you didn't change the VM partitions layout and that /tmp is not a mounted partition.
mount | grep /tmp
if [ $? -eq 0 ]; then
    echo "WARNING: It is likely that the mounted /tmp could be too short. If you experience a build error bellow, Try make some room on the FS and reboot, first."
fi

dch --newversion 999+$(date +%Y%m%d%H%M%S)-1 --distribution local --force-distribution "Autobuilt."
debuild --no-lintian --no-tgz-check -us -uc -tc

debrelease -f local
mv $f debian/changelog

cd
