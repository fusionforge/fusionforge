#! /bin/sh

# Authors :
#  Roland Mas
#  Olivier BERGER <olivier.berger@it-sudparis.eu>
#  Sylvain Beucler

# This script will build the Debian packages to be tested

# Prerequisite : running 'update.sh' and its prerequisites


# removed as the grep test below would break otherwise
#set -e

#set -x


# Build dependencies
aptitude -y install mini-dinstall dput devscripts equivs
mk-build-deps -i /usr/src/fusionforge/src/debian/control -t 'apt-get -y' -r


# Populate the repo
#rm -rf /usr/src/debian-repository
mkdir -p /usr/src/debian-repository

if [ ! -f /root/.mini-dinstall.conf ]; then
    cat >/root/.mini-dinstall.conf <<EOF
[DEFAULT]
archivedir = /usr/src/debian-repository
archive_style = flat

verify_sigs = 0

generate_release = 1
release_signscript = /usr/src/fusionforge/vm/scripts/mini-dinstall-sign.sh

max_retry_time = 3600
mail_on_success = false

[local]
EOF
fi

export GNUPGHOME=/usr/src/gnupg
if [ ! -e $GNUPGHOME ]; then
    mkdir -m 700 $GNUPGHOME
    # Quick 'n Dirty hack to get entropy on VMs
    # https://bugs.launchpad.net/ubuntu/+source/gnupg/+bug/706011
    # (don't do this in prod!)
    aptitude install -y rng-tools
    echo HRNGDEVICE=/dev/urandom >> /etc/default/rng-tools
    service rng-tools restart
    gpg --batch --gen-key <<EOF
      Key-Type: RSA
      Key-Length: 2048
      Subkey-Type: RSA
      Subkey-Length: 2048
      Name-Real: FusionForge
      Expire-Date: 0
      %commit
EOF
fi
gpg --export FusionForge -a > /usr/src/debian-repository/key.asc
apt-key add /usr/src/debian-repository/key.asc

mini-dinstall -b


if [ ! -f /root/.dput.cf ]; then
    cat > /root/.dput.cf <<EOF
[local]
fqdn = localhost
incoming = /usr/src/debian-repository/mini-dinstall/incoming 
method = local
run_dinstall = 0
allow_unsigned_uploads = yes
post_upload_command = mini-dinstall -b
allowed_distributions = local
EOF
fi

if [ ! -f /root/.devscripts ]; then
    cat > /root/.devscripts <<EOF
DEBRELEASE_UPLOADER=dput
DEBUILD_DPKG_BUILDPACKAGE_OPTS=-i
EOF
fi


cd /usr/src/fusionforge/src
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

debian/rules debian/control  # re-gen debian/control from packaging/*
dch --newversion $(dpkg-parsechangelog | sed -n 's/^Version: \([0-9.]\+\(\~rc[0-9]\)\?\).*/\1/p')+$(date +%Y%m%d%H%M)-1 --distribution local --force-distribution "Autobuilt."
debuild --no-lintian --no-tgz-check -us -uc -tc  # using -tc so 'bzr st' is readable

debrelease -f local
mv $f debian/changelog

echo 'deb file:///usr/src/debian-repository local/' > /etc/apt/sources.list.d/local.list
apt-get update
