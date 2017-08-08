#!/bin/bash
#
# FusionForge startpoint script. Entrypoint for docker container
#
# Copyright 2017, Franck Villaume - TrivialDev
#
# This file is part of FusionForge. FusionForge is free software;
# you can redistribute it and/or modify it under the terms of the
# GNU General Public License as published by the Free Software
# Foundation; either version 2 of the Licence, or (at your option)
# any later version.
#
# FusionForge is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with FusionForge; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

__initdb() {
if [[ ! -d /var/lib/pgsql/backups ]]; then
    mkdir -p /var/lib/pgsql/backups
    chown postgres:postgres /var/lib/pgsql/backups
fi
if [[ ! -d /var/lib/pgsql/data ]]; then
    mkdir -p /var/lib/pgsql/data
    chown postgres:postgres /var/lib/pgsql/data
fi
if [[ ! -f /var/lib/pgsql/data/postgresql.conf ]]; then
    su postgres -c "/usr/bin/initdb -D /var/lib/pgsql/data"
fi

su postgres -c "/usr/bin/postgres -D /var/lib/pgsql/data -p 5432 &"
}

__install() {
cd /opt/sources/fusionforge/src/ && \
make && \
make install-base \
     install-shell \
     install-scm \
     install-ftp \
     install-plugin-scmcvs \
     install-plugin-scmsvn \
     install-plugin-scmgit \
     install-plugin-scmhg \
     install-plugin-blocks \
     install-plugin-moinmoin \
     install-plugin-taskboard \
     install-plugin-message \
     install-plugin-repositoryapi \
     install-plugin-mediawiki \
     install-plugin-compactpreview \
     install-plugin-headermenu \
     install-plugin-gravatar \
     install-plugin-scmhook \
     install-plugin-webanalytics
}
__postinstall() {
/usr/local/share/fusionforge/post-install.d/common/common.sh configure
/usr/local/share/fusionforge/post-install.d/web/web.sh rawconfigure
/usr/local/share/fusionforge/post-install.d/shell/shell.sh rawconfigure
/usr/local/share/fusionforge/post-install.d/ftp/ftp.sh configure
}

__zzzzlocalini() {
echo '[core]' > /etc/fusionforge/config.ini.d/zzzz-local.ini
echo 'is_docker = 1' >> /etc/fusionforge/config.ini.d/zzzz-local.ini
if [[ ! -z ${PORT_HTTP} ]]; then
   echo 'http_port = '${PORT_HTTP} >> /etc/fusionforge/config.ini.d/zzzz-local.ini
fi
if [[ ! -z ${PORT_HTTPS} ]]; then
   echo 'https_port = '${PORT_HTTPS} >> /etc/fusionforge/config.ini.d/zzzz-local.ini
fi
if [[ ! -z ${PORT_SSH} ]]; then
   echo 'ssh_port = '${PORT_SSH} >> /etc/fusionforge/config.ini.d/zzzz-local.ini
fi
}

__etchost() {
echo "127.0.0.1  scm."`hostname -f` >> /etc/hosts
}

__run_supervisor() {
supervisord -n
}

# Call all functions
__initdb
__install
__postinstall
__zzzzlocalini
__etchost
__run_supervisor
