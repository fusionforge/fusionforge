%define plugin		authldap
%{!?release:%define release 1}

Summary: LDAP external authentication plugin for GForge CDE
Name: gforge-plugin-%{plugin}
Version: 4.1
Release: %{release}
BuildArch: noarch
License: GPL
Group: Development/Tools
Source0: %{name}-%{version}.tar.bz2
AutoReqProv: off
Requires: gforge >= 4.0
URL: http://www.gforge.org
BuildRoot: %{_tmppath}/%{name}-%{version}-root

%define gfuser			gforge
%define gfgroup			gforge

%if "%{_vendor}" == "suse"
	%define httpduser		wwwrun
	%define httpdgroup		www
Requires: php5-ldap
%else
	%define httpduser		apache
	%define httpdgroup		apache
Requires: php-ldap
%endif

#Globals defines for gforge
%define GFORGE_DIR		%{_datadir}/gforge
%define GFORGE_CONF_DIR		%{_sysconfdir}/gforge
%define GFORGE_LANG_DIR		%{GFORGE_CONF_DIR}/languages-local
%define GFORGE_SBIN_DIR		%{_sbindir}
%define GFORGE_LIB_DIR		%{_libdir}/gforge/lib
%define GFORGE_DB_DIR		%{_libdir}/gforge/db
%define GFORGE_BIN_DIR		%{_libdir}/gforge/bin
%define PLUGINS_LIB_DIR		%{_libdir}/gforge/plugins
%define PLUGINS_CONF_DIR	%{GFORGE_CONF_DIR}/plugins
%define CROND_DIR		%{_sysconfdir}/cron.d

#specific define for plugins
%define PLUGIN_LIB_DIR		%{PLUGINS_LIB_DIR}/%{plugin}
%define PLUGIN_CONF_DIR		%{PLUGINS_CONF_DIR}/%{plugin}

%description
GForge CDE is a web-based Collaborative Development Environment offering
easy access to CVS, mailing lists, bug tracking, message
boards/forums, task management, permanent file archival, and total
web-based administration.

This RPM installs LDAP external authentication plugin for GForge CDE.

%prep
%setup

%build

%install
# cleaning build environment
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

# copying all needed stuff to %{PLUGIN_LIB_DIR}
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_LIB_DIR}
for dir in bin include rpm-specific ; do
        cp -rp $dir $RPM_BUILD_ROOT/%{PLUGIN_LIB_DIR}/
done;
chmod 755 $RPM_BUILD_ROOT/%{PLUGIN_LIB_DIR}/bin/db-*.pl

# installing configuration file
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_CONF_DIR}
cp -p etc/plugins/%{plugin}/* $RPM_BUILD_ROOT/%{PLUGIN_CONF_DIR}/

%pre

%post
if [ "$1" = "1" ] ; then
	# register plugin in database
	%{GFORGE_BIN_DIR}/register-plugin %{plugin} "LDAP external authentication" &> /dev/null
	# su -l %{gfuser} -c "%{PLUGIN_LIB_DIR}/bin/db-upgrade.pl 2>&1" | grep -v ^NOTICE
else
	# upgrade
	#su -l %{gfuser} -c "%{PLUGIN_LIB_DIR}/bin/db-upgrade.pl 2>&1" | grep -v ^NOTICE
	:
fi

%postun
if [ "$1" = "0" ] ; then
	# unregister plugin in database
	%{GFORGE_BIN_DIR}/unregister-plugin %{plugin}
	#su -l %{gfuser} -c "%{PLUGIN_LIB_DIR}/bin/db-delete.pl 2>&1" | grep -v ^NOTICE
else
	# upgrade
	:
fi

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
%doc README
%attr(0660, %{httpduser}, %{gfgroup}) %config(noreplace) %{PLUGIN_CONF_DIR}/config.php
%attr(0660, %{httpduser}, %{gfgroup}) %config(noreplace) %{PLUGIN_CONF_DIR}/mapping.php
%{PLUGIN_LIB_DIR}/bin
%{PLUGIN_LIB_DIR}/include
%{PLUGIN_LIB_DIR}/rpm-specific

%changelog
* Fri Apr 29 2005 Xavier Rameau <xrameau@gmail.com>
- Added support for SuSE
* Thu Mar 03 2005 Guillaume Smet <guillaume-gforge@smet.org>
- config files have moved
* Sat Feb 19 2005 Guillaume Smet <guillaume-gforge@smet.org>
- 4.1
- redirects register-plugin output to /dev/null
* Fri Nov 26 2004  Dassault Aviation <guillaume.smet@openwide.fr>
Initial RPM packaging
