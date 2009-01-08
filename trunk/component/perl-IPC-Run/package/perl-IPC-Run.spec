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
%define real_name IPC-Run

# Constants related to other RPMs we provide
%define getdist_version 1.2

# Constants related to the distribution
%if %{dist} == "rhel3"
%define perl_version 5.8.0
%endif
%if %{dist} == "rhel4"
%define perl_version 5.8.5
%endif
%if %{dist} == "rhel5"
%define perl_version 5.8.8
%endif
%if %{dist} == "aurora2"
%define perl_version 5.8.5
%endif
%if %{unsupported_dist} == 1
%define perl_version 999
%endif

# Sources and patches
Source0:	http://search.cpan.org/CPAN/authors/id/R/RS/RSOD/%{real_name}-%{version}.tar.gz

# Packages required for build
BuildRequires:	getdist >= %{getdist_version}
BuildRequires:	perl >= %{perl_version}

# Build architecture
BuildArch:	noarch

# Build root
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-buildroot

#
# Main package
#

Summary:	Perl IPC functions
Name:		perl-%{real_name}
Version:	0.80
Release:	2.3.%{dist}
License:	Artistic/GPL
Group:		Applications/CPAN
URL:		http://search.cpan.org/dist/IPC-Run/
Requires:	getdist >= %{getdist_version}
Requires:	perl >= %{perl_version}

%description
This module provides various Perl IPC functionalities.

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

%build
%{__perl} Makefile.PL INSTALLDIRS="vendor" PREFIX="%{buildroot}%{_prefix}"
%{__make} %{?_smp_mflags} OPTIMIZE="%{optflags}"

%install
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && %{__rm} -rf %{buildroot}
%makeinstall
# Cleanup unneeded files
%if %{dist} == "rhel4"
%{__rm} -rf %{buildroot}%{_libdir}/perl5/%{perl_version}
%{__rm} -rf %{buildroot}%{_libdir}/perl5/vendor_perl/%{perl_version}/%{_build_arch}-linux-thread-multi
%endif
%if %{dist} == "rhel5"
%{__rm} -rf %{buildroot}%{_libdir}/perl5/%{perl_version}
%{__rm} -rf %{buildroot}%{_libdir}/perl5/vendor_perl/%{perl_version}/%{_build_arch}-linux-thread-multi
%endif
%if %{dist} == "aurora2"
%{__rm} -rf %{buildroot}%{_libdir}/perl5/%{perl_version}
%{__rm} -rf %{buildroot}%{_libdir}/perl5/vendor_perl/%{perl_version}/%{_build_arch}-linux-thread-multi
%endif
%{__rm} -f %{buildroot}%{_libdir}/perl5/vendor_perl/%{perl_version}/IPC/Run/Win32*

%clean
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && %{__rm} -rf %{buildroot}
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
%defattr(-, root, root, 0755)
%doc Changes
%doc %{_mandir}/man3/*
%{_libdir}/perl5/vendor_perl/%{perl_version}/IPC/Run.pm
%{_libdir}/perl5/vendor_perl/%{perl_version}/IPC/Run/*

%changelog
* Fri Oct 31 2008 Gregory Cuellar <gregory.cuellar@bull.net> 0.80-2.3
- Ajout rhel5

* Wed Nov 14 2007 Gilles Menigot <gilles.menigot@bull.net> 0.80-2.2
- Add GPL v2 license
- Requires getdist >= 1.2

* Fri Jun 08 2007 Gilles Menigot <gilles.menigot@bull.net> 0.80-2.1
- Moved to SVN repository

* Fri Mar 09 2007 Gilles Menigot <gilles.menigot@bull.net> 0.80-2
- Spec file modifications for Aurora SPARC Linux 2.0 support

* Fri Feb 23 2007 Gilles Menigot <gilles.menigot@bull.net> 0.80-1
- Based on http://ftp.belnet.be/packages/dries.ulyssis.org/redhat/el4/en/i386/SRPMS.dries/perl-IPC-Run-0.80-1.el4.rf.src.rpm
- Initial release
