#!/bin/bash
# Install FusionForge from source
#
# Copyright (C) 2011  Roland Mas
# Copyright (C) 2011  Olivier Berger - Institut Telecom
# Copyright (C) 2014  Inria (Sylvain Beucler)
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

#set -x
set -e
. $(dirname $0)/common-backports

# Install FusionForge dependencies
if [ -e /etc/debian_version ]; then
	export DEBIAN_FRONTEND=noninteractive
	backports_deb
	apt-get update
	if grep -q ^8 /etc/debian_version; then
		apt-get install -y make gettext php5-cli php5-pgsql php-htmlpurifier php-http php-text-captcha \
			libapache2-mpm-itk libapache2-mod-svn \
			libapache2-mod-php5 \
			apache2 postgresql postgresql-contrib libnss-pgsql2 unscd \
			cvs subversion viewvc python-pycurl git mercurial xinetd \
			python-moinmoin libapache2-mod-wsgi python-psycopg2 \
			unoconv poppler-utils dpkg-dev \
			libmarkdown-php \
			vsftpd \
			fonts-dejavu-core
		apt-get -y install mediawiki -t jessie-backports
	else
		apt-get install -y make gettext php-cli php-pgsql php-htmlpurifier php-http php-text-captcha \
			libapache2-mpm-itk libapache2-mod-svn \
			libapache2-mod-php \
			apache2 postgresql postgresql-contrib libnss-pgsql2 unscd \
			cvs subversion viewvc python-pycurl git mercurial xinetd \
			python-moinmoin libapache2-mod-wsgi python-psycopg2 \
			unoconv poppler-utils dpkg-dev \
			libmarkdown-php \
			vsftpd \
			fonts-dejavu-core mediawiki
	fi
	if ! dpkg-vendor --is Ubuntu; then
		apt-get install locales-all  # https://bugs.launchpad.net/ubuntu/+source/glibc/+bug/1394929
	fi
elif [ -e /etc/SuSE-release ]; then
	suse_check_release
	suse_install_repos
	suse_install_rpms make gettext-runtime php5 php5-gettext php5-posix php5-pgsql \
		apache2 apache2-mod_php5 apache2-mod_wsgi apache2-mod_mpm_itk \
		postgresql-server subversion \
		php5-pear php5-pear-htmlpurifier \
		mailman postfix  \
		openssh  \
		cvs rcs perl-IPC-Run perl-URI \
		subversion-server  \
		git git-web \
		mercurial \
		python-psycopg2 \
		mediawiki moinmoin-wiki \
		vsftpd
else
	yum install -y make tar
	backports_rpm
	yum --enablerepo=epel install -y httpd-itk
	yum install -y gettext php-cli php-pgsql php-process php-mbstring php-pear-HTTP php-pear-Text-CAPTCHA \
		httpd mod_dav_svn mod_ssl postgresql-server postgresql-contrib nscd \
		cvs subversion viewvc python-pycurl git gitweb mercurial xinetd \
		moin mod_wsgi python-psycopg2 \
		unoconv poppler-utils libreoffice-headless \
		mediawiki ImageMagick php-markdown \
		vsftpd \
		dejavu-fonts-common
fi

(
	cd $(dirname $0)/../src/
	make
	make install-base install-shell install-scm \
		install-plugin-scmcvs install-plugin-scmsvn install-plugin-scmgit install-plugin-scmhg \
		install-plugin-blocks install-plugin-moinmoin \
		install-plugin-taskboard install-plugin-message \
		install-plugin-repositoryapi \
		install-plugin-mediawiki
	if [ -e /etc/centos-release -o -e /etc/debian-release ]; then
		make install-plugin-phptextcaptcha
	fi
	make post-install
)
