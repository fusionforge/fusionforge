%define plugin cvstracker
%define pluginlibdir %{_libdir}/gforge/plugins/%{plugin}
%define pluginconfdir /etc/gforge/plugins/%{plugin}

%{!?release:%define release 1}

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

# setting paths
PLUGIN_LIB_DIR=$RPM_BUILD_ROOT%{pluginlibdir}
CONF_DIR=$RPM_BUILD_ROOT/etc
PLUGIN_CONF_DIR=$RPM_BUILD_ROOT/%{pluginconfdir}

# installing crontab
install -m 755 -d ${CONF_DIR}/cron.d
install -m 644 rpm-specific/cron.d/%{name} ${CONF_DIR}/cron.d/

# copying all needed stuff to $PLUGIN_LIB_DIR
install -m 755 -d $PLUGIN_LIB_DIR
for dir in bin include lib rpm-specific www; do
	cp -rp $dir ${PLUGIN_LIB_DIR}/
done;

chmod 755 ${PLUGIN_LIB_DIR}/bin/db-upgrade.pl
chmod 755 ${PLUGIN_LIB_DIR}/bin/db-delete.pl

# installing configuration file
install -m 755 -d ${PLUGIN_CONF_DIR}
install -m 664 etc/plugins/%{plugin}/config.php $PLUGIN_CONF_DIR/

%pre

%post
if [ "$1" = "1" ] ; then
	# register plugin in database
	%{_libdir}/gforge/bin/register-plugin %{plugin} "CVS Tracker" &> /dev/null
	
	su -l gforge -c "%{_libdir}/gforge/plugins/%{plugin}/bin/db-upgrade.pl 2>&1" | grep -v ^NOTICE
else
	# upgrade
	su -l gforge -c "%{_libdir}/gforge/plugins/%{plugin}/bin/db-upgrade.pl 2>&1" | grep -v ^NOTICE
fi

%postun
if [ "$1" = "0" ] ; then
	# unregister plugin in database
	%{_libdir}/gforge/bin/unregister-plugin %{plugin}
else
	# upgrade
	:
fi

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
%doc AUTHORS COPYING README
%attr(0664, apache, gforge) %config(noreplace) %{pluginconfdir}/config.php
%{pluginlibdir}/bin
%{pluginlibdir}/include
%{pluginlibdir}/lib
%{pluginlibdir}/rpm-specific
%{pluginlibdir}/www
/etc/cron.d/%{name}

%changelog
* Sat Mar 05 2005  Guillaume Smet <guillaume-gforge@smet.org>
Initial RPM packaging
