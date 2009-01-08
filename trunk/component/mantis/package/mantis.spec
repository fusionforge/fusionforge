#---------------------------------------------------------------------------
# Novaforge is a registered trade mark from Bull S.A.S
# Copyright (C) 2007 Bull S.A.S.
# 
# http://novaforge.org/
#
#
# This file has been developped within the Novaforge(TM) project from Bull S.A.S
# and contributed back to GForge community.
#
# GForge is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# GForge is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this file; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#---------------------------------------------------------------------------

# Identify the distribution
%define dist %(test -x %{_bindir}/getdist && %{_bindir}/getdist || echo unknown)
%define unsupported_dist 1
%if %{dist} == "rhel3"
%define unsupported_dist 0
%endif
%if %{dist} == "rhel4"
%define unsupported_dist 0
%endif
%if %{dist} == "rhel5"
%define unsupported_dist 0
%endif
%if %{dist} == "aurora2"
%define unsupported_dist 0
%endif

# Constants related to this RPM
%define friendly_name Mantis
%define mantis_custom_level 1
%define mantis_custom_version %{version}.%{mantis_custom_level}

# Constants related to other RPMs we provide
%define getdist_version 1.3
%define pwgen_version 2.05

# Constants related to the distribution
%define apache_group apache
%if %{dist} == "rhel3"
%define httpd_version 2.0.46
%define mysql_version 3.23.58 
%define php_version 4.3.2
%endif
%if %{dist} == "rhel4"
%define httpd_version 2.0.52
%define mysql_version 4.1.7
%define php_version 4.3.9
%endif
%if %{dist} == "rhel5"
%define httpd_version 2.2.3
%define mysql_version 5.0.45
%define php_version 5.1.6
%endif
%if %{dist} == "aurora2"
%define httpd_version 2.0.52
%define mysql_version 4.1.12
%define php_version 4.3.10
%endif
%if %{unsupported_dist} == 1
%define httpd_version 999
%define mysql_version 999
%define php_version 999
%endif

# Sources and patches
Source0:	mantis-%{version}.tar.gz
Source1:	mantis-custom-%{mantis_custom_version}.tar.gz

Patch0:		mantis-%{version}-offline.diff
Patch1:		mantis-%{version}-ldap.diff
Patch2:		mantis-%{version}-username.diff
Patch3:		mantis-%{version}-task.diff
Patch4:		mantis-%{version}-checkin.diff

# Packages required for build
BuildRequires:	getdist >= %{getdist_version}

# Build architecture
BuildArch:	noarch

# Build root
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-buildroot

#
# Main package
#
Summary:	Mantis web-based bugtracking system
Name:		mantis
Version:	1.1.4
Release:	%{mantis_custom_level}.1.%{dist}
License:	GPL
Group:		Applications/Internet
URL:		http://www.mantisbt.org
Requires:	getdist >= %{getdist_version}
Requires:	httpd >= %{httpd_version}
Requires:	mysql >= %{mysql_version}
Requires:	mysql-server >= %{mysql_version}
Requires:	php-mysql >= %{php_version}
Requires:	pwgen >= %{pwgen_version}

%description
Mantis is a free popular web-based bugtracking system
using PHP and MySQL.

%prep
if [ "%{unsupported_dist}" = "1" ] ; then
	cat <<ENDTEXT
ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR

The Linux distribution of this system is '%{dist}'.
This package can be built on the following distributions:
- Red Hat Enterprise Linux 3 or CentOS 3 (rhel3)
- Red Hat Enterprise Linux 4 or CentOS 4 (rhel4)
- Red Hat Enterprise Linux 5 or CentOS 5 (rhel5)
- Aurora SPARC Linux 2.0 (aurora2)

ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR
ENDTEXT
	exit 1
fi
%setup -q -a1
# Offline patch
%patch0 -p1
# LDAP patch
%patch1 -p1
# Username patch
%patch2 -p1
# Task patch
%patch3 -p1
# Checkin patch
%patch4 -p1

%build

%install
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && %{__rm} -rf %{buildroot}

# Install /etc/httpd/conf.d/
%{__install} -d %{buildroot}%{_sysconfdir}/httpd/conf.d
%{__install} mantis-custom-%{mantis_custom_version}/apacheconfig/mantis.conf %{buildroot}%{_sysconfdir}/httpd/conf.d/%{name}.conf
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-i %{buildroot}%{_sysconfdir}/httpd/conf.d/%{name}.conf

# Install /etc/mantis
%{__install} -d %{buildroot}%{_sysconfdir}/%{name}
touch %{buildroot}%{_sysconfdir}/%{name}/config_inc.php
touch %{buildroot}%{_sysconfdir}/%{name}/custom_constant_inc.php
%{__ln_s} %{_datadir}/%{name}/www/mantis_offline.php %{buildroot}%{_sysconfdir}/%{name}/mantis_offline.php

