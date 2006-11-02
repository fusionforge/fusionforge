%define plugin		scmsvn
%{!?release:%define release 2}

Summary: SVN Plugin for GForge CDE
Name: gforge-plugin-%{plugin}
Version: 4.5.14
Release: %{release}
BuildArch: noarch
License: GPL
Group: Development/Tools
Source: %{name}-%{version}.tar.bz2
Patch0: gforge-plugin-scmsvn-4.5.14-nogroupdircheck.patch
Patch1: gforge-plugin-scmsvn-4.5.14-hotbackup.py-path.patch
Patch2: gforge-plugin-scmsvn-4.5.14-svn_dav_authz.patch
Patch3: gforge-plugin-scmsvn-4.5.14-viewcvs.cgi.patch
Patch4: gforge-plugin-scmsvn-4.5.14-svn-host.patch
Patch5: gforge-plugin-scmsvn-4.5.14-styles.css.patch
Patch6: gforge-plugin-scmsvn-4.5.14-templates.patch
Patch7: gforge-plugin-scmsvn-4.5.14-viewcvs.php.patch
AutoReqProv: off
Requires: gforge >= 4.5
Requires: subversion = 1.3.2
Requires: mod_dav_svn = 1.3.2
Requires: viewcvs
URL: http://www.gforge.org/
BuildRoot: %{_tmppath}/%{name}-%{version}-root

%define	gfuser			gforge
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
%define PLUGINS_WWW_DIR		%{GFORGE_DIR}/www/plugins
%define PLUGINS_LIB_DIR		%{_libdir}/gforge/plugins
%define PLUGINS_CONF_DIR	%{GFORGE_CONF_DIR}/plugins
%define CROND_DIR		%{_sysconfdir}/cron.d

#specific define for plugins
%define PLUGIN_WWW		%{PLUGINS_WWW_DIR}/%{plugin}
%define PLUGIN_LIB		%{PLUGINS_LIB_DIR}/%{plugin}
%define PLUGIN_CONF		%{PLUGINS_CONF_DIR}/%{plugin}


%description
GForge CDE is a web-based Collaborative Development Environment offering
easy access to CVS, mailing lists, bug tracking, message
boards/forums, task management, permanent file archival, and total
web-based administration.

This RPM installs SCM SVN plugin for GForge CDE which provides SVN
support to GForge CDE.

%prep
%setup
%patch0 -p0
%patch1 -p0
%patch2 -p0
%patch3 -p0
%patch4 -p0
#%patch5 -p0
%patch6 -p0
%patch7 -p0

%build

%install
# cleaning build environment
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

# installing crontab
install -m 755 -d $RPM_BUILD_ROOT/%{CROND_DIR}/
install -m 644 rpm-specific/cron.d/%{name} $RPM_BUILD_ROOT/%{CROND_DIR}/

# copying all needed stuff to %{PLUGIN_LIB}
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_LIB}
for dir in bin cronjobs include lib rpm-specific; do
	cp -rp $dir $RPM_BUILD_ROOT/%{PLUGIN_LIB}/
done;
chmod 755 $RPM_BUILD_ROOT/%{PLUGIN_LIB}/bin/*

install -m 644 rpm-specific/scripts/create_authz_svn.php $RPM_BUILD_ROOT/%{PLUGIN_LIB}/cronjobs/

# copying all needed stuff to %{PLUGIN_WWW}
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_WWW}/
cp -rp www/* $RPM_BUILD_ROOT/%{PLUGIN_WWW}/
cp -rp rpm-specific/viewcvs/* $RPM_BUILD_ROOT/%{PLUGIN_WWW}/viewcvs/

# installing ViewCVS cgi
install -m 755 -d $RPM_BUILD_ROOT/%{GFORGE_BIN_DIR}/
install -m 755 cgi-bin/viewcvs.cgi $RPM_BUILD_ROOT/%{GFORGE_BIN_DIR}/
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_LIB}/cgi-bin/
install -m 755 cgi-bin/viewcvs.cgi $RPM_BUILD_ROOT/%{PLUGIN_LIB}/cgi-bin/

# installing configuration file
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_CONF}
cp -rp etc/plugins/%{plugin}/* $RPM_BUILD_ROOT/%{PLUGIN_CONF}/

# installing installation specific language files
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_CONF}/languages
if ls rpm-specific/languages/*.tab &> /dev/null; then
	cp rpm-specific/languages/*.tab $RPM_BUILD_ROOT/%{PLUGIN_CONF}/languages/
fi


%pre

%post
if [ "$1" = "1" ] ; then
	# register plugin in database
	%{GFORGE_BIN_DIR}/register-plugin %{plugin} Subversion &> /dev/null
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
%doc README
%attr(0660, %{httpduser}, %{gfgroup}) %config(noreplace) %{PLUGIN_CONF}/config.php
%attr(0755,-,-) %{GFORGE_BIN_DIR}/viewcvs.cgi
%{PLUGIN_WWW}
%{PLUGIN_CONF}/config.pl
%{PLUGIN_CONF}/viewcvs
%{PLUGIN_LIB}/bin
%{PLUGIN_LIB}/cronjobs
%{PLUGIN_LIB}/cgi-bin
%{PLUGIN_LIB}/include
%{PLUGIN_LIB}/lib
%{PLUGIN_LIB}/rpm-specific
%{CROND_DIR}/%{name}

%changelog
* Mon Oct 30 2006  Open Wide <guillaume.smet@openwide.fr>
Updated packaging to use Dag packages and mod_dav_svn
* Fri Oct 13 2006  Open Wide <guillaume.smet@openwide.fr>
Initial RPM packaging
