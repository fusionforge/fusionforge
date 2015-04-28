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
    apt-get install -y make gettext php5-cli php5-pgsql php-htmlpurifier php-http \
	libapache2-mpm-itk libapache2-mod-svn \
	apache2 postgresql libnss-pgsql2 unscd \
	subversion viewvc python-pycurl git xinetd \
	mediawiki \
	python-moinmoin libapache2-mod-wsgi python-psycopg2 \
	unoconv poppler-utils
    if ! dpkg-vendor --is Ubuntu; then
	apt-get install locales-all  # https://bugs.launchpad.net/ubuntu/+source/glibc/+bug/1394929
    fi
else
    yum install -y make tar
    backports_rpm
    yum --enablerepo=epel install -y httpd-itk
    yum install -y gettext php-cli php-pgsql php-process php-mbstring php-pear-HTTP \
	httpd mod_dav_svn mod_ssl postgresql-server nscd \
	subversion viewvc python-pycurl git gitweb xinetd \
	mediawiki119 \
	moin mod_wsgi python-psycopg2 \
	unoconv poppler-utils
fi

(
    cd $(dirname $0)/../src/
    make
    make install-base install-shell install-scm \
        install-plugin-scmsvn install-plugin-scmgit \
        install-plugin-blocks install-plugin-mediawiki install-plugin-moinmoin \
        install-plugin-online_help install-plugin-taskboard
    make post-install
)

# Dump clean DB
if [ ! -e /root/dump ]; then $(dirname $0)/../tests/func/db_reload.sh --backup; fi
