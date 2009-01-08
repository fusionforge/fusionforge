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
%if %{dist} == "rhel4"
%define unsupported_dist 0
%endif
%if %{dist} == "rhel5"
%define unsupported_dist 0
%endif

# Constants related to this RPM
%define ant_full_name apache-ant
%define java_version 1.5.0

# Constants related to other RPMs we provide
%define getdist_version 1.2

# Constants related to the distribution
%define apache_group apache
%if %{dist} == "rhel4"
%define bash_version 3.0
%define sed_version 4.1.2
%endif
%if %{dist} == "rhel5"
%define bash_version 3.2
%define sed_version 4.1.5
%endif
%if %{unsupported_dist} == 1
%define bash_version 999
%define sed_version 999
%endif

# Sources and patches
Source0:	%{ant_full_name}-%{version}-bin.tar.gz

# Packages required for build
BuildRequires:	getdist >= %{getdist_version}
BuildRequires:	sed >= %{sed_version}

# Build architecture
BuildArch:	noarch

# Build root
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-buildroot

#
# Main package
#
Summary:	The Java-based Build Tool
Name:		ant
Version:	1.7.1
Release:	2.%{dist}
License:	GPL
Group:		Applications/Internet
URL:		http://ant.apache.org/
Requires:	bash >= %{bash_version}
Requires:	getdist >= %{getdist_version}
Requires:	jdk >= %{java_version}
Requires:	sed >= %{sed_version}

%description
Apache Ant is a Java-based build tool.

%prep
if [ "%{unsupported_dist}" = "1" ] ; then
	cat <<ENDTEXT
ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR

The Linux distribution of this system is '%{dist}'.
This package can be built on the following distributions:
- Red Hat Enterprise Linux 4 or CentOS 4 (rhel4)
- Red Hat Enterprise Linux 5 or CentOS 5 (rhel5)

ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR
ENDTEXT
	exit 1
fi
%setup -q -n apache-ant-%{version}

%build

%install
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && %{__rm} -rf %{buildroot}

# Install /usr/share/ant
%{__install} -d %{buildroot}%{_datadir}/%{name}
%{__install} -d %{buildroot}%{_datadir}/%{name}/bin
%{__cp} -r bin etc lib fetch.xml get-m2.xml %{buildroot}%{_datadir}/%{name}/

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

%preun

%files
%defattr(-,root,root)
%doc docs INSTALL KEYS LICENSE NOTICE README WHATSNEW
%{_datadir}/%{name}

%changelog
* Fri Oct 31 2008 Gregory Cuellar <gregory.cuellar@bull.net> 1.7.1-2
- Ajout rhel5

* Mon Jul 09 2008 Gregory Cuellar <gregory.cuellar@bull.net> 1.7.1-1
- Initial release
