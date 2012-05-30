%define plugin          scmcvs
%{!?release:%define release 1}

Summary: CVS Plugin for FusionForge
Name: fusionforge-plugin-%{plugin}
Version: 4.8.2
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
%define GFORGE_BIN_DIR          %{GFORGE_DIR}/bin
%define PLUGINS_LIB_DIR         %{GFORGE_DIR}/plugins
%define PLUGINS_CONF_DIR        %{GFORGE_CONF_DIR}/plugins
%define CROND_DIR               %{_sysconfdir}/cron.d
%define SBIN_DIR				%{_sbindir}

#specific define for plugins
%define PLUGIN_LIB              %{PLUGINS_LIB_DIR}/%{plugin}
%define PLUGIN_CONF             %{PLUGINS_CONF_DIR}/%{plugin}


%description
FusionForge is a web-based Collaborative Development Environment offering
easy access to CVS, mailing lists, bug tracking, message
boards/forums, task management, permanent file archival, and total
web-based administration.

This RPM installs SCM CVS plugin for FusionForge and provides CVS support
to FusionForge.

It also provides a specific version of CVSWeb wrapped in FusionForge.

%prep
%setup

%build

%install
# cleaning build environment
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

# installing crontab
install -m 755 -d $RPM_BUILD_ROOT/%{CROND_DIR}/
install -m 644 cron.d/%{name} $RPM_BUILD_ROOT/%{CROND_DIR}/

# copying all needed stuff to %{PLUGIN_LIB}
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_LIB}
for dir in bin common www cronjobs; do
        cp -rp $dir $RPM_BUILD_ROOT/%{PLUGIN_LIB}/
done;
chmod 755 $RPM_BUILD_ROOT/%{PLUGIN_LIB}/bin/*
chmod 755 $RPM_BUILD_ROOT/%{PLUGIN_LIB}/cronjobs/cvscreate.sh

# installing executable for pserver
install -m 755 -d $RPM_BUILD_ROOT/%{GFORGE_BIN_DIR}/
cp -rp sbin/cvs-pserver $RPM_BUILD_ROOT/%{GFORGE_BIN_DIR}/
chmod 755 $RPM_BUILD_ROOT/%{GFORGE_BIN_DIR}/cvs-pserver

# installing configuration file
install -m 755 -d  $RPM_BUILD_ROOT/%{GFORGE_CONF_DIR}/
cp -rp etc/* $RPM_BUILD_ROOT/%{GFORGE_CONF_DIR}/
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_CONF}
install -m 664 etc/plugins/%{plugin}/config.php $RPM_BUILD_ROOT/%{PLUGIN_CONF}/
install -m 664 etc/plugins/%{plugin}/cvsweb.conf $RPM_BUILD_ROOT/%{PLUGIN_CONF}/


%pre

%post
if [ "$1" = "1" ] ; then
	# link the plugin www rep to be accessed by web
	ln -s %{PLUGIN_LIB}/www %{GFORGE_DIR}/www/plugins/%{plugin}

	[ ! -f /bin/cvssh ] && ln -s %{PLUGIN_LIB}/bin/cvssh.pl /bin/cvssh

        #GF_DOMAIN=$(grep ^domain_name= %{GFORGE_CONF_DIR}/gforge.conf | cut -d= -f2-)
        #perl -pi -e "
        #        s#^\\\$sys_plugins_path=.*#\\\$sys_plugins_path='"%{PLUGINS_LIB_DIR}"';#;
        #        s#^\\\$sys_default_domain=.*#\\\$sys_default_domain='$GF_DOMAIN';#" %{PLUGIN_CONF}/config.php

        # register plugin in database
        %{GFORGE_BIN_DIR}/register-plugin %{plugin} CVS &> /dev/null
        
        perl -pi -e "
		s/sys_use_scm=false/sys_use_scm=true/g" %{GFORGE_CONF_DIR}/gforge.conf
		
	CHROOT=`grep '^gforge_chroot=' %{GFORGE_CONF_DIR}/gforge.conf | sed 's/.*=\s*\(.*\)/\1/'`
	if [ ! -d $CHROOT/cvsroot ] ; then
		mkdir -p $CHROOT/cvsroot
	fi
	ln -s $CHROOT/cvsroot /cvsroot

	#if sys_account_manager_type=pgsql, comment the cron usergroup.php
	SYS_ACCOUNT_MANAGER_TYPE=`grep '^sys_account_manager_type=' %{GFORGE_CONF_DIR}/gforge.conf | sed 's/.*=\s*\(.*\)/\1/'`
	if [ $SYS_ACCOUNT_MANAGER_TYPE = "pgsql" ]; then
		#echo "plugin scmcvs installed"
		if [ "$(grep 'usergroup.php' %{CROND_DIR}/fusionforge-plugin-scmcvs | grep '#')" = "" ]; then
			#echo "I comment the cron if it is un comment"
			sed -i "s/^\(.*usergroup.php.*\)/#\1/" %{CROND_DIR}/fusionforge-plugin-scmcvs
		fi
	fi
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
%{GFORGE_CONF_DIR}/httpd.d
%{PLUGIN_CONF}/config.pl
%{PLUGIN_LIB}/bin
%{PLUGIN_LIB}/common
%{PLUGIN_LIB}/www
%{PLUGIN_LIB}/cronjobs
%{CROND_DIR}/%{name}
%{GFORGE_BIN_DIR}/cvs-pserver

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
