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

# Constants related to other RPMs we provide
%define getdist_version 1.2

# Constants related to the distribution
%if %{dist} == "rhel3"
%define gcc_version 3.2.3
%define glibc_version 2.3.2
%define postgresql_name rh-postgresql
%define postgresql_version 7.3.4
%define xmlto_version 0.0.14
%endif
%if %{dist} == "rhel4"
%define gcc_version 3.4.3
%define glibc_version 2.3.4
%define postgresql_name postgresql
%define postgresql_version 7.4.6
%define xmlto_version 0.0.18
%endif
%if %{dist} == "rhel5"
%define gcc_version 4.1.2
%define glibc_version 2.5
%define postgresql_name postgresql
%define postgresql_version 8.1.11
%define xmlto_version 0.0.18
%endif
%if %{dist} == "aurora2"
%define gcc_version 3.4.2
%define glibc_version 2.3.3
%define postgresql_name postgresql
%define postgresql_version 7.4.6
%define xmlto_version 0.0.18
%endif
%if %{unsupported_dist} == 1
%define gcc_version 999
%define glibc_version 999
%define postgresql_name postgresql
%define postgresql_version 999
%define xmlto_version 999
%endif

# Sources and patches
Source0:	%{name}_%{version}.tar.gz
Source1:	%{name}.conf
Patch0:		%{name}-redhat.diff

# Packages required for build
BuildRequires:	gcc-c++ >= %{gcc_version}
BuildRequires:  getdist >= %{getdist_version}
BuildRequires:  glibc-devel >= %{glibc_version}
BuildRequires:  %{postgresql_name}-devel >= %{postgresql_version}
BuildRequires:  xmlto >= %{xmlto_version}

# Build architecture

# Build root
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-buildroot

#
# Main package
#

Summary:	PostgreSQL module for NSS
Name:		libnss-pgsql
Version:	1.3.1
Release:	4.3.%{dist}
License:	GPL
Group:		System Environment/Libraries
URL:		http://www.pgfoundry.org/projects/sysauth/
Requires:	getdist >= %{getdist_version}
Requires:	glibc >= %{glibc_version}
Requires:	%{postgresql_name}-libs >= %{postgresql_version}

%description
%{name} is a name service switch module that allows the 
use of a PostgreSQL backend for passwd, group and shadow lookups.

%prep
if [ "%{unsupported_dist}" = "1" ] ; then
	cat <<ENDTEXT
ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR

The Linux distribution of this system is '%{dist}'.
This package can be built on the following distributions:
- Red Hat Enterprise Linux 3 or CentOS 3 (rhel3)
- Red Hat Enterprise Linux 4 or CentOS 4 (rhel4)
- Red Hat Enterprise Linux 5 or CentOS 5 (rhel5)
- Aurora SPARC linux 2.0 (aurora2)

ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR
ENDTEXT
	exit 1
fi
%setup -q
%patch0 -p1
./configure --prefix=/ --sysconfdir=%{_sysconfdir}

%build
make

%install
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && %{__rm} -rf %{buildroot}
%{__make} DESTDIR=%{buildroot} install
%{__rm} -rf %{buildroot}/doc
%{__rm} -f %{buildroot}/lib/*.a
%{__rm} -f %{buildroot}/lib/*.la
%{__install} -d %{buildroot}%{_sysconfdir}
%{__install} %{SOURCE1} %{buildroot}%{_sysconfdir}/nss-pgsql.conf

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

%files
%defattr(-,root,root)
%doc AUTHORS COPYING ChangeLog INSTALL NEWS README conf/dbschema.sql conf/nsswitch.conf doc/caution.png doc/nss-pgsql.html
%attr(0644,root,root) %config(noreplace) %{_sysconfdir}/nss-pgsql.conf
/%{_lib}/*.so*

%changelog
* Fri Oct 31 2008 Gregory Cuellar <gregory.cuellar@bull.net> 1.3.1-4.3
- Ajout rhel5

* Wed Nov 14 2007 Gilles Menigot <gilles.menigot@bull.net> 1.3.1-4.2
- Add GPL v2 license
- Requires getdist >= 1.2

* Fri Jun 08 2007 Gilles Menigot <gilles.menigot@bull.net> 1.3.1-4.1
- Moved to SVN repository

* Fri Mar 09 2007 Gilles Menigot <gilles.menigot@bull.net> 1.3.1-4
- Spec file modifications for Aurora SPARC Linux 2.0 support
- Add GCC build requirement

* Fri Feb 23 2007 Gilles Menigot <gilles.menigot@bull.net> 1.3.1-3
- Spec file modifications for RHEL 4 support

* Wed Jan 10 2007 Gilles Menigot <gilles.menigot@bull.net> 1.3.1-2
- Correction of config file (missing space caused fatal parsing error)

* Wed Oct 04 2006 Gilles Menigot <gilles.menigot@bull.net> 1.3.1-1
- Initial release, based on spec file from Bret Mogilefsky <mogul-sysauth-pgsql@gelatinous.com>
