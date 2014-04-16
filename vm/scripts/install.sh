#! /bin/sh


# Authors :
#  Roland Mas
#  Olivier BERGER <olivier.berger@it-sudparis.eu>
#  Sylvain Beucler

# This script will install the Debian packages to be tested which have been build inside the VM

# Prerequisite : running 'build.sh' and its prerequisites


#set -x
set -e

variant=${1:-full}

# "Backport" recent dependency
if ! dpkg -l loggerhead | grep -q ^ii ; then
    wget -c http://snapshot.debian.org/archive/debian/20121107T152130Z/pool/main/l/loggerhead/loggerhead_1.19%7Ebzr477-1_all.deb
    aptitude install gdebi-core
    gdebi --non-interactive loggerhead_1.19~bzr477-1_all.deb
fi

aptitude update
if dpkg -l fusionforge-$variant | grep -q ^ii ; then
    # Already installed, upgrading
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive aptitude -y dist-upgrade
else
    # Initial installation
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive aptitude -y install fusionforge-$variant
    /usr/share/gforge/bin/forge_set_password admin myadmin
    a2dissite default
    invoke-rc.d apache2 restart
    su - postgres -c "pg_dumpall" > /root/dump
fi
