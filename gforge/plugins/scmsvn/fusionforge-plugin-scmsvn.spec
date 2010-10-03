%define plugin          scmsvn
%{!?release:%define release 1}

Summary: collaborative development tool - Subversion plugin
Name: fusionforge-plugin-%{plugin}
Version: 4.8
Release: %{release}
BuildArch: noarch
License: GPL
Group: Development/Tools
Source: %{name}-%{version}.tar.bz2
AutoReqProv: off

Requires: fusionforge >= 4.7
#Requires: perl perl-URI
Requires: subversion
Requires: python >= 2.3
Requires: xinetd

URL: http://fusionforge.org/
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

#Globals defines for fusionforge
%define FFORGE_DIR              %{_datadir}/gforge
%define FFORGE_CONF_DIR         %{_sysconfdir}/gforge
%define FFORGE_SBIN_DIR         %{_sbindir}
%define FFORGE_BIN_DIR          %{FFORGE_DIR}/bin
%define PLUGINS_LIB_DIR         %{FFORGE_DIR}/plugins
%define PLUGINS_CONF_DIR        %{FFORGE_CONF_DIR}/plugins
%define SBIN_DIR				%{_sbindir}

#specific define for plugins
%define PLUGIN_LIB              %{PLUGINS_LIB_DIR}/%{plugin}
%define PLUGIN_CONF             %{PLUGINS_CONF_DIR}/%{plugin}
%define PLUGIN_DUMP				/var/lib/gforge/dumps


%description
This plugin contains the Subversion subsystem of FusionForge. It allows
each FusionForge project to have its own Subversion repository, and gives
some control over it to the project's administrator.

%prep
%setup

%build

%install
# cleaning build environment
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

# copying all needed stuff to %{PLUGIN_LIB}
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_LIB}
for dir in bin common; do
        cp -rp $dir $RPM_BUILD_ROOT/%{PLUGIN_LIB}/
done;
#chmod 755 $RPM_BUILD_ROOT/%{PLUGIN_LIB}/bin/*

# installing configuration file
install -m 755 -d  $RPM_BUILD_ROOT/%{FFORGE_CONF_DIR}/
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_CONF}
cp -rp etc/plugins/%{plugin}/* $RPM_BUILD_ROOT/%{PLUGIN_CONF}/

# installing dumps repository
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGIN_DUMP}

%pre

%post
if [ "$1" = "1" ] ; then
	# link the plugin www rep to be accessed by web
	#ln -s %{PLUGIN_LIB}/www %{FFORGE_DIR}/www/plugins/%{plugin}
        
    # register plugin in database
    %{FFORGE_BIN_DIR}/register-plugin %{plugin} SVN &> /dev/null
    
    perl -pi -e "
	s/sys_use_scm=false/sys_use_scm=true/g" %{FFORGE_CONF_DIR}/gforge.conf
		
	# initializing configuration
	%{SBIN_DIR}/fusionforge-config
	
	CHROOT=`grep '^gforge_chroot=' %{FFORGE_CONF_DIR}/gforge.conf | sed 's/.*=\s*\(.*\)/\1/'`
	if [ ! -d $CHROOT/svnroot ] ; then
		mkdir -p $CHROOT/svnroot
	fi
	ln -s $CHROOT/svnroot /svnroot
	
	#configuration svn
	/usr/share/gforge/plugins/scmsvn/bin/install-svn.sh configure

else
        # upgrade
        :
fi

%postun
if [ "$1" = "0" ] ; then
        # unregister plugin in database
        %{FFORGE_BIN_DIR}/unregister-plugin %{plugin}
        
else
        # upgrade
        :
fi

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
%doc README
%{PLUGIN_CONF}
%attr(0750, root, root) %{PLUGIN_LIB}/bin/install-svn.sh
%{PLUGIN_LIB}/common
%attr(0744, gforge, gforge) %{PLUGIN_DUMP}

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
