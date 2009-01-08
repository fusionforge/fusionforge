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
%define httpd_version 2.0.46
%endif
%if %{dist} == "rhel4"
%define gcc_version 3.4.3
%define glibc_version 2.3.4
%define httpd_version 2.0.52
%endif
%if %{dist} == "rhel5"
%define gcc_version 4.1.2
%define glibc_version 2.5
%define httpd_version 2.2.3
%endif
%if %{dist} == "aurora2"
%define gcc_version 3.4.2
%define glibc_version 2.3.3
%define httpd_version 2.0.52
%endif
%if %{unsupported_dist} == 1
%define gcc_version 999
%define glibc_version 999
%define httpd_version 999
%endif

# Sources and patches
Source0:	http://www.outoforder.cc/downloads/mod_umask/%{name}-%{version}.tar.bz2
Source1:	mod_umask.apache

# Packages required for build
BuildRequires:	gcc >= %{gcc_version}
BuildRequires:	getdist >= %{getdist_version}
BuildRequires:	glibc-devel >= %{glibc_version}
BuildRequires:	httpd-devel >= %{httpd_version}

# Build architecture

# Build root
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-buildroot

#
# Main package
#

Summary:	Apache module for setting umask
Name:		mod_umask
Version:	0.1.0
Release:	4.3.%{dist}
License:	Apache License
Group:		System/Servers
URL:		http://www.outoforder.cc/projects/apache/mod_umask/
Requires:	getdist >= %{getdist_version}
Requires:	glibc >= %{glibc_version}
Requires:	httpd >= %{httpd_version}

%description
Sets the Unix umask of the Apache httpd processes.

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
%setup -q

%build
%{_sbindir}/apxs -c src/mod_umask.c

%install
[ "%{buildroot}" != "/" ] && rm -rf %{buildroot}
%{__install} -d %{buildroot}%{_libdir}/httpd/modules
%{_sbindir}/apxs -i \
	-S LIBEXECDIR=%{buildroot}%{_libdir}/httpd/modules \
	-n umask \
	src/.libs/mod_umask.so
%{__install} -d %{buildroot}%{_sysconfdir}/httpd/conf.d
%{__install} %{SOURCE1} %{buildroot}%{_sysconfdir}/httpd/conf.d/umask.conf

%clean
[ "%{buildroot}" != "/" ] && %{__rm} -rf %{buildroot}
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
%attr(0644,root,root) %{_sysconfdir}/httpd/conf.d/umask.conf
%attr(0755,root,root) %{_libdir}/httpd/modules/mod_umask.so

%changelog
* Fri Oct 31 2008 Gregory Cuellar <gregory.cuellar@bull.net> 0.1.0-4.3
- Ajout rhel5

* Wed Nov 14 2007 Gilles Menigot <gilles.menigot@bull.net> 0.1.0-4.2
- Add GPL v2 license
- Requires getdist >= 1.2

* Fri Jun 08 2007 Gilles Menigot <gilles.menigot@bull.net> 0.1.0-4.1
- Moved to SVN repository

* Fri Mar 09 2007 Gilles Menigot <gilles.menigot@bull.net> 0.1.0-4
- Spec file modifications for Aurora SPARC Linux 2.0 support
- Add GCC build requirement

* Fri Feb 23 2007 Gilles Menigot <gilles.menigot@bull.net> 0.1.0-3
- Spec file modifications for RHEL 4 support

* Mon Nov 06 2006 Gilles Menigot <gilles.menigot@bull.net> 0.1.0-2
- Minor spec correction

* Tue Oct 31 2006 Gilles Menigot <gilles.menigot@bull.net> 0.1.0-1
- Initial release for RHEL 3
