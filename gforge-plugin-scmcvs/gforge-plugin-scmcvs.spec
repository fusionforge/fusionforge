%define plugin scmcvs
%define pluginlibdir %{_libdir}/gforge/plugins/%{plugin}
%define pluginconfdir /etc/gforge/plugins/%{plugin}

%{!?release:%define release 1}

Summary: CVS Plugin for GForge CDE
Name: gforge-plugin-scmcvs
Version: 4.0.1
Release: %{release}
BuildArch: noarch
License: GPL
Group: Development/Tools
Source0: %{name}-%{version}.tar.gz
AutoReqProv: off
Requires: gforge >= 4.0
Requires: perl perl-IPC-Run perl-URI
Requires: cvs rcs
URL: http://www.gforge.org/
BuildRoot: %{_tmppath}/%{name}-%{version}-root

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

# setting paths
PLUGIN_LIB_DIR=$RPM_BUILD_ROOT%{pluginlibdir}
CONF_DIR=$RPM_BUILD_ROOT/etc
PLUGIN_CONF_DIR=$RPM_BUILD_ROOT/%{pluginconfdir}

# installing crontab
install -m 755 -d ${CONF_DIR}/cron.d
install -m 644 rpm-specific/cron.d/%{name} ${CONF_DIR}/cron.d/

# copying all needed stuff to $PLUGIN_LIB_DIR
install -m 755 -d $PLUGIN_LIB_DIR
for dir in cgi-bin cronjobs include lib  rpm-specific www; do
	cp -rp $dir ${PLUGIN_LIB_DIR}/
done;

# installing CVSWeb cgi
install -m 755 cgi-bin/cvsweb ${PLUGIN_LIB_DIR}/cgi-bin/

# installing configuration file
install -m 755 -d ${PLUGIN_CONF_DIR}
install -m 664 etc/plugins/%{plugin}/config.php $PLUGIN_CONF_DIR/
install -m 664 etc/plugins/%{plugin}/cvsweb.conf $PLUGIN_CONF_DIR/

%pre

%post
if [ "$1" = "1" ] ; then
	# register plugin in database
	%{_libdir}/gforge/bin/register-plugin scmcvs CVS
fi

%postun
if [ "$1" = "0" ] ; then
	# unregister plugin in database
	%{_libdir}/gforge/bin/unregister-plugin scmcvs
fi

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
%doc AUTHORS COPYING README
%attr(0660, apache, gforge) %config(noreplace) %{pluginconfdir}/config.php
%attr(0660, apache, gforge) %config(noreplace) %{pluginconfdir}/cvsweb.conf
%{pluginlibdir}/cgi-bin
%{pluginlibdir}/cronjobs
%{pluginlibdir}/include
%{pluginlibdir}/lib
%{pluginlibdir}/rpm-specific
%{pluginlibdir}/www
/etc/cron.d/%{name}

%changelog
* Sun Sep 26 2004  Guillaume Smet <guillaume-gforge@smet.org>
Initial RPM packaging
