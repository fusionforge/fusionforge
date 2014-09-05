#! /bin/sh
# Install FusionForge from source
#
# Copyright (C) 2011  Roland Mas
# Copyright (C) 2011  Olivier Berger - Institut Telecom
# Copyright (C) 2014  Inria (Sylvain Beucler)
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

#set -x
set -e
export DEBIAN_FRONTEND=noninteractive

# fusionforge-plugin-scmbzr depends on loggerhead (>= 1.19~bzr477~),
# but wheezy only has 1.19~bzr461-1, so we need to manually "Backport"
# a more recent dependency
if grep -q ^7 /etc/debian_version && ! dpkg-query -s loggerhead >/dev/null 2>&1 ; then
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
apt-get install -y make gettext php5-cli php5-pgsql php-htmlpurifier \
    apache2 postgresql \
    subversion viewvc \
    mediawiki \
    python-moinmoin libapache2-mod-wsgi python-psycopg2

cd /usr/src/fusionforge/src/
make
make install-base install-shell
make install-plugin-scmsvn install-plugin-blocks \
    install-plugin-mediawiki install-plugin-moinmoin \
    install-plugin-online_help
# adapt .ini configuration in /etc/fusionforge/config.ini.d/
make post-install-base post-install-plugin-scmsvn post-install-plugin-blocks \
    post-install-plugin-mediawiki post-install-plugin-moinmoin \
    post-install-plugin-online_help

# Dump clean DB
if [ ! -e /root/dump ]; then $(dirname $0)/../../func/db_reload.sh --backup; fi
