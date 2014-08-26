#! /bin/sh
# Install FusionForge packages from build.sh + dependencies

# Authors :
#  Roland Mas
#  Olivier BERGER <olivier.berger@it-sudparis.eu>
#  Sylvain Beucler

#set -x
set -e
export DEBIAN_FRONTEND=noninteractive

# fusionforge-plugin-scmbzr depends on loggerhead (>= 1.19~bzr477~),
# but wheezy only has 1.19~bzr461-1, so we need to manually "Backport"
# a more recent dependency
if ! dpkg-query -s loggerhead >/dev/null 2>&1 ; then
    # install loggerhead with its dependencies
    # we need gdebi to make sure dependencies are installed too (simple dpkg -i won't)
    apt-get -y install gdebi-core wget
    wget -c http://snapshot.debian.org/archive/debian/20121107T152130Z/pool/main/l/loggerhead/loggerhead_1.19%7Ebzr477-1_all.deb
    gdebi --non-interactive loggerhead_1.19~bzr477-1_all.deb
fi

# Install locales-all which is a Recommends and not a Depends
if ! dpkg -l locales-all | grep -q ^ii ; then
    apt-get -y install locales-all
fi

# Install FusionForge packages
apt-get update
if dpkg-query -s fusionforge >/dev/null 2>&1; then
    # Already installed, upgrading
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive apt-get -y dist-upgrade
else
    # Initial installation
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive apt-get -y install fusionforge

    # Initial configuration
    forge_set_password admin myadmin

    # Backup the DB, so that it can be restored for the test suite
    su - postgres -c "pg_dumpall" > /root/dump
    service postgresql stop
    pgdir=/var/lib/postgresql
    if [ -e /etc/redhat-release ]; then pgdir=/var/lib/pgsql; fi
    if [ -d $pgdir.backup ]; then
        rm -fr $pgdir.backup
    fi
    cp -a --reflink=auto $pgdir $pgdir.backup
    service postgresql start
fi
