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
%define plugin_name novafrs

# Constants related to other RPMs we provide
%define getdist_version 1.2
%define gforge_friendly_name NovaForge
%define gforge_name gforge
%define gforge_plugin_apibull_release 1
%define gforge_plugin_apibull_version 1.9
%define gforge_release 28.1
%define gforge_roles_bull_version 1.1
%define gforge_version 4.5.11

# Constants related to the distribution
%define apache_group apache
%define apache_user apache
%if %{dist} == "rhel3"
%define sed_version 4.0.7
%endif
%if %{dist} == "rhel4"
%define sed_version 4.1.2
%endif
%if %{dist} == "rhel5"
%define sed_version 4.1.5
%endif
%if %{dist} == "aurora2"
%define sed_version 4.1.2
%endif
%if %{unsupported_dist} == 1
%define sed_version 999
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

Summary:	FRS plugin for %{gforge_friendly_name}
Name:		%{gforge_name}-plugin-%{plugin_name}
Version:	1.13
Release:	1.%{dist}
License:	GPL
Group:		Applications/Internet
URL:		http://novaforge.frec.bull.fr/projects/novaforge/
Requires:	getdist >= %{getdist_version}
Requires:	%{gforge_name} >= %{gforge_version}-%{gforge_release}
Requires:	%{gforge_name}-plugin-apibull >= %{gforge_plugin_apibull_version}-%{gforge_plugin_apibull_release}
Requires:	%{name}-config
Requires: gettext

%description
This RPM installs the File Release System (FRS) plugin of %{gforge_friendly_name}.

#
# Sub-package config
#

%package	config-standard
Summary:	Configuration of FRS plugin for %{gforge_friendly_name}
Group:		Applications/Internet
Provides:	%{name}-config
Requires:	%{name} = %{version}-%{release}
Requires:	%{gforge_name}-roles-bull >= %{gforge_roles_bull_version}

%description	config-standard
This RPM installs the configuration of the File Release System (FRS)
plugin for %{gforge_friendly_name}.

%prep
if [ "%{unsupported_dist}" = "1" ] ; then
	cat <<ENDTEXT
ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR

The Linux distribution of this system is '%{dist}'.
This package can be built on the following distributions:
- Red Hat Enterprise Linux 3 or CentOS 3 (rhel3)
- Red Hat Enterprise Linux 4 or CentOS 4 (rhel4)
- Aurora SPARC Linux 2.0 (aurora2)

ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR
ENDTEXT
	exit 1
fi
%setup -q

%build
	
%install
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && rm -rf %{buildroot}

# /etc/gforge/plugins/novafrs
%{__install} -d %{buildroot}%{_sysconfdir}/%{gforge_name}/plugins/%{plugin_name}
%{__install} pluginconfig/config.php %{buildroot}%{_sysconfdir}/%{gforge_name}/plugins/%{plugin_name}/config.php

# /usr/share/gforge/config/scripts/config
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

# /usr/share/gforge/config/scripts/destroy
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/destroy
%{__install} configscripts/destroy %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/destroy/plugin-%{plugin_name}
%{__sed} \
	-e "s|%PLUGIN_NAME%|%{plugin_name}|g" \
	-e "s|%NAME%|%{gforge_name}|g" \
	-e "s|%FRIENDLY_NAME%|%{gforge_friendly_name}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-i %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/destroy/plugin-%{plugin_name}

# /usr/share/gforge/config/scripts/remove
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

