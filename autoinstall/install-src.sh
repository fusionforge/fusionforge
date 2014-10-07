#!/bin/bash
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
. $(dirname $0)/common-backports

# Install FusionForge dependencies
if [ -e /etc/debian_version ]; then
    export DEBIAN_FRONTEND=noninteractive
    backports_deb
    apt-get update
    apt-get install -y make gettext php5-cli php5-pgsql php-htmlpurifier \
	apache2 locales-all postgresql libnss-pgsql2 unscd \
	subversion augeas-tools viewvc git \
	mediawiki \
	php-twig \
	python-moinmoin libapache2-mod-wsgi python-psycopg2
else
    backports_rpm
    yum install -y make gettext php-cli php-pgsql \
	httpd mod_ssl postgresql-server nscd \
	subversion augeas viewvc git \
	mediawiki119 \
	moin mod_wsgi python-psycopg2
fi

cd /usr/src/fusionforge/src/
make
make install-base install-shell \
    install-plugin-scmsvn install-plugin-scmgit \
    install-plugin-blocks install-plugin-mediawiki install-plugin-moinmoin \
    install-plugin-online_help
# adapt .ini configuration in /etc/fusionforge/config.ini.d/
make post-install-base post-install-shell \
    post-install-plugin-scmsvn post-install-plugin-scmgit \
    post-install-plugin-blocks post-install-plugin-mediawiki post-install-plugin-moinmoin \
    post-install-plugin-online_help

# Dump clean DB
if [ ! -e /root/dump ]; then $(dirname $0)/../tests/func/db_reload.sh --backup; fi
