#! /bin/sh


# Authors :
#  Roland Mas
#  Olivier BERGER <olivier.berger@it-sudparis.eu>

# This script will install the Debian packages to be tested which have been build inside the VM

# Prerequisite : running 'build.sh' and its prerequisites


#set -x
grep -q debian-repository /etc/apt/sources.list
if [ $? -ne 0 ]; then
    echo "You probably need to add the following in /etc/apt/sources.list :"
    echo "deb file:///root/debian-repository local/"
    exit 1
fi

set -e

aptitude update
if dpkg -l fusionforge-full | grep -q ^ii ; then
    # Already installed, upgrading
    /root/scripts/reload-db.sh
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive aptitude -y dist-upgrade
else
    # Initial installation
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive aptitude -y install postgresql-8.4
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive aptitude -y install gforge-db-postgresql
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive aptitude -y install fusionforge-full
    /usr/share/gforge/bin/forge_set_password admin myadmin
    a2dissite default
    invoke-rc.d apache2 restart
    su - postgres -c "pg_dump -Fc gforge" > /root/dump
    echo "If you saw a message like 'Could not connect to database' above, don't worry, it's probably harmless."
fi
