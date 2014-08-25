#! /bin/sh


# Authors :
#  Roland Mas
#  Olivier BERGER <olivier.berger@it-sudparis.eu>
#  Sylvain Beucler

# This script will install the Debian packages to be tested which have been build inside the VM

# Prerequisite : running 'build.sh' and its prerequisites


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
if dpkg -l fusionforge | grep -q ^ii ; then
    # Already installed, upgrading
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive apt-get -y dist-upgrade
else
    # Initial installation
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive apt-get -y install fusionforge

    # Initial configuration
    forge_set_password admin myadmin
    a2dissite default
    invoke-rc.d apache2 restart

    # Backup the DB, so that it can be restored for the test suite to run
    su - postgres -c "pg_dumpall" > /root/dump
    invoke-rc.d postgresql stop
    if [ -d /var/lib/postgresql.backup ]; then
        rm -fr /var/lib/postgresql.backup
    fi
    cp -a --reflink=auto /var/lib/postgresql /var/lib/postgresql.backup
    invoke-rc.d postgresql start
fi
