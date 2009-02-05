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
%define plugin_name archiva

# Constants related to other RPMs we provide
%define getdist_version 1.2
%define gforge_friendly_name NovaForge
%define gforge_name gforge
%define gforge_plugin_apibull_release 1
%define gforge_plugin_apibull_version 1.11
%define gforge_release 30.1
%define gforge_version 4.5.11

# Constants related to the distribution
%define apache_group apache
%define apache_user apache
%if %{dist} == "rhel4"
%define sed_version 4.1.2
%define php_version 4.3.9
%endif
%if %{dist} == "rhel5"
%define sed_version 4.1.5
%define php_version 5.1.6
%endif
%if %{unsupported_dist} == 1
%define sed_version 999
%define php_version 999
%endif

# Sources and patches
Source0:	%{gforge_name}-plugin-%{plugin_name}-%{version}.tar.gz

# Packages required for build
BuildRequires:	getdist >= %{getdist_version}
BuildRequires:	sed >= %{sed_version}

# Build architecture
BuildArch:	noarch

# Build root
BuildRoot:	%{_tmppath}/%{gforge_name}-plugin-%{plugin_name}-%{version}-%{release}-buildroot

#
# Main package
#

Summary:	Archiva plugin for %{gforge_friendly_name}
Name:		%{gforge_name}-plugin-%{plugin_name}
Version:	1.1
Release:	1.%{dist}
License:	GPL
Group:		Applications/Internet
URL:		http://novaforge.frec.bull.fr/projects/novaforge/
Requires:	getdist >= %{getdist_version}
Requires:	%{gforge_name} >= %{gforge_version}-%{gforge_release}
Requires:	%{gforge_name}-plugin-apibull >= %{gforge_plugin_apibull_version}-%{gforge_plugin_apibull_release}
Requires: gettext

%description
This RPM installs the Archiva plugin of %{gforge_friendly_name}.

%prep
if [ "%{unsupported_dist}" = "1" ] ; then
	cat <<ENDTEXT
ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR

The Linux distribution of this system is '%{dist}'.
This package can be built on the following distributions:
- Red Hat Enterprise Linux 4 or CentOS 4 (rhel4)

ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR
ENDTEXT
	exit 1
fi
%setup -q

%build
	
%install
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && rm -rf %{buildroot}

# Install /etc/httpd/conf.d
%{__install} -d %{buildroot}%{_sysconfdir}/httpd/conf.d
%{__install} apacheconfig/%{gforge_name}-plugin-%{plugin_name}.conf %{buildroot}%{_sysconfdir}/httpd/conf.d
%{__sed} \
	-e "s|%NAME%|%{gforge_name}|g" \
	-e "s|%FRIENDLY_NAME%|%{gforge_friendly_name}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%PLUGIN_NAME%|%{plugin_name}|g" \
	-i %{buildroot}%{_sysconfdir}/httpd/conf.d/%{gforge_name}-plugin-%{plugin_name}.conf

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
	-i %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/destroy/plugin-%{plugin_name}

# Install /usr/share/gforge/config/scripts/remove
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/remove
%{__install} configscripts/remove %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/remove/plugin-%{plugin_name}
%{__sed} \
	-e "s|%PLUGIN_NAME%|%{plugin_name}|g" \
	-e "s|%NAME%|%{gforge_name}|g" \
	-e "s|%FRIENDLY_NAME%|%{gforge_friendly_name}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%BINDIR%|%{_bindir}|g" \
	-e "s|%SBINDIR%|%{_sbindir}|g" \
	-e "s|%APACHE_GROUP%|%{apache_group}|g" \
	-i %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/remove/plugin-%{plugin_name}

# Install /usr/share/gforge/plugins/archiva
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/plugins/%{plugin_name}
%{__cp} -a plugincore/* %{buildroot}%{_datadir}/%{gforge_name}/plugins/%{plugin_name}/

# Install /usr/share/gforge/www/plugins/archiva
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/www/plugins/%{plugin_name}
%{__install} pluginwww/* %{buildroot}%{_datadir}/%{gforge_name}/www/plugins/%{plugin_name}/

# Install /usr/share/locale
%{__install} -d %{buildroot}%{_datadir}/locale
%{__cp} -a locale/* %{buildroot}%{_datadir}/locale/

%find_lang %{name}

# Install /usr/share/gforge/override
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/override
pushd %{buildroot}%{_datadir}/%{gforge_name}
for TOPDIR in plugins www ; do
	DIRS=`find $TOPDIR -type d -printf "%%P\n" 2>/dev/null`
	for DIR in $DIRS ; do
		if [ -n "$DIR" ] ; then
			%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/override/$TOPDIR/$DIR
		fi
	done
done
popd

# Install /var/lib/gforge/config/plugin-archiva
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{gforge_name}/config/plugin-%{plugin_name}

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

%files -f %{name}.lang
%defattr(-,root,root)
%doc LICENSE
%{_sysconfdir}/httpd/conf.d/%{gforge_name}-plugin-%{plugin_name}.conf
%attr(0755,root,root) %{_datadir}/%{gforge_name}/config/scripts/config/plugin-%{plugin_name}
%attr(0755,root,root) %{_datadir}/%{gforge_name}/config/scripts/destroy/plugin-%{plugin_name}
%attr(0755,root,root) %{_datadir}/%{gforge_name}/config/scripts/remove/plugin-%{plugin_name}
%{_datadir}/%{gforge_name}/override/plugins/%{plugin_name}
%{_datadir}/%{gforge_name}/override/www/plugins/%{plugin_name}
%{_datadir}/%{gforge_name}/plugins/%{plugin_name}
%{_datadir}/%{gforge_name}/www/plugins/%{plugin_name}
%{_localstatedir}/lib/%{gforge_name}/config/plugin-%{plugin_name}

%changelog
* Mon Jan 26 2009 Jean-Yves Cronier <jean-yves.cronier@bull.net> 1.1-1
- Migration vers NovaForge 1.2
- Gestion de l'internationnalisation
* Fri Jul 04 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0-1
- Initial release
