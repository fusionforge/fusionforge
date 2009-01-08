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
%define plugin_name mantis

# Constants related to other RPMs we provide
%define getdist_version 1.3
%define gforge_friendly_name NovaForge
%define gforge_name gforge
%define gforge_plugin_apibull_release 2
%define gforge_plugin_apibull_version 1.11
%define gforge_release 1.1
%define gforge_version 4.7.1

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

Summary:	Mantis plugin for %{gforge_friendly_name}
Name:		%{gforge_name}-plugin-%{plugin_name}
Version:	2.9
Release:	1.%{dist}
License:	GPL
Group:		Applications/Internet
URL:		http://novaforge.frec.bull.fr/projects/novaforge/
Requires:	getdist >= %{getdist_version}
Requires:	%{gforge_name} >= %{gforge_version}-%{gforge_release}
Requires:	%{gforge_name}-plugin-apibull >= %{gforge_plugin_apibull_version}-%{gforge_plugin_apibull_release}

%description
This RPM installs the Mantis plugin of %{gforge_friendly_name}.

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

# Install /etc/cron.d
%{__install} -d %{buildroot}%{_sysconfdir}/cron.d
%{__install} crond/gforge-plugin-mantis %{buildroot}%{_sysconfdir}/cron.d/%{gforge_name}-plugin-%{plugin_name}
%{__sed} \
	-e "s|%PLUGIN_NAME%|%{plugin_name}|g" \
	-e "s|%NAME%|%{gforge_name}|g" \
	-e "s|%FRIENDLY_NAME%|%{gforge_friendly_name}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-e "s|%BINDIR%|%{_bindir}|g" \
	-i %{buildroot}%{_sysconfdir}/cron.d/%{gforge_name}-plugin-%{plugin_name}

# Install /etc/httpd/conf.d
%{__install} -d %{buildroot}%{_sysconfdir}/httpd/conf.d
touch %{buildroot}%{_sysconfdir}/httpd/conf.d/%{gforge_name}-plugin-%{plugin_name}.conf

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

# Install /usr/share/gforge/config/skel/plugin-mantis
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/config/skel/plugin-%{plugin_name}
%{__install} apacheconfig/%{gforge_name}-plugin-%{plugin_name}.conf %{buildroot}%{_datadir}/%{gforge_name}/config/skel/plugin-%{plugin_name}/
%{__sed} \
	-e "s|%NAME%|%{gforge_name}|g" \
	-e "s|%FRIENDLY_NAME%|%{gforge_friendly_name}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%PLUGIN_NAME%|%{plugin_name}|g" \
	-i %{buildroot}%{_datadir}/%{gforge_name}/config/skel/plugin-%{plugin_name}/%{gforge_name}-plugin-%{plugin_name}.conf

