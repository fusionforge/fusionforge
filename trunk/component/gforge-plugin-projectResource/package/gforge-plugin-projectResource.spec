# Identify the distribution
%define dist %(test -x %{_bindir}/getdist && %{_bindir}/getdist || echo unknown)
%define unsupported_dist 1
%if %{dist} == "rhel3"
%define unsupported_dist 0
%endif
%if %{dist} == "rhel4"
%define unsupported_dist 0
%endif
%if %{dist} == "rhel5"
%define unsupported_dist 0
%endif
%if %{dist} == "aurora2"
%define unsupported_dist 0
%endif

# Constants related to this RPM
%define plugin_name projectResource
%define plugin_rpm_name projectResource

# Constants related to other RPMs we provide
%define getdist_version 1.1
%define gforge_friendly_name NovaForge
%define gforge_name gforge
%define gforge_release 19.1
%define gforge_version 4.5.11

# Constants related to the distribution
%define apache_group apache
%define apache_user apache
%if %{dist} == "rhel3"
%define sed_version 4.0.7
%endif
%if %{dist} == "rhel4"
%define sed_version 4.1.2
%endif
%if %{dist} == "rhel5"
%define sed_version 4.1.5
%endif
%if %{dist} == "aurora2"
%define sed_version 4.1.2
%endif
%if %{unsupported_dist} == 1
%define sed_version 999
%endif

# Sources and patches
Source0:	%{gforge_name}-plugin-%{plugin_name}-%{version}.tar.gz

# Packages required for build
BuildRequires:	getdist >= %{getdist_version}
BuildRequires:	sed >= %{sed_version}

# Build architecture
BuildArch:	noarch

# Build root
BuildRoot:	%{_tmppath}/%{gforge_name}-plugin-%{plugin_name}-%{version}-%{release}-buildroot

#
# Main package
#

Summary:	Outils communs projets pour Novaforge
Name:		%{gforge_name}-plugin-%{plugin_rpm_name}
Version:	1.2
Release:	1.%{dist}
License:	GPL
Group:		Applications/Internet
URL:		http://novaforge.frec.bull.fr/projects/novaforge/
Conflicts:	gforge-commonproject
Requires:	getdist >= %{getdist_version}
Requires:	%{gforge_name} >= %{gforge_version}-%{gforge_release}

%description
Plugin Outils Communs : Outils communs projets pour Novaforge

%prep
if [ "%{unsupported_dist}" = "1" ] ; then
	cat <<ENDTEXT
ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR

The Linux distribution of this system is '%{dist}'.
This package can be built on the following distributions:
- Red Hat Enterprise Linux 3 or CentOS 3 (rhel3)
- Red Hat Enterprise Linux 4 or CentOS 4 (rhel4)
- Aurora SPARC Linux 2.0 (aurora2)

ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR
ENDTEXT
	exit 1
fi
%setup -q 

%build

%install
# Clean
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && rm -rf %{buildroot}

# /etc/gforge/plugins/projectResource
%{__install} -d %{buildroot}%{_sysconfdir}/%{gforge_name}/plugins/%{plugin_name}
%{__install} pluginconfig/config.php %{buildroot}%{_sysconfdir}/%{gforge_name}/plugins/%{plugin_name}/config.php

# /usr/share/gforge/config/scripts/config
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/config
%{__install} configscripts/config %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/config/plugin-%{plugin_name}
%{__sed} \
	-e "s|%PLUGIN_NAME%|%{plugin_name}|g" \
	-e "s|%NAME%|%{gforge_name}|g" \
	-e "s|%FRIENDLY_NAME%|%{gforge_friendly_name}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%BINDIR%|%{_bindir}|g" \
	-e "s|%SBINDIR%|%{_sbindir}|g" \
	-e "s|%APACHE_GROUP%|%{apache_group}|g" \
	-i %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/config/plugin-%{plugin_name}

# /usr/share/gforge/config/scripts/remove
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/remove
%{__install} configscripts/remove %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/remove/plugin-%{plugin_name}
%{__sed} \
	-e "s|%PLUGIN_NAME%|%{plugin_name}|g" \
	-e "s|%NAME%|%{gforge_name}|g" \
	-e "s|%FRIENDLY_NAME%|%{gforge_friendly_name}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%BINDIR%|%{_bindir}|g" \
	-e "s|%SBINDIR%|%{_sbindir}|g" \
	-e "s|%APACHE_GROUP%|%{apache_group}|g" \
	-i %{buildroot}%{_datadir}/%{gforge_name}/config/scripts/remove/plugin-%{plugin_name}

# /usr/share/gforge/plugins/projectResource/include
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/plugins/%{plugin_name}/include
%{__install} plugincore/include/* %{buildroot}%{_datadir}/%{gforge_name}/plugins/%{plugin_name}/include/

# /usr/share/gforge/override
%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/override
pushd %{buildroot}%{_datadir}/%{gforge_name}
DIRS=`find plugins -type d -printf "%%P\n" 2>/dev/null`
for DIR in $DIRS ; do
	if [ -n "$DIR" ] ; then
		%{__install} -d %{buildroot}%{_datadir}/%{gforge_name}/override/plugins/$DIR
	fi
done
popd

# /var/lib/gforge/config/plugin-projectResource
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{gforge_name}/config/plugin-%{plugin_name}

%clean
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && %{__rm} -rf %{buildroot}
%{__rm} -rf %{_builddir}/%{gforge_name}-plugin-%{plugin_name}-%{version}

%pre
if [ -x %{_bindir}/getdist ] ; then
	DIST=`%{_bindir}/getdist 2>/dev/null`
else
	DIST=unknown
fi
if [ "$DIST" != "%{dist}" ] ; then
	cat <<ENDTEXT
ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR

The Linux distribution of this system is '$DIST'.
This package has been built for Linux distribution '%{dist}' and will not function on this system.
Please install a package built specially for Linux distribution '$DIST'.

ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR
ENDTEXT
	exit 1
fi

%files
%defattr(-,root,root)
%dir %{_sysconfdir}/%{gforge_name}/plugins/%{plugin_name}
%attr(0640,root,%{apache_group}) %{_sysconfdir}/%{gforge_name}/plugins/%{plugin_name}/*
%attr(0755,root,root) %{_datadir}/%{gforge_name}/config/scripts/config/plugin-%{plugin_name}
%attr(0755,root,root) %{_datadir}/%{gforge_name}/config/scripts/remove/plugin-%{plugin_name}
%{_datadir}/%{gforge_name}/override/plugins/%{plugin_name}
%{_datadir}/%{gforge_name}/plugins/%{plugin_name}
%dir %{_localstatedir}/lib/%{gforge_name}/config/plugin-%{plugin_name}

%changelog
* Wed Feb 04 2009 Jean-Yves Cronier <jean-yves.cronier@bull.net> 1.2-1
- NovaForge 1.2 migration

* Mon Jun 04 2007 Gilles Menigot <gilles.menigot@bull.net> 1.1-1
- Moved to SVN repository
- Remove script is not executed anymore at %preun
- Requires gforge 4.5.11-19.1
- projectResource-conf.php renamed to config.php

* Tue Mar 20 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0-5
- Add message in config script  if /usr/share/gforge/config/util/functions
  is missing

* Tue Mar 13 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0-4
- Add missing version of getdist
- Spec file modifications for Aurora SPARC Linux 2.0 support

* Fri Feb 23 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0-3
- Spec file modifications for RHEL 4

* Wed Feb 14 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0-2
- Correction of removal script

* Thu Feb 08 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0-1
- Initial release
- svn tag: gforge-plugin-projectResource-1.0
