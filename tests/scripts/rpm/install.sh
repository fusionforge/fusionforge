#!/bin/bash
# Install FusionForge and its dependencies
#
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

# postgresql-server
# php-cli php-pgsql
# httpd php
# nscd  # no unscd T-T

# get_news_notapproved.pl:
# perl perl-DBI perl-Text-Autoformat perl-Mail-Sendmail

# php-htmlpurifier-htmlpurifier  # fedora
# htmlpurifier  # pear
#   pear channel-discover htmlpurifier.org
#   pear install hp/HTMLPurifier
#   Note: htmlpurifier required in -common: group->forum->textsanitizer->htmlpurifier
# arc           # vendor/
# graphite      # vendor/
# php-pear-CAS  # epel
# php-simplepie # epel or common/rss/simplepie.inc

# postfix: needs to be recompiled, el6 doesn't have pgsql support enabled (conditional in .spec)

# mediawiki (provided by mediawiki119): EPEL

# Fedora/RHEL/CentOS version:
os_version=$(rpm -q --qf "%{VERSION}" $(rpm -q --whatprovides redhat-release))

if ! rpm -q fedora-release >/dev/null; then
    # EPEL - http://download.fedoraproject.org/pub/epel/6/i386/repoview/epel-release.html
    if ! rpm -q epel-release >/dev/null; then
	rpm -ivh http://fr2.rpmfind.net/linux/epel/6/i386/epel-release-6-8.noarch.rpm
    fi

    # Prepare manual backports
    cat <<'EOF' > /etc/yum.repos.d/fedora-source.repo
[fedora]
name=Fedora 20
failovermethod=priority
metalink=https://mirrors.fedoraproject.org/metalink?repo=fedora-20&arch=$basearch
enabled=0
gpgcheck=0
[fedora-source]
name=Fedora 20 - Source
failovermethod=priority
metalink=https://mirrors.fedoraproject.org/metalink?repo=fedora-source-20&arch=$basearch
enabled=0
gpgcheck=0
EOF
    yum install -y yum-utils  # yumdownloader
fi

if yum list libnss-pgsql >/dev/null 2>&1; then
    yum install -y libnss-pgsql
else
    # libnss-pgsql: id., plus http://yum.postgresql.org/8.4/redhat/rhel-5-x86_64/
    yumdownloader --enablerepo=fedora --source libnss-pgsql
    DEPS="gcc postgresql-devel xmlto"
    yum install -y $DEPS
    rpmbuild --rebuild libnss-pgsql-*.src.rpm
    yum remove -y $DEPS
    rpm -ivh ~/rpmbuild/RPMS/x86_64/libnss-pgsql-*.x86_64.rpm
fi

if yum list moin >/dev/null 2>&1; then
    yum install -y moin
else
    # moin: no available package for RHEL; though 'moin' is available in Fedora
    yumdownloader --enablerepo=fedora --source moin
    DEPS="python-devel"
    yum install -y $DEPS
    rpmbuild --rebuild moin-*.src.rpm
    yum remove -y $DEPS
    rpm -ivh ~/rpmbuild/RPMS/noarch/moin-*.noarch.rpm
fi

if yum list php-htmlpurifier-htmlpurifier >/dev/null 2>&1; then
    yum install -y php-htmlpurifier-htmlpurifier
else
    # moin: no available package for RHEL; though 'moin' is available in Fedora
    yumdownloader --enablerepo=fedora --source php-htmlpurifier-htmlpurifier
    DEPS="php-channel-htmlpurifier"  # for v4.3.0-6.fc20
    yum install -y $DEPS
    rpmbuild --rebuild php-htmlpurifier-htmlpurifier-*.src.rpm
    yum remove -y $DEPS
    yum install -y ~/rpmbuild/RPMS/noarch/php-htmlpurifier-htmlpurifier-*.noarch.rpm
fi

# TODO: postfix: rebuild from RHEL/CentOS sources with pgsql enabled,
# so we can test SSH

if rpm -q fusionforge >/dev/null ; then
    yum upgrade -y
else
    # Initial installation
    yum install -y fusionforge fusionforge-shell \
	fusionforge-plugin-scmgit fusionforge-plugin-scmsvn \
	fusionforge-plugin-mediawiki \
	fusionforge-plugin-blocks fusionforge-plugin-online_help
fi

# Dump clean DB
if [ ! -e /root/dump ]; then $(dirname $0)/../../func/db_reload.sh --backup; fi