# Install /usr/share/gforge/plugins/mantis
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/plugins/%{plugin_name}
%{__cp} -a plugincore/* %{buildroot}%{_datadir}/%{gforge_name}/plugins/%{plugin_name}/

# Install /usr/share/gforge/www/plugins/mantis
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/www/plugins/%{plugin_name}
%{__install} pluginwww/* %{buildroot}%{_datadir}/%{gforge_name}/www/plugins/%{plugin_name}/

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

# Install /usr/share/gforge/override/www/pm
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/override/www/pm
%{__install} gforgewww/pm/*.php %{buildroot}%{_datadir}/%{gforge_name}/override/www/pm/

# Install /var/lib/gforge/config/plugin-mantis
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

%files
%defattr(-,root,root)
%doc LICENSE
%attr(0644,root,root) %{_sysconfdir}/cron.d/%{gforge_name}-plugin-%{plugin_name}
%verify(not md5 size mtime) %attr(0600,root,root) %{_sysconfdir}/httpd/conf.d/%{gforge_name}-plugin-%{plugin_name}.conf
%attr(0755,root,root) %{_datadir}/%{gforge_name}/config/scripts/config/plugin-%{plugin_name}
%attr(0755,root,root) %{_datadir}/%{gforge_name}/config/scripts/destroy/plugin-%{plugin_name}
%attr(0755,root,root) %{_datadir}/%{gforge_name}/config/scripts/remove/plugin-%{plugin_name}
%{_datadir}/%{gforge_name}/config/skel/plugin-%{plugin_name}
%{_datadir}/%{gforge_name}/override/www/pm/*.php
%{_datadir}/%{gforge_name}/override/plugins/%{plugin_name}
%{_datadir}/%{gforge_name}/override/www/plugins/%{plugin_name}
%{_datadir}/%{gforge_name}/plugins/%{plugin_name}
%{_datadir}/%{gforge_name}/www/plugins/%{plugin_name}
%{_localstatedir}/lib/%{gforge_name}/config/plugin-%{plugin_name}

%changelog
* Fri Oct 31 2008 Gregory Cuellar <gregory.cuellar@bull.net> 4.7.1-1.1
- Update to RHEL 5
- Requires gforge >= 4.7.1-1.1
- Requires gforge-plugin-apibull >= 1.11-2

* Tue Jul 08 2008 Gilles Menigot <gilles.menigot@bull.net> 2.8-1
- Update pluginwww/proxy to new constructor of class ProxyConfig
  of gforge-plugin-apibull
- Modify proxy to use a cookie prefix and a path
- Increase max URL length from 40 to 128 chars in siteAdmin.php
- Modify CSS regular expression in siteAdmin.php to match Mantis 1.1.1
- Requires gforge >= 4.5.11-30.1
- Requires gforge-plugin-apibull >= 1.11-1

* Thu Mar 27 2008 Gilles Menigot <gilles.menigot@bull.net> 2.7-1
- Add checkinMantisBugs() function in mantisfunctions.php
- Remove post-commit.sample file and /etc/gforge/plugins/mantis directory
- Correct require order of novaforge/auth.php in proxy
- Add license
- Correct name of cronjob not displayed in crontab manager

* Wed Feb 06 2008 Gilles Menigot <gilles.menigot@bull.net> 2.6-1
- Add "fill_cron_arr" hook in MantisPlugin.class
- Requires gforge >= 4.5.11-28.1
- Requires gforge-plugin-apibull >= 1.9-1

* Thu Jan 10 2008 Gilles Menigot <gilles.menigot@bull.net> 2.5-1
- Correct cookies badly transmitted from auth.php to command.php in
  callUrl() function (mantisfunctions.php)
- Requires gforge >= 4.5.11-27.1

* Thu Dec 06 2007 Gilles Menigot <gilles.menigot@bull.net> 2.4-1
- Remove preun scriptlet with removal script
- Cosmetic modifications in admin pages and language files
- Remove unused SQL field commit_default in plugin_mantis_project db

* Thu Nov 22 2007 Gilles Menigot <gilles.menigot@bull.net> 2.3-1
- Add text in site admin page to specify format of an instance URL
- Allow modification of an instance URL
- Correct CSS replacement patterns
- Requires gforge >= 4.5.11-24.1

* Fri Nov 16 2007 Gilles Menigot <gilles.menigot@bull.net> 2.2-1
- Check number of tables in config script
- Improve authentication

* Wed Nov 14 2007 Gilles Menigot <gilles.menigot@bull.net> 2.1-1
- Add GPL v2 license
- Requires getdist >= 1.2
- Requires gforge >= 4.5.11-23.1
- Requires gforge-plugin-apibull >= 1.7-1

* Wed Oct 10 2007 Gilles Menigot <gilles.menigot@bull.net> 2.0-1
- Full rewrite for multi-instances and security
- Requires gforge >= 4.5.11-21.1
- Requires gforge-plugin-apibull >= 1.6-1

* Tue Jun 26 2007 Gilles Menigot <gilles.menigot@bull.net> 1.10-1
- Correct synchronization of GForge superuser
- Remove unused code
- Move mantis.conf to skeleton
- Requires gforge >= 4.5.11-20.1

* Wed Jun 13 2007 Gilles Menigot <gilles.menigot@bull.net> 1.9-1
- Initial release
