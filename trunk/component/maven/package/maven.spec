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
%define maven_full_name apache-maven
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
Source0:	%{maven_full_name}-%{version}-bin.tar.gz
Patch0:		%{maven_full_name}-%{version}-repo.diff

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
Summary:	The Project Management And Comprehension Tool
Name:		maven
Version:	2.0.9
Release:	2.%{dist}
License:	GPL
Group:		Applications/Internet
URL:		http://maven.apache.org/
Requires:	bash >= %{bash_version}
Requires:	getdist >= %{getdist_version}
Requires:	jdk >= %{java_version}
Requires:	sed >= %{sed_version}

%description
Maven is a software project management and comprehension tool.
Based on the concept of a project object model (POM), Maven can 
manage a project's build, reporting and documentation from a 
central piece of information. 

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
%setup -q -n apache-maven-%{version}
%patch -p1

%build

%install
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && %{__rm} -rf %{buildroot}

# Install /usr/share/maven
%{__install} -d %{buildroot}%{_datadir}/%{name}
%{__install} -d %{buildroot}%{_datadir}/%{name}/bin
%{__cp} -r bin boot conf lib %{buildroot}%{_datadir}/%{name}/

# Install /var/lib/maven
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/repository

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
# Set owner of /var/lib/maven tree
chown -R root.root %{_localstatedir}/lib/%{name}
chmod -R g+w %{_localstatedir}/lib/%{name}
chmod -R o+rwx %{_localstatedir}/lib/%{name}

%preun

%files
%defattr(-,root,root)
%doc LICENSE.txt NOTICE.txt README.txt
%{_datadir}/%{name}
%verify(not mode user group) %{_localstatedir}/lib/%{name}

%changelog
* Fri Oct 31 2008 Gregory Cuellar <gregory.cuellar@bull.net> 2.0.9-2
- Ajout rhel5

* Mon Jul 07 2008 Gregory Cuellar <gregory.cuellar@bull.net> 2.0.9-1
- Initial release
