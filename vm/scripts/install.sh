#! /bin/sh


# Authors :
#  Roland Mas
#  Olivier BERGER <olivier.berger@it-sudparis.eu>
#  Sylvain Beucler

# This script will install the Debian packages to be tested which have been build inside the VM

# Prerequisite : running 'build.sh' and its prerequisites


#set -x
grep -q debian-repository /etc/apt/sources.list /etc/apt/sources.list.d/*
if [ $? -ne 0 ]; then
    echo "You probably need to add the following in /etc/apt/sources.list :"
    echo "echo 'deb file:///usr/src/debian-repository local/' >> /etc/apt/sources.list"
    exit 1
fi

set -e

# "Backport" recent dependency
if ! dpkg -l loggerhead | grep -q ^ii ; then
    wget -c http://snapshot.debian.org/archive/debian/20121107T152130Z/pool/main/l/loggerhead/loggerhead_1.19%7Ebzr477-1_all.deb
    aptitude install gdebi-core
    gdebi --non-interactive loggerhead_1.19~bzr477-1_all.deb
fi

aptitude update
if dpkg -l fusionforge-full | grep -q ^ii ; then
    # Already installed, upgrading
    /usr/src/fusionforge/tests/func/db_reload.sh
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive aptitude -y dist-upgrade
else
    # Initial installation
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive aptitude -y install gforge-db-postgresql
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive aptitude -y install fusionforge-full
    /usr/share/gforge/bin/forge_set_password admin myadmin
    a2dissite default
    invoke-rc.d apache2 restart
    su - postgres -c "pg_dumpall" > /root/dump
    echo "If you saw a message like 'Could not connect to database' above, don't worry, it's probably harmless."
fi
