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
%define continuum_full_name apache-continuum
%define continuum_custom_level 1
%define continuum_custom_version %{version}.%{continuum_custom_level}
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
Source0:	%{continuum_full_name}-%{continuum_custom_version}_NovaForge-bin.tar.gz
Source1:	%{continuum_full_name}-init.sh
Source2:	%{continuum_full_name}-config.sh
Source3:	%{continuum_full_name}-startup.sh

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
Summary:	The Continuous Integration Server
Name:		continuum
Version:	1.1
Release:	%{continuum_custom_level}.2.%{dist}
License:	GPL
Group:		Applications/Internet
URL:		http://continuum.apache.org
Requires:	bash >= %{bash_version}
Requires:	getdist >= %{getdist_version}
Requires:	jdk >= %{java_version}
Requires:	sed >= %{sed_version}

%description
Apache Continuum is a continuous integration server for building 
Java based projects.
It supports a wide range of projects such as Maven 1, Maven 2, 
Ant and Shell scripts.

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
%setup -q -n apache-continuum-%{continuum_custom_version}_NovaForge

%build

%install
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && %{__rm} -rf %{buildroot}

# Install /etc/rc.d/init.d
%{__install} -d %{buildroot}%{_initrddir}
%{__install} %{SOURCE1} %{buildroot}%{_initrddir}/%{name}
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-e "s|%INITRDDIR%|%{_initrddir}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-i %{buildroot}%{_initrddir}/%{name}

# Install /etc/sysconfig
%{__install} -d %{buildroot}%{_sysconfdir}/sysconfig
%{__install} %{SOURCE2} %{buildroot}%{_sysconfdir}/sysconfig/continuum

# Install /usr/share/continuum
%{__install} -d %{buildroot}%{_datadir}/%{name}
%{__install} -d %{buildroot}%{_datadir}/%{name}/bin
%{__install} %{SOURCE3} %{buildroot}%{_datadir}/%{name}/bin/startup
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-e "s|%INITRDDIR%|%{_initrddir}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-i %{buildroot}%{_datadir}/%{name}/bin/startup
%{__cp} -r core %{buildroot}%{_datadir}/%{name}/
%{__install} -d %{buildroot}%{_datadir}/%{name}/templates
%{__install} conf/plexus.xml %{buildroot}%{_datadir}/%{name}/templates/
%{__sed} \
	-e "s/8080/%HTTP_PORT%/g" \
	-i %{buildroot}%{_datadir}/%{name}/templates/plexus.xml

# Install /var/lib/continuum
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/data
%{__cp} -r apps conf logs services temp %{buildroot}%{_localstatedir}/lib/%{name}/
%{__rm} -f %{buildroot}%{_localstatedir}/lib/%{name}/conf/plexus.xml
touch %{buildroot}%{_localstatedir}/lib/%{name}/conf/plexus.xml

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
# Add user and group
useradd -c "Continuum Server" -d %{_localstatedir}/lib/%{name} -M -s /bin/bash %{name} >> /dev/null 2>&1 || :
# Add service
chkconfig --add %{name} >> /dev/null 2>&1 || :
# Set owner of /var/lib/continuum tree
chown -R %{name}.%{name} %{_localstatedir}/lib/%{name}
chmod -R g-w %{_localstatedir}/lib/%{name}
chmod -R o-rwx %{_localstatedir}/lib/%{name}

%preun
# Stop service
%{_initrddir}/%{name} stop >> /dev/null 2>&1 || :
# Remore service
chkconfig --del %{name} >> /dev/null 2>&1 || :
# Remove user and group
userdel %{name} >> /dev/null 2>&1 || :

%files
%defattr(-,root,root)
%doc LICENSE NOTICE
%attr(0755,root,root) %{_initrddir}/%{name}
%config(noreplace) %{_sysconfdir}/sysconfig/continuum
%{_datadir}/%{name}
%verify(not mode user group) %{_localstatedir}/lib/%{name}
%verify(not mode user group md5 size mtime) %{_localstatedir}/lib/%{name}/conf/plexus.xml

%changelog
* Fri Oct 31 2008 Gregory Cuellar <gregory.cuellar@bull.net> 1.1-2
- Ajout rhel5

* Mon Jul 07 2008 Gregory Cuellar <gregory.cuellar@bull.net> 1.1-1.1
- Initial release
