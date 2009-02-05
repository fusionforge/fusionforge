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
%define short_name bull

# Constants related to other RPMs we provide
%define getdist_version 1.3
%define gforge_friendly_name NovaForge
%define gforge_name gforge
%define gforge_release 1.1
%define gforge_version 4.7.1

# Constants related to the distribution

# Sources and patches
Source0:	%{gforge_name}-roles-%{short_name}-%{version}.tar.gz

# Packages required for build
BuildRequires:	getdist >= %{getdist_version}

# Build architecture
BuildArch:	noarch

# Build root
BuildRoot:	%{_tmppath}/%{gforge_name}-roles-%{short_name}-%{version}-%{release}-buildroot

#
# Main package
#

Summary:	Custom default roles for Bull
Name:		%{gforge_name}-roles-%{short_name}
Version:	1.2
Release:	3.%{dist}
License:	GPL
Group:		Applications/Internet
URL:		http://novaforge.frec.bull.fr/projects/novaforge/
Requires:	getdist >= %{getdist_version}
Requires:	%{gforge_name} >= %{gforge_version}-%{gforge_release}

%description
This package customizes the default roles of %{gforge_friendly_name} for Bull.

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

%install
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && rm -rf %{buildroot}

# Install /usr/share/gforge/override/common/include
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/override/common/include
%{__install} gforgecommon/include/Role.class.php %{buildroot}%{_datadir}/%{gforge_name}/override/common/include/

%clean
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && %{__rm} -rf %{buildroot}
%{__rm} -rf %{_builddir}/%{gforge_name}-roles-%{short_name}-%{version}

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
%{_datadir}/%{gforge_name}/override/common/include/Role.class.php

%changelog
* Fri Nov 07 2008 Gregory Cuellar <gregory.cuellar@bull.net> 1.2-3
- Modif pour RHEL 5

* Wed Nov 14 2007 Gilles Menigot <gilles.menigot@bull.net> 1.1-3
- Add GPL v2 license
- Requires getdist >= 1.2
- Requires gforge >= 4.5.11-23.1

* Mon Jun 04 2007 Gilles Menigot <gilles.menigot@bull.net> 1.1-2
- Moved to SVN repository
- Added getdist version
- Requires gforge 4.5.11-19.1

* Thu Mar 22 2007 Gilles Menigot <gilles.menigot@bull.net> 1.1-1
- Remove Observateur role

* Thu Mar 15 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0-1
- Initial release
