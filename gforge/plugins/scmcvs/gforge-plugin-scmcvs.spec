%define plugin		scmcvs
%{!?release:%define release 1}

Summary: CVS Plugin for GForge CDE
Name: gforge-plugin-%{plugin}
Version: 4.1
Release: %{release}
BuildArch: noarch
License: GPL
Group: Development/Tools
Source: %{name}-%{version}.tar.bz2
AutoReqProv: off
Requires: gforge >= 4.0
Requires: perl perl-URI
Requires: cvs rcs
URL: http://www.gforge.org/
BuildRoot: %{_tmppath}/%{name}-%{version}-root

%define	gfuser			gforge
%define gfgroup			gforge

%if "%{_vendor}" == "suse"
	%define httpduser		wwwrun
	%define httpdgroup		www
#Requires: perl-IPC-Run
%else
	%define httpduser		apache
	%define httpdgroup		apache
Requires: perl-IPC-Run
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
%define PLUGIN_LIB		%{PLUGINS_LIB_DIR}/%{plugin}
%define PLUGIN_CONF		%{PLUGINS_CONF_DIR}/%{plugin}


%description
GForge CDE is a web-based Collaborative Development Environment offering
easy access to CVS, mailing lists, bug tracking, message
boards/forums, task management, permanent file archival, and total
web-based administration.

This RPM installs SCM CVS plugin for GForge CDE which was previously bundled
with GForge CDE and provides CVS support to GForge CDE.

It also provides a specific version of CVSWeb wrapped in GForge CDE.

%prep
%setup

%build

%install
# cleaning build environment
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

# installing crontab
install -m 755 -d $RPM_BUILD_ROOT/%{CROND_DIR}/
install -m 644 rpm-specific/cron.d/%{name} $RPM_BUILD_ROOT/%{CROND_DIR}/

# copying all needed stuff to %{PLUGIN_LIB}
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_LIB}
for dir in bin include lib rpm-specific www; do
	cp -rp $dir $RPM_BUILD_ROOT/%{PLUGIN_LIB}/
done;
chmod 755 $RPM_BUILD_ROOT/%{PLUGIN_LIB}/bin/*

# installing CVSWeb cgi
install -m 755 -d $RPM_BUILD_ROOT/%{GFORGE_BIN_DIR}/
install -m 755 cgi-bin/cvsweb $RPM_BUILD_ROOT/%{GFORGE_BIN_DIR}/
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_LIB}/cgi-bin/
install -m 755 cgi-bin/cvsweb $RPM_BUILD_ROOT/%{PLUGIN_LIB}/cgi-bin/

# installing configuration file
install -m 755 -d  $RPM_BUILD_ROOT/%{GFORGE_CONF_DIR}/
cp -rp etc/* $RPM_BUILD_ROOT/%{GFORGE_CONF_DIR}/
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_CONF}
install -m 664 etc/plugins/%{plugin}/config.php $RPM_BUILD_ROOT/%{PLUGIN_CONF}/
install -m 664 etc/plugins/%{plugin}/cvsweb.conf $RPM_BUILD_ROOT/%{PLUGIN_CONF}/

# installing installation specific language files
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_CONF}/languages
if ls rpm-specific/languages/*.tab &> /dev/null; then
	cp rpm-specific/languages/*.tab $RPM_BUILD_ROOT/%{PLUGIN_CONF}/languages/
fi


%pre

%post
if [ "$1" = "1" ] ; then
	# register plugin in database
	%{GFORGE_BIN_DIR}/register-plugin %{plugin} CVS &> /dev/null
else
	# upgrade
	:
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
%attr(0660, %{httpduser}, %{gfgroup}) %config(noreplace) %{PLUGIN_CONF}/config.php
%attr(0660, %{httpduser}, %{gfgroup}) %config(noreplace) %{PLUGIN_CONF}/cvsweb.conf
%attr(0755,-,-) %{GFORGE_BIN_DIR}/cvsweb
%{GFORGE_CONF_DIR}/httpd.d
%{PLUGIN_CONF}/languages
%{PLUGIN_CONF}/config.pl
%{PLUGIN_LIB}/bin
%{PLUGIN_LIB}/cgi-bin
%{PLUGIN_LIB}/include
%{PLUGIN_LIB}/lib
%{PLUGIN_LIB}/rpm-specific
%{PLUGIN_LIB}/www
%{CROND_DIR}/%{name}

%changelog
* Fri Apr 29 2005 Xavier Rameau <xrameau@gmail.com>
- Added support for SuSE
* Sat Feb 19 2005 Guillaume Smet <guillaume-gforge@smet.org>
- 4.1
- replaced -f test with ls
- redirects register-plugin output to /dev/null
* Mon Jan 03 2005 Guillaume Smet <guillaume-gforge@smet.org>
- it's now possible to add specific language files in the RPM
* Sun Sep 26 2004  Guillaume Smet <guillaume-gforge@smet.org>
Initial RPM packaging
