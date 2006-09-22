%define plugin		cvstracker
%{!?release:%define release 2}

Summary: CVS Tracker Plugin for GForge CDE
Name: gforge-plugin-%{plugin}
Version: 4.1
Release: %{release}
BuildArch: noarch
License: GPL
Group: Development/Tools
Source0: %{name}-%{version}.tar.bz2
AutoReqProv: off
Requires: gforge >= 4.0
Requires: gforge-plugin-scmcvs
URL: http://www.gforge.org/
BuildRoot: %{_tmppath}/%{name}-%{version}-root

%define gfuser			gforge
%define gfgroup			gforge

%if "%{_vendor}" == "suse"
	%define httpduser		wwwrun
	%define httpdgroup		www
%else
	%define httpduser		apache
	%define httpdgroup		apache
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

This RPM installs CVS tracker plugin for GForge CDE which allows to link
cvs logs to trackers and tasks in GForge.

%prep
%setup

%build

%install
# cleaning build environment
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

# installing crontab
install -m 755 -d $RPM_BUILD_ROOT/%{CROND_DIR}/
install -m 644 rpm-specific/cron.d/%{name} $RPM_BUILD_ROOT/%{CROND_DIR}/

# copying all needed stuff to %{PLUGIN_LIB_DIR}
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_LIB_DIR}
for dir in bin include lib rpm-specific www; do
	cp -rp $dir $RPM_BUILD_ROOT/%{PLUGIN_LIB_DIR}/
done;
chmod 755 $RPM_BUILD_ROOT/%{PLUGIN_LIB_DIR}/bin/db-*.pl

# installing configuration file
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_CONF_DIR}
install -m 664 etc/plugins/%{plugin}/config.php $RPM_BUILD_ROOT/%{PLUGIN_CONF_DIR}/

%pre

%post
if [ "$1" = "1" ] ; then
	# register plugin in database
	%{GFORGE_BIN_DIR}/register-plugin %{plugin} "CVS Tracker" &> /dev/null
	
	su -l %{gfuser} -c "%{PLUGIN_LIB_DIR}/bin/db-upgrade.pl 2>&1" | grep -v ^NOTICE
	
	# we have to check the CVS version and change it in the config file
	CVS_VERSION=`cvs --version | grep 'Concurrent Versions System (CVS)' | sed -r 's/[a-z\(\) ]+ ([0-9]\.[0-9]{2})\.[0-9]{1,2}[a-z\(\)\/ ]+/\1/i'`
	sed -i "s/1\.12/$CVS_VERSION/" %{PLUGIN_CONF_DIR}/config.php
else
	# upgrade
	su -l %{gfuser} -c "%{PLUGIN_LIB_DIR}/bin/db-upgrade.pl 2>&1" | grep -v ^NOTICE
fi

%postun
if [ "$1" = "0" ] ; then
	# unregister plugin in database
	%{GFORGE_BIN_DIR}/unregister-plugin %{plugin}
else
	# upgrade
	:
fi

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
%doc AUTHORS COPYING README
%attr(0664, %{httpduser}, %{gfgroup}) %config(noreplace) %{PLUGIN_CONF_DIR}/config.php
%{PLUGIN_LIB_DIR}/bin
%{PLUGIN_LIB_DIR}/common
%{PLUGIN_LIB_DIR}/db
%{PLUGIN_LIB_DIR}/rpm-specific
%{PLUGIN_LIB_DIR}/www
%{CROND_DIR}/%{name}

%changelog
* Fri Jul 08 2005  Guillaume Smet <guillaume-gforge@smet.org>
- config.php is now 664 instead of 660
- we detect the version of CVS and we update the config file accordingly on installation
* Fri Apr 29 2005 Xavier Rameau <xrameau@gmail.com>
- Added support for SuSE
* Sat Mar 05 2005  Guillaume Smet <guillaume-gforge@smet.org>
Initial RPM packaging
