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

# Constants related to other RPMs we provide
%define getdist_version 1.2

# Constants related to the distribution
%if %{dist} == "rhel3"
%define gcc_version 3.2.3
%endif
%if %{dist} == "rhel4"
%define gcc_version 3.4.3
%endif
%if %{dist} == "rhel5"
%define gcc_version 4.1.2
%endif
%if %{dist} == "aurora2"
%define gcc_version 3.4.2
%endif
%if %{unsupported_dist} == 1
%define gcc_version 999
%endif

# Sources and patches
Source0:	http://dl.sf.net/pwgen/pwgen-%{version}.tar.gz

# Packages required for build
BuildRequires:	gcc >= %{gcc_version}
BuildRequires:	getdist >= %{getdist_version}

# Build architecture

# Build root
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-buildroot

#
# Main package
#

Summary:	Automatic password generation
Name:		pwgen
Version:	2.05
Release:	5.3.%{dist}
License:	GPL
Group:		Applications/System
URL:            http://sf.net/projects/pwgen
Requires:	getdist >= %{getdist_version}

%description
pwgen generates random, meaningless but pronounceable passwords. These
passwords contain either only lowercase letters, or upper and lower case, or
upper case, lower case and numeric digits. Upper case letters and numeric
digits are placed in a way that eases memorizing the password.

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
%configure
make %{?_smp_mflags}

%install
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && %{__rm} -rf %{buildroot}
make install DESTDIR=%{buildroot}

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
%doc ChangeLog
%{_bindir}/pwgen
%{_mandir}/man1/pwgen.1*

%changelog
* Fri Oct 31 2008 Gregory Cuellar <gregory.cuellar@bull.net> 2.05-5.3
- Ajout rhel5

* Wed Nov 14 2007 Gilles Menigot <gilles.menigot@bull.net> 2.05-5.2
- Add GPL v2 license
- Requires getdist >= 1.2

* Mon Jun 04 2007 Gilles Menigot <gilles.menigot@bull.net> 2.05-5.1
- Moved to SVN repository

* Fri Mar 09 2007 Gilles Menigot <gilles.menigot@bull.net> 2.05-5
- Spec file modifications for Aurora SPARC Linux 2.0 support
- Add GCC build requirement

* Fri Feb 23 2007 Gilles menigot <gilles.menigot@bull.net> 2.05-4
- Add distribution checking

* Sat Mar 25 2006 James Bowes <jbowes@redhat.com> 2.05-3
- Add dist tag to release.
- Don't strip binary, since rpmbuild will do it.

* Fri Mar 24 2006 James Bowes <jbowes@redhat.com> 2.05-2
- Use url for Source0 in spec file.
- Use glob for man page extension.

* Sun Mar 12 2006 James Bowes <jbowes@redhat.com> 2.05-1
- Initial Fedora packaging.