# Install /usr/sbin
%{__install} -d %{buildroot}%{_sbindir}
%{__install} mantis-custom-%{mantis_custom_version}/scripts/* %{buildroot}%{_sbindir}/
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-e "s|%VERSION%|%{version}|g" \
	-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-e "s|%INITRDDIR%|%{_initrddir}|g" \
	-e "s|%APACHE_GROUP%|%{apache_group}|g" \
	-e "s|%MANTIS_CUSTOM_LEVEL%|%{mantis_custom_level}|g" \
	-i %{buildroot}%{_sbindir}/*

# Install /usr/share/mantis/config
%{__install} -d %{buildroot}%{_datadir}/%{name}/config
%{__install} mantis-custom-%{mantis_custom_version}/config/* %{buildroot}%{_datadir}/%{name}/config/
%{__sed} \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-i %{buildroot}%{_datadir}/%{name}/config/config_inc.php
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-e "s|%INITRDDIR%|%{_initrddir}|g" \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-i %{buildroot}%{_datadir}/%{name}/config/functions

# Install /usr/share/mantis/www
%{__install} -d %{buildroot}%{_datadir}/%{name}/www
%{__cp} -a *.php core css graphs images javascript lang %{buildroot}%{_datadir}/%{name}/www/
%{__install} mantis_offline.php.sample %{buildroot}%{_datadir}/%{name}/www/mantis_offline.php
%{__ln_s} %{_sysconfdir}/%{name} %{buildroot}%{_datadir}/%{name}/www/conf

# Install /usr/share/mantis/www/gforge
%{__install} -d %{buildroot}%{_datadir}/%{name}/www/gforge
%{__install} mantis-custom-%{mantis_custom_version}/src/* %{buildroot}%{_datadir}/%{name}/www/gforge/

# Install /var/lib/mantis/keys
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/keys

%clean
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && %{__rm} -rf %{buildroot}
%{__rm} -rf %{_builddir}/%{name}-%{version}

%pre
if [ -x %{_bindir}/getdist ] ; then
	DIST=`%{_bindir}/getdist 2>/dev/null`
else
	DIST=unknown
fi
if [ "$DIST" != "%{dist}" ] ; then
	cat <<ENDTEXT
ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR

The Linux distribution of this system is '$DIST'.
This package has been built for Linux distribution '%{dist}' and will not function on this system.
Please install a package built specially for Linux distribution '$DIST'.

ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR
ENDTEXT
	exit 1
fi

%post
if [ -x %{_initrddir}/httpd ] ; then
	%{_initrddir}/httpd condrestart >> /dev/null 2>&1 || :
fi

%postun
if [ -x %{_initrddir}/httpd ] ; then
	%{_initrddir}/httpd condrestart >> /dev/null 2>&1 || :
fi

%files
%defattr(-,root,root)
%docdir doc
%{_sysconfdir}/httpd/conf.d/%{name}.conf
%dir %{_sysconfdir}/%{name}
%verify(not md5 size mtime) %attr(0640,root,%{apache_group}) %{_sysconfdir}/%{name}/config_inc.php
%verify(not md5 size mtime) %attr(0640,root,%{apache_group}) %config(noreplace) %{_sysconfdir}/%{name}/custom_constant_inc.php
%{_sysconfdir}/%{name}/mantis_offline.php
%attr(0755,root,root) %{_sbindir}/*
%{_datadir}/%{name}
%{_localstatedir}/lib/%{name}

%changelog
* Fri Nov 07 2008 Gregory Cuellar <gregory.cuellar@bull.net> 1.1.4-1.1
- Update to version 1.1.4
- Ajout RHEL 5

* Wed Jul 30 2008 Olivier Genty <olivier.genty@bull.net> 1.1.1-2.1
- Call user_set_password() instead of user_set_field() in functions.php

* Fri Jul 04 2008 Olivier Genty <olivier.genty@bull.net> 1.1.1-1.1
- Update to version 1.1.1

* Thu Mar 06 2008 Gilles Menigot <gilles.menigot@bull.net> 1.0.8-7.1
- Rename COMMIT command to CHECKIN_BUGS
- Replace "[LF]" pattern with \n in multiline attributes values
  received in XML

* Fri Feb 08 2008 Gilles Menigot <gilles.menigot@bull.net> 1.0.8-6.1
- Add COMMIT command to enable Subversion to Mantis link

* Tue Jan 22 2008 Gilles Menigot <gilles.menigot@bull.net> 1.0.8-5.1
- Modify login valid regex in configuration
- Modify post and postun scriptlets to avoid install and uninstall
  error
- Improve backup and restore scripts

* Tue Nov 20 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0.8-4.1
- Remove Options Indexes in Apache configuration

* Fri Nov 16 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0.8-3.1
- Correct mantis-add-public-key
- Improve authentication

* Wed Nov 14 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0.8-2.1
- Add GPL v2 license
- Requires getdist >= 1.2

* Fri Oct 12 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0.8-1.1
- Update to version 1.0.8
- Improve security with an SSL encrypted text exchange
- Remove mantis-standard package and mantis-config virtual package
- Add backup and restore scripts

* Mon Jun 18 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0.5-2.1
- Correct synchronization of GForge superuser
- Rename functions and variables of API
- Remove unused code

* Mon Jun 11 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0.5-1.1
- Initial release