# /usr/share/gforge/plugins/novafrs
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/plugins/%{plugin_name}
%{__cp} -a plugincore/* %{buildroot}%{_datadir}/%{gforge_name}/plugins/%{plugin_name}/

# /usr/share/gforge/www/plugins/novafrs
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/www/plugins/%{plugin_name}
%{__cp} -a pluginwww/* %{buildroot}%{_datadir}/%{gforge_name}/www/plugins/%{plugin_name}/

# Install /usr/share/locale
if [ -e "locale" ] ; then
	%{__install} -d %{buildroot}%{_datadir}/locale
	%{__cp} -a locale/* %{buildroot}%{_datadir}/locale/
	%find_lang %{name}
fi

# /usr/share/gforge/override
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/override/plugins/%{plugin_name}
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

# /var/lib/gforge/config/plugin-novafrs
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{gforge_name}/config/plugin-%{plugin_name}

# /var/lib/gforge/novafrs
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{gforge_name}/%{plugin_name}

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
%dir %{_sysconfdir}/%{gforge_name}/plugins/%{plugin_name}
%attr(0755,root,root) %{_datadir}/%{gforge_name}/config/scripts/config/plugin-%{plugin_name}
%attr(0755,root,root) %{_datadir}/%{gforge_name}/config/scripts/destroy/plugin-%{plugin_name}
%attr(0755,root,root) %{_datadir}/%{gforge_name}/config/scripts/remove/plugin-%{plugin_name}
%{_datadir}/%{gforge_name}/override/plugins/%{plugin_name}
%dir %{_datadir}/%{gforge_name}/plugins/%{plugin_name}
%{_datadir}/%{gforge_name}/plugins/%{plugin_name}/db
%dir %{_datadir}/%{gforge_name}/plugins/%{plugin_name}/bin
%{_datadir}/%{gforge_name}/plugins/%{plugin_name}/bin/frs2novafrs.php
%attr(0755,root,root) %{_datadir}/%{gforge_name}/plugins/%{plugin_name}/bin/frs2novafrs.sh
%{_datadir}/%{gforge_name}/plugins/%{plugin_name}/include
%{_datadir}/%{gforge_name}/www/plugins/%{plugin_name}
%dir %{_localstatedir}/lib/%{gforge_name}/config/plugin-%{plugin_name}
%attr(2750,%{apache_user},%{apache_group}) %dir %{_localstatedir}/lib/%{gforge_name}/%{plugin_name}

%files config-standard
%defattr(-,root,root)
%attr(0644,root,root) %{_sysconfdir}/%{gforge_name}/plugins/%{plugin_name}/config.php

%changelog
* Tue Feb 03 2009 Jean-Yves Cronier <jean-yves.cronier@bull.net> 1.13-1
- Migration NovaForge 1.2
- i18n

* Fri Sep 12 2008 Gregory Cuellar <gregory.cuellar@bull.net> 1.12-1
- Correct security issue

* Tue Mar 25 2008 Gilles Menigot <gilles.menigot@bull.net> 1.11-1
- Modify functions names in utils.php to not redeclare functions of
  orginal frs

* Thu Feb 14 2008 Gilles Menigot <gilles.menigot@bull.net> 1.10-1
- Requires gforge >= 4.5.11-28.1
- Requires gforge-plugin-apibull >= 1.9-1
- Replace Bull API classes by standard GForge classes (Error, Group,
  User,...)

* Thu Nov 15 2007 Gilles Menigot <gilles.menigot@bull.net> 1.9-1
- Add GPL v2 license
- Requires getdist >= 1.2
- Requires gforge >= 4.5.11-23.1
- Requires gforge-plugin-apibull >= 1.7-1
- Requires gforge-roles-bull >= 1.1

* Thu Oct 11 2007 Gilles Menigot <gilles.menigot@bull.net> 1.8-2
- Add missing Provides

* Wed Oct 10 2007 Gilles Menigot <gilles.menigot@bull.net> 1.8-1
- Add i18n for plugin selection text
- Add sub-package for configuration
- Requires gforge >= 4.5.11-21.1
- Requires gforge-plugin-apibull >= 1.6-1

* Thu Jun 07 2007 Gilles Menigot <gilles.menigot@bull.net> 1.7-1
- Moved to SVN repository
- Remove script is not executed anymore at %preun
- correct error when submitting document (group == 100)
- Requires gforge 4.5.11-19.1
- Add frs2novafrs.sh script to migrate from frs to novafrs

* Thu May 03 2007 Gilles Menigot <gilles.menigot@bull.net> 1.6.2-1
- svn tag: gforge-plugin-novafrs-1.6.2
- Correct creating a previously deleted folder
- Requires gforge-plugin-apibull 1.4

* Mon Apr 23 2007 Gilles Menigot <gilles.menigot@bull.net> 1.6-1
- svn tag: gforge-plugin-novafrs-1.6
- minor code corrections (File.class and view.php)
- i18n corrections (Base.tab and French.tab)
- Set permissions of config.php to root.root and 644 so that it
  can be included by non-Apache PHP scripts
- Requires gforge 4.5.11-17

* Wed Apr 11 2007 Gilles Menigot <gilles.menigot@bull.net> 1.5-1
- svn tag: gforge-plugin-novafrs-1.5
- improve rights management
- add chrono functionnality
- add javascript and images
- remove SCM related stuff

* Tue Mar 20 2007 Gilles Menigot <gilles.menigot@bull.net> 1.4.2-2
- Add message in config script  if /usr/share/gforge/config/util/functions
  is missing

* Tue Mar 13 2007 Gilles Menigot <gilles.menigot@bull.net> 1.4.2-1
- RPM renamed gforge-plugin-novafrs (previously: gforge-plugin-frs)
- svn tag: gforge-plugin-novafrs-1.4.2
- Add missing version of getdist
- Spec file modifications for Aurora SPARC Linux 2.0 support

* Mon Mar 05 2007 Gilles Menigot <gilles.menigot@bull.net> 1.3-2
- svn tag: gforge-plugin-novafrs-1.3

* Fri Feb 23 2007 Gilles Menigot <gilles.menigot@bull.net> 1.2-3
- Spec file modifications for RHEL 4

* Wed Feb 14 2007 Gilles Menigot <gilles.menigot@bull.net> 1.2-2
- Correction of removal script

* Mon Feb 12 2007 Gilles Menigot <gilles.menigot@bull.net> 1.2-1
- svn tag: gforge-plugin-novafrs-1.2

* Thu Feb 08 2007 Gilles Menigot <gilles.menigot@bull.net> 1.1-1
- svn tag: gforge-plugin-novafrs-1.1

* Fri Feb 02 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0-1
- Initial release
- svn tag: gforge-plugin-novafrs-1.0
