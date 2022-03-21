#!/bin/bash
# Install FusionForge from source
#
# Copyright (C) 2011, 2019, Roland Mas
# Copyright (C) 2011, Olivier Berger - Institut Telecom
# Copyright (C) 2014, Inria (Sylvain Beucler)
# Copyright 2017,2019,2021-2022 Franck Villaume - TrivialDev
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
	apt-get update || true
	if grep -q ^9 /etc/debian_version; then
		apt-get install -y make gettext php-cli php-pgsql php-htmlpurifier php-http php-text-captcha php-soap \
			libapache2-mpm-itk libapache2-mod-svn \
			libapache2-mod-php \
			apache2 postgresql postgresql-contrib libnss-pgsql2 unscd \
			cvs subversion viewvc python-pycurl libcgi-pm-perl git mercurial bzr xinetd mksh \
			python-moinmoin libapache2-mod-wsgi python-psycopg2 \
			unoconv poppler-utils dpkg-dev \
			libmarkdown-php \
			vsftpd \
			fonts-dejavu-core mediawiki
	elif grep -q ^10 /etc/debian_version; then
		apt-get install -y make gettext php-cli php-pgsql php-htmlpurifier php-http php-soap php-pear ca-certificates \
			libapache2-mpm-itk libapache2-mod-svn \
			libapache2-mod-php \
			apache2 postgresql postgresql-contrib libnss-pgsql2 unscd \
			cvs subversion viewvc python-pycurl libcgi-pm-perl git mercurial bzr xinetd mksh \
			python-moinmoin libapache2-mod-wsgi python-psycopg2 \
			unoconv poppler-utils dpkg-dev \
			libmarkdown-php \
			vsftpd \
			fonts-dejavu-core mediawiki
		pushd $(mktemp -d)
		apt-get install -y wget php-pear
		ptpver=1.2.1-2
		wget http://ftp.fr.debian.org/debian/pool/main/p/php-text-password/php-text-password_${ptpver}_all.deb
		dpkg -i php-text-password_${ptpver}_all.deb
		rm -f php-text-password_${ptpver}_all.deb
		ptcver=1.0.2-4
		wget http://ftp.fr.debian.org/debian/pool/main/p/php-text-captcha/php-text-captcha_${ptcver}_all.deb
		dpkg -i php-text-captcha_${ptcver}_all.deb
		rm -f php-text-captcha_${ptcver}_all.deb
		popd
	else
		apt-get install -y make gettext php-cli php-pgsql php-htmlpurifier php-http php-soap php-pear php-text-captcha ca-certificates \
			libapache2-mpm-itk libapache2-mod-svn \
			libapache2-mod-php \
			apache2 postgresql postgresql-contrib libnss-pgsql2 unscd \
			cvs subversion libcgi-pm-perl git mercurial bzr xinetd mksh \
			unoconv poppler-utils dpkg-dev \
			libmarkdown-php \
			vsftpd \
			fonts-dejavu-core mediawiki
	fi
	if ! pear list Text_CAPTCHA ; then
		pear install Text_CAPTCHA
	fi
	if ! dpkg-vendor --is Ubuntu; then
		apt-get install locales-all  # https://bugs.launchpad.net/ubuntu/+source/glibc/+bug/1394929
	fi
elif [[ ! -z `cat /etc/os-release | grep 'SUSE'` ]]; then
	suse_check_release
	suse_install_repos
	suse_install_rpms make gettext-runtime php7 php7-gettext php7-posix php7-pgsql php7-soap \
		apache2 apache2-mod_php7 apache2-mod_wsgi apache2-mod_mpm_itk \
		postgresql-server postgresql-contrib subversion \
		php7-pear php-pear-HTMLPurifier php7-curl \
		mailman postfix \
		openssh \
		cvs rcs perl-IPC-Run perl-URI \
		subversion-server \
		git git-web php7-pcntl \
		mercurial \
		python-psycopg2 \
		mediawiki moinmoin-wiki \
		vsftpd xinetd insserv-compat
	suse_backport_from_fedora_rpm
else
	yum install -y make tar
	backports_rpm
	# Fedora/RHEL/CentOS version:
	os_version=$(rpm -q --qf "%{VERSION}" $(rpm -q --whatprovides redhat-release))
	case $os_version in
		7)
		yum install -y gettext php-cli php-pgsql php-process php-mbstring php-pear-HTTP php-pear-Text-CAPTCHA php-soap \
		httpd mod_dav_svn mod_ssl postgresql-server postgresql-contrib nscd \
		cvs subversion viewvc python-pycurl git gitweb mercurial xinetd \
		moin mod_wsgi python-psycopg2 \
		unoconv poppler-utils libreoffice-headless \
		ImageMagick php-markdown \
		vsftpd \
		dejavu-fonts-common
		;;
		8*)
		yum install -y gettext php php-cli php-pgsql php-process php-mbstring php-soap \
			httpd mod_dav_svn mod_ssl postgresql-server postgresql-contrib nscd \
			cvs subversion git gitweb mercurial xinetd \
			unoconv poppler-utils libreoffice-headless \
			ImageMagick \
			vsftpd \
			dejavu-fonts-common
		#open port 80 & 443 if firewalld is running otherwise no need to adjust
		firewall-cmd --zone=public --permanent --add-service=http || true
		firewall-cmd --zone=public --permanent --add-service=https || true
		firewall-cmd --reload || true
		#enable prefork (we use itk & php7_module
		echo 'LoadModule mpm_prefork_module modules/mod_mpm_prefork.so' > /etc/httpd/conf.modules.d/00-mpm.conf
		echo 'LoadModule mpm_itk_module modules/mod_mpm_itk.so' > /etc/httpd/conf.modules.d/01-mpm.conf
		;;
	esac
fi

(
	cd $(dirname $0)/../src/
	make
	make install-base install-shell install-scm \
		install-plugin-scmcvs install-plugin-scmsvn install-plugin-scmgit install-plugin-scmhg \
		install-plugin-blocks \
		install-plugin-taskboard install-plugin-message \
		install-plugin-repositoryapi
	if [ -e /etc/centos-release ]; then
		make install-plugin-scmbzr
	fi
	if [ -e /etc/centos-release ] && ! grep -q "[[:space:]]8" /etc/centos-release; then
		make install-plugin-mediawiki
		make install-plugin-moinmoin
	fi
	if [ -e /etc/debian_version ] && ! grep -q ^11 /etc/debian_version; then
		make install-plugin-moinmoin
	fi
	if [ -e /etc/debian_version ]; then
		make install-plugin-mediawiki
	fi
	if [ -e /etc/centos-release -o -e /etc/debian_version ]; then
		make install-plugin-phptextcaptcha
	fi
	if [[ ! -z `cat /etc/os-release | grep 'SUSE'` ]]; then
		make install-plugin-moinmoin
		make install-plugin-mediawiki
	fi
	make post-install
)
