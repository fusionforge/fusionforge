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
%define plugin_name apibull

# Constants related to other RPMs we provide
%define getdist_version 1.3
%define gforge_friendly_name NovaForge
%define gforge_name gforge
%define gforge_release 1.1
%define gforge_version 4.7.1

# Constants related to the distribution
%define apache_group apache
%define apache_user apache

# Sources and patches
Source0:	%{gforge_name}-plugin-%{plugin_name}-%{version}.tar.gz

# Packages required for build
BuildRequires:	getdist >= %{getdist_version}

# Build architecture
BuildArch:	noarch

# Build root
BuildRoot:	%{_tmppath}/%{gforge_name}-plugin-%{plugin_name}-%{version}-%{release}-buildroot

#
# Main package
#

Summary:	Bull API for %{gforge_friendly_name} plugins
Name:		%{gforge_name}-plugin-%{plugin_name}
Version:	1.11
Release:	2.%{dist}
License:	GPL
Group:		Applications/Internet
URL:		http://novaforge.frec.bull.fr/projects/novaforge/
Requires:	getdist >= %{getdist_version}
Requires:	%{gforge_name} >= %{gforge_version}-%{gforge_release}

%description
This RPM installs the API used by plugins developped by Bull for %{gforge_friendly_name}

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

# Install /etc/gforge/plugins/apibull
%{__install} -d %{buildroot}%{_sysconfdir}/%{gforge_name}/plugins/%{plugin_name}
%{__install} pluginconfig/config.php %{buildroot}%{_sysconfdir}/%{gforge_name}/plugins/%{plugin_name}/

# Install /usr/share/gforge/common
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/common/novaforge
%{__cp} -a gforgecommon/novaforge/* %{buildroot}%{_datadir}/%{gforge_name}/common/novaforge/

# Install /usr/share/gforge/config/scripts/config
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/config
%{__install} configscripts/config %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/config/plugin-%{plugin_name}
%{__sed} \
	-e "s|%PLUGIN_NAME%|%{plugin_name}|g" \
	-e "s|%NAME%|%{gforge_name}|g" \
	-e "s|%FRIENDLY_NAME%|%{gforge_friendly_name}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%BINDIR%|%{_bindir}|g" \
	-e "s|%SBINDIR%|%{_sbindir}|g" \
	-e "s|%APACHE_GROUP%|%{apache_group}|g" \
	-i %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/config/plugin-%{plugin_name}

# Install /usr/share/gforge/config/scripts/destroy
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/destroy
%{__install} configscripts/destroy %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/destroy/plugin-%{plugin_name}
%{__sed} \
	-e "s|%PLUGIN_NAME%|%{plugin_name}|g" \
	-e "s|%NAME%|%{gforge_name}|g" \
	-e "s|%FRIENDLY_NAME%|%{gforge_friendly_name}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-i %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/destroy/plugin-%{plugin_name}

# Install /usr/share/gforge/override
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/override
pushd %{buildroot}%{_datadir}/%{gforge_name}
for TOPDIR in common ; do
	DIRS=`find $TOPDIR -type d -printf "%%P\n"`
	for DIR in $DIRS ; do
		if [ -n "$DIR" ] ; then
			%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/override/$TOPDIR/$DIR
		fi
	done
done
popd

%clean
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && %{__rm} -rf %{buildroot}
%{__rm} -rf %{_builddir}/%{gforge_name}-plugin-%{plugin_name}-%{version}

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
%dir %{_sysconfdir}/%{gforge_name}/plugins/%{plugin_name}
%{_sysconfdir}/%{gforge_name}/plugins/%{plugin_name}/config.php
%{_datadir}/%{gforge_name}/common/novaforge
%attr(0755,root,root) %{_datadir}/%{gforge_name}/config/scripts/config/plugin-%{plugin_name}
%attr(0755,root,root) %{_datadir}/%{gforge_name}/config/scripts/destroy/plugin-%{plugin_name}
%{_datadir}/%{gforge_name}/override/common/novaforge

%changelog
* Fri Nov 07 2008 Gregory Cuellar <gregory.cuellar@bull.net> 1.11-2
- Modif pour RHEL 5

* Fri Jul 04 2008 Gilles Menigot <gilles.menigot@bull.net> 1.11-1
- Correct the changeLink and deleteHtmlHeader functions in class ProxyReponse
- Correct the translateUrl function in ProxyConfig
- Correct the cookies prefix management
- Reformat the code
- Requires gforge >= 4.5.11-30.1

* Fri Mar 07 2008 Gilles Menigot <gilles.menigot@bull.net> 1.10-1
- Remove unnecessary setting of sys_auth_* variables in encryptText
  function

* Thu Feb 21 2008 Gilles Menigot <gilles.menigot@bull.net> 1.9-1
- Requires gforge >= 4.5.11-28.1
- Remove Bull API classes (ErrorApi, GroupApi, PermissionApi,
  SessionApi and UserApi)

* Fri Nov 16 2007 Gilles Menigot <gilles.menigot@bull.net> 1.8-1
- Correct authentication and proxy

* Wed Nov 14 2007 Gilles Menigot <gilles.menigot@bull.net> 1.7-1
- Add GPL v2 license
- Requires getdist >= 1.2
- Requires gforge >= 4.5.11-23.1

* Wed Sep 26 2007 Gilles Menigot <gilles.menigot@bull.net> 1.6-1
- Add config and destroy scripts, and manage OpenSSL keys
  for other plugins
- Requires gforge 4.5.11-21.1

* Mon Jun 04 2007 Gilles Menigot <gilles.menigot@bull.net> 1.5-2
- Moved to SVN repository
- Requires gforge 4.5.11-19.1
- Correct absolute URL rewriting in reverse-proxy classes

* Thu May 24 2007 Gilles Menigot <gilles.menigot@bull.net> 1.5-1
- svn tag: gforge-plugin-apibull-1.5
- Correct IE related bug

* Thu May 03 2007 Gilles Menigot <gilles.menigot@bull.net> 1.4-1
- svn tag: gforge-plugin-apibull-1.4
- Correct CSS loading with IE in reverse-proxy classes
- Correct download with IE in reverse-proxy classes
- Correct POST variables transmission in reverse-proxy classes

* Tue Apr 24 2007 Gilles Menigot <gilles.menigot@bull.net> 1.3-1
- svn tag: gforge-plugin-apibull-1.3
- add reverse-proxy API
- password are now encrypted
- Requires gforge-4.5.11-12

* Tue Mar 13 2007 Gilles Menigot <gilles.menigot@bull.net> 1.2-4
- Add missing version of getdist
- Spec file modifications for Aurora SPARC Linux 2.0 support

* Fri Feb 23 2007 Gilles Menigot <gilles.menigot@bull.net> 1.2-3
- Spec file modifications for RHEL 4

* Wed Feb 14 2007 Gilles Menigot <gilles.menigot@bull.net> 1.2-2
- Correction of removal script

* Mon Feb 12 2007 Gilles Menigot <gilles.menigot@bull.net> 1.2-1
- svn tag: gforge-plugin-apibull-1.2

* Thu Feb 08 2007 Gilles Menigot <gilles.menigot@bull.net> 1.1-1
- svn tag: gforge-plugin-apibull-1.1

* Fri Feb 02 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0-1
- Initial revision
- svn tag: gforge-plugin-apibull-1.0
