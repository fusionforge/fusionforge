%define plugin ldapextauth
%define pluginlibdir %{_libdir}/gforge/plugins/%{plugin}
%define pluginconfdir /etc/gforge/plugins/%{plugin}

%{!?release:%define release 1}

Summary: LDAP external authentication plugin for GForge CDE
Name: gforge-plugin-ldapextauth
Version: 4.0.1
Release: %{release}
BuildArch: noarch
License: GPL
Group: Development/Tools
Source0: %{name}-%{version}.tar.bz2
AutoReqProv: off
Requires: gforge >= 4.0
Requires: php-ldap
URL: http://www.gforge.org
BuildRoot: %{_tmppath}/%{name}-%{version}-root

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

# setting paths
PLUGIN_LIB_DIR=$RPM_BUILD_ROOT%{pluginlibdir}
PLUGIN_CONF_DIR=$RPM_BUILD_ROOT/%{pluginconfdir}

# copying all needed stuff to $PLUGIN_LIB_DIR
install -m 755 -d $PLUGIN_LIB_DIR
for dir in include; do
	cp -rp $dir ${PLUGIN_LIB_DIR}/
done;

# installing configuration file
install -m 755 -d ${PLUGIN_CONF_DIR}
install -m 664 etc/config.php $PLUGIN_CONF_DIR/
install -m 664 etc/mapping.php $PLUGIN_CONF_DIR/

%pre

%post
if [ "$1" = "1" ] ; then
	# register plugin in database
	%{_libdir}/gforge/bin/register-plugin %{plugin} "LDAP external authentication"
fi

%postun
if [ "$1" = "0" ] ; then
	# unregister plugin in database
	%{_libdir}/gforge/bin/unregister-plugin %{plugin}
fi

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
%doc README
%attr(0660, apache, gforge) %config(noreplace) %{pluginconfdir}/config.php
%attr(0660, apache, gforge) %config(noreplace) %{pluginconfdir}/mapping.php
%{pluginlibdir}/include

%changelog
* Fri Nov 26 2004  Open Wide <guillaume.smet@openwide.fr>
Initial RPM packaging
