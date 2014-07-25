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

%define FORGE_DIR       %{_datadir}/gforge
%define FORGE_CONF_DIR  %{_sysconfdir}/gforge
%define FORGE_LANG_DIR  %{_datadir}/locale
%define FORGE_DATA_PATH %{_var}/lib/gforge
%define FORGE_CHROOT_PATH %{FORGE_DATA_PATH}/chroot
%define FORGE_PLUGINS_LIB_DIR       %{FORGE_DIR}/plugins
%define FORGE_PLUGINS_CONF_DIR        %{FORGE_CONF_DIR}/plugins

# If that works, then a better way would be the following:
# %define FORGE_DIR       %(utils/forge_get_config_basic fhsrh source_path)
# %define FORGE_CONF_DIR  %(utils/forge_get_config_basic fhsrh config_path)
# %define FORGE_LANG_DIR  %{_datadir}/locale
# %define FORGE_DATA_PATH   %(utils/forge_get_config_basic fhsrh data_path)
# %define FORGE_CHROOT_PATH   %(utils/forge_get_config_basic fhsrh chroot)
# %define FORGE_PLUGINS_LIB_DIR         %(utils/forge_get_config_basic fhsrh plugins_path)
# %define FORGE_PLUGINS_CONF_DIR        %{FORGE_CONF_DIR}/plugins

#specific define for plugins
%define FORGE_PLUGIN_LIB              %{FORGE_PLUGINS_LIB_DIR}/%{plugin}
%define FORGE_PLUGIN_CONF             %{FORGE_PLUGINS_CONF_DIR}/%{plugin}
%define FORGE_PLUGIN_DUMP				%{FORGE_VAR_LIB}/dumps

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

# copying all needed stuff to %{FORGE_PLUGIN_LIB}
install -m 755 -d $RPM_BUILD_ROOT/%{FORGE_PLUGIN_LIB}
for dir in bin common; do
        cp -rp $dir $RPM_BUILD_ROOT/%{FORGE_PLUGIN_LIB}/
done;
#chmod 755 $RPM_BUILD_ROOT/%{FORGE_PLUGIN_LIB}/bin/*

# installing configuration file
install -m 755 -d  $RPM_BUILD_ROOT/%{FORGE_CONF_DIR}/
install -m 755 -d $RPM_BUILD_ROOT/%{FORGE_PLUGIN_CONF}
cp -rp etc/plugins/%{plugin}/* $RPM_BUILD_ROOT/%{FORGE_PLUGIN_CONF}/

# installing dumps repository
install -m 755 -d $RPM_BUILD_ROOT/%{FORGE_PLUGIN_DUMP}

%pre

%post
if [ "$1" = "1" ] ; then
	# link the plugin www rep to be accessed by web
	#ln -s %{FORGE_PLUGIN_LIB}/www %{FORGE_DIR}/www/plugins/%{plugin}
        
    # register plugin in database
    %{FORGE_BIN_DIR}/register-plugin %{plugin} SVN &> /dev/null
    
    perl -pi -e "
	s/sys_use_scm=false/sys_use_scm=true/g" %{FORGE_CONF_DIR}/gforge.conf
		
	CHROOT=`grep '^gforge_chroot=' %{FORGE_CONF_DIR}/gforge.conf | sed 's/.*=\s*\(.*\)/\1/'`
	if [ ! -d $CHROOT/svnroot ] ; then
		mkdir -p $CHROOT/svnroot
	fi
	ln -s $CHROOT/svnroot /svnroot
	
	#configuration svn
	%{FORGE_PLUGIN_LIB}/bin/install-svn.sh configure

else
        # upgrade
        :
fi

%postun
if [ "$1" = "0" ] ; then
        # unregister plugin in database
        %{FORGE_BIN_DIR}/unregister-plugin %{plugin}
        
else
        # upgrade
        :
fi

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
%doc README
%{FORGE_PLUGIN_CONF}
%attr(0750, root, root) %{FORGE_PLUGIN_LIB}/bin/install-svn.sh
%{FORGE_PLUGIN_LIB}/common
%attr(0744, gforge, gforge) %{FORGE_PLUGIN_DUMP}

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
