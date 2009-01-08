#---------------------------------------------------------------------------
# This file is part of the NovaForge project
# Novaforge is a registered trade mark from Bull S.A.S
# Copyright (C) 2007 Bull S.A.S.
# http://novaforge.org
#
# This file is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This file is distributed in the hope that it will be useful,
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
%define debug_package %{nil}
%define real_name libapache2-mod-auth-gforge

# Constants related to other RPMs we provide
%define getdist_version 1.2
%define subversion_version 1.3.2

# Constants related to the distribution
%if %{dist} == "rhel3"
%define file_version 3.39
%define glibc_version 2.3.2
%define httpd_version 2.0.46
%define php_version 4.3.2
%define postgresql_name rh-postgresql
%define postgresql_version 7.3.4
%endif
%if %{dist} == "rhel4"
%define file_version 4.10
%define glibc_version 2.3.4
%define httpd_version 2.0.52
%define php_version 4.3.9
%define postgresql_name postgresql
%define postgresql_version 7.4.6
%endif
%if %{dist} == "rhel5"
%define file_version 4.17
%define glibc_version 2.5
%define httpd_version 2.2.3
%define php_version 5.1.6
%define postgresql_name postgresql
%define postgresql_version 8.1.11
%endif
%if %{dist} == "aurora2"
%define file_version 4.10
%define glibc_version 2.3.3
%define httpd_version 2.0.52
%define php_version 4.3.10
%define postgresql_name postgresql
%define postgresql_version 7.4.6
%endif
%if %{unsupported_dist} == 1
%define file_version 999
%define glibc_version 999
%define httpd_version 999
%define php_version 999
%define postgresql_name postgresql
%define postgresql_version 999
%endif

# Sources and patches
Source0:	http://www.gforge.org/frs/download.php/207/%{real_name}-%{version}.tar.gz
Source1:	%{name}.conf
Patch0:		%{name}-%{version}-apr1x.diff
Patch1:		%{name}-%{version}-anonymous.diff

# Packages required for build
BuildRequires:	file >= %{file_version}
BuildRequires:	gcc >= %{gcc_version}
BuildRequires:	getdist >= %{getdist_version}
BuildRequires:	glibc-devel >= %{glibc_version}
BuildRequires:	httpd-devel >= %{httpd_version}
BuildRequires:	php >= %{php_version}
BuildRequires:	%{postgresql_name}-devel >= %{postgresql_version}
BuildRequires:	subversion-devel >= %{subversion_version}

# Build architecture

# Build root
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-buildroot

#
# Main package
#

Summary:	Apache module for GForge PostgreSQL auth
Name:		mod_auth_gforge
Version:	0.5.9.3
Release:	5.3.%{dist}
License:	Apache License
Group:		System/Servers
URL:		http://www.gforge.org/
Requires:	getdist >= %{getdist_version}
Requires:	glibc >= %{glibc_version}
Requires:	httpd >= %{httpd_version}
Requires:	%{postgresql_name}-libs >= %{postgresql_version}

%description
An Apache module for authenticating and authorizing users against
information stored in the GForge PostgreSQL database.

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
%setup -q -n %{real_name}-%{version}
%patch0 -p0
%patch1 -p1
# Strip away annoying ^M
find . -type f|xargs file|grep 'CRLF'|cut -d: -f1|xargs perl -p -i -e 's/\r//'
find . -type f|xargs file|grep 'text'|cut -d: -f1|xargs perl -p -i -e 's/\r//'

%build
%{_sbindir}/apxs -c \
	%{?_with_debug: -DMOD_AUTH_GFORGE_DEBUG} \
	-I %{_includedir}/subversion-1 \
	-l pq \
	src/mod_auth_gforge.c src/apacheconfig.c src/database.c src/utils.c

%install
[ "%{buildroot}" != "/" ] && rm -rf %{buildroot}
%{__install} -d %{buildroot}%{_libdir}/httpd/modules
%{_sbindir}/apxs -i \
	-S LIBEXECDIR=%{buildroot}%{_libdir}/httpd/modules \
	-n auth_gforge \
	src/.libs/mod_auth_gforge.so

%clean
[ "%{buildroot}" != "/" ] && %{__rm} -rf %{buildroot}
%{__rm} -rf %{_builddir}/%{real_name}-%{version}

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

%files
%defattr(-,root,root)
%doc AUTHORS COPYING ChangeLog NEWS README TESTS TODO
%attr(0755,root,root) %{_libdir}/httpd/modules/mod_auth_gforge.so

%changelog
* Fri Oct 31 2008 Gregory Cuellar <gregory.cuellar@bull.net> 0.5.9.3-5.3
- Ajout rhel5

* Wed Nov 14 2007 Gilles Menigot <gilles.menigot@bull.net> 0.5.9.3-5.2
- Add GPL v2 license
- Requires getdist >= 1.2

* Fri Jun 08 2007 Gilles Menigot <gilles.menigot@bull.net> 0.5.9.3-5.1
- Moved to SVN repository

* Fri Mar 09 2007 Gilles Menigot <gilles.menigot@bull.net> 0.5.9.3-5
- Spec file modifications for Aurora SPARC Linux 2.0 support
- Add GCC build requirement

* Fri Feb 23 2007 Gilles Menigot <gilles.menigot@bull.net> 0.5.9.3-4
- Spec file modifications for RHEL 4 support

* Mon Nov 06 2006 Gilles Menigot <gilles.menigot@bull.net> 0.5.9.3-3
- Build and install with APXS

* Mon Oct 30 2006 Gilles Menigot <gilles.menigot@bull.net> 0.5.9.3-2
- Patch to correct anonymous access
- Add debug option through rpmbuild argument "--with debug" to have verbose logs

* Mon Oct 09 2006 Gilles Menigot <gilles.menigot@bull.net> 0.5.9.3-1
- Initial release for RHEL 3

* Mon May 08 2006 Oden Eriksson <oeriksson@mandriva.com> 0.5.9.3-3mdk
- fix deprecated apr calls (Lutz Güttler)
