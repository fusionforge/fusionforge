#! /bin/sh

set -e
set -x

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
fi
