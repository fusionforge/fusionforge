%define plugin          scmcvs
%{!?release:%define release 1}

Summary: CVS Plugin for GForge CDE
Name: gforge-plugin-%{plugin}
Version: 4.7
Release: %{release}
BuildArch: noarch
License: GPL
Group: Development/Tools
Source: %{name}-%{version}.tar.bz2
AutoReqProv: off
Requires: fusionforge >= 4.7
Requires: perl perl-URI
Requires: cvs >= 1.11
#update etc/plugins/scmcvs/config.php $cvs_binary_version before updating cvs to 1.12
Requires: cvs < 1.12
Requires: rcs
URL: http://www.gforge.org/
BuildRoot: %{_tmppath}/%{name}-%{version}-root

%define gfuser                  gforge
%define gfgroup                 gforge

%if "%{_vendor}" == "suse"
        %define httpduser               wwwrun
        %define httpdgroup              www
#Requires: perl-IPC-Run
%else
        %define httpduser               apache
        %define httpdgroup              apache
Requires: perl-IPC-Run
%endif

#Globals defines for gforge
%define GFORGE_DIR              %{_datadir}/gforge
%define GFORGE_CONF_DIR         %{_sysconfdir}/gforge
%define GFORGE_SBIN_DIR         %{_sbindir}
#%define GFORGE_LIB_DIR          %{_libdir}/gforge/lib
#%define GFORGE_DB_DIR           %{_libdir}/gforge/db
%define GFORGE_BIN_DIR          %{_libdir}/gforge/bin
%define PLUGINS_LIB_DIR         %{_libdir}/gforge/plugins
%define PLUGINS_CONF_DIR        %{GFORGE_CONF_DIR}/plugins
%define CROND_DIR               %{_sysconfdir}/cron.d
%define SBIN_DIR				%{_sbindir}

#specific define for plugins
%define PLUGIN_LIB              %{PLUGINS_LIB_DIR}/%{plugin}
%define PLUGIN_CONF             %{PLUGINS_CONF_DIR}/%{plugin}


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
#install -m 644 rpm-specific/cron.d/%{name} $RPM_BUILD_ROOT/%{CROND_DIR}/

# copying all needed stuff to %{PLUGIN_LIB}
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_LIB}
for dir in bin common sbin www cronjobs; do
        cp -rp $dir $RPM_BUILD_ROOT/%{PLUGIN_LIB}/
done;
chmod 755 $RPM_BUILD_ROOT/%{PLUGIN_LIB}/bin/*
chmod 755 $RPM_BUILD_ROOT/%{PLUGIN_LIB}/sbin/*
chmod 755 $RPM_BUILD_ROOT/%{PLUGIN_LIB}/cronjobs/cvscreate.sh

# installing CVSWeb cgi
#install -m 755 -d $RPM_BUILD_ROOT/%{GFORGE_BIN_DIR}/
#install -m 755 cgi-bin/cvsweb $RPM_BUILD_ROOT/%{GFORGE_BIN_DIR}/
#install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_LIB}/cgi-bin/
#install -m 755 cgi-bin/cvsweb $RPM_BUILD_ROOT/%{PLUGIN_LIB}/cgi-bin/

# installing configuration file
install -m 755 -d  $RPM_BUILD_ROOT/%{GFORGE_CONF_DIR}/
cp -rp etc/* $RPM_BUILD_ROOT/%{GFORGE_CONF_DIR}/
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_CONF}
install -m 664 etc/plugins/%{plugin}/config.php $RPM_BUILD_ROOT/%{PLUGIN_CONF}/
install -m 664 etc/plugins/%{plugin}/cvsweb.conf $RPM_BUILD_ROOT/%{PLUGIN_CONF}/

# installing installation specific language files
#install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_CONF}/languages
#if ls rpm-specific/languages/*.tab &> /dev/null; then
#       cp rpm-specific/languages/*.tab $RPM_BUILD_ROOT/%{PLUGIN_CONF}/languages/
#fi


%pre

%post
if [ "$1" = "1" ] ; then
		#if not the env.inc.php include-path isn't correct
		ln -s /usr/lib/gforge/plugins/ /usr/share/gforge/plugins
		
		[ ! -f /bin/cvssh ] && ln -s %{PLUGIN_LIB}/bin/cvssh.pl /bin/cvssh

        #GF_DOMAIN=$(grep ^domain_name= %{GFORGE_CONF_DIR}/gforge.conf | cut -d= -f2-)
        #perl -pi -e "
        #        s#^\\\$sys_plugins_path=.*#\\\$sys_plugins_path='"%{PLUGINS_LIB_DIR}"';#;
        #        s#^\\\$sys_default_domain=.*#\\\$sys_default_domain='$GF_DOMAIN';#" %{PLUGIN_CONF}/config.php

        # register plugin in database
        %{GFORGE_BIN_DIR}/register-plugin %{plugin} CVS &> /dev/null
        
        perl -pi -e "
		s/sys_use_scm=false/sys_use_scm=true/g" %{GFORGE_CONF_DIR}/gforge.conf
		
		# initializing configuration
		%{SBIN_DIR}/gforge-config
		
		chroot=`grep '^gforge_chroot:' /etc/gforge/gforge.conf | sed 's/.*:\s*\(.*\)/\1/'`
 		if [ ! -d /var/lib/gforge/chroot/cvsroot/ ] ; then
			mkdir -p /var/lib/gforge/chroot/cvsroot/
		fi
		ln -s /var/lib/gforge/chroot/cvsroot /cvsroot
else
        # upgrade
        :
fi

%postun
if [ "$1" = "0" ] ; then
        # unregister plugin in database
        %{GFORGE_BIN_DIR}/unregister-plugin %{plugin}
        
        [ -L /bin/cvssh ] && rm -f /bin/cvssh
else
        # upgrade
        :
fi

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
%doc AUTHORS COPYING README
%attr(0664, %{httpduser}, %{gfgroup}) %config(noreplace) %{PLUGIN_CONF}/config.php
%attr(0660, %{httpduser}, %{gfgroup}) %config(noreplace) %{PLUGIN_CONF}/cvsweb.conf
#%attr(0755,root,root) %{GFORGE_BIN_DIR}/cvsweb
%{GFORGE_CONF_DIR}/httpd.d
#%{PLUGIN_CONF}/languages
%{PLUGIN_CONF}/config.pl
%{PLUGIN_LIB}/bin
%{PLUGIN_LIB}/common
%{PLUGIN_LIB}/sbin
#%{PLUGIN_LIB}/cgi-bin
#%{PLUGIN_LIB}/include
#%{PLUGIN_LIB}/lib
#%{PLUGIN_LIB}/rpm-specific
%{PLUGIN_LIB}/www
%{PLUGIN_LIB}/cronjobs
#%{CROND_DIR}/%{name}

%changelog
* Mon Jan 09 2006 Nicolas Quienot <nquienot@linagora.com>
- 4.5.6
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
