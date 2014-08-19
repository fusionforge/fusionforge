Name: fusionforge
Version: 5.99.50
Release: 1%{?dist}
Summary: FusionForge collaborative development tool

Group: Development/Tools
BuildArch: noarch
License: GPLv2+
URL: http://www.fusionforge.org/
Source0: http://fusionforge.org/frs/download.php/file/XX/%{name}-%{version}.tar.bz2

%description
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

%package common
Summary: collaborative development tool - shared files
Requires: php
%description common
This package contains files and programs used by several other
subpackages.


%prep
%setup -q

%build
make %{?_smp_mflags}

%install
# List installed files automatically
# 'make install' knows how to install plugins separately, let's rely on it
install_listfiles()
{
    # Install separately to list the installed files
    make install-${1} prefix=%{_prefix} DESTDIR=%{_builddir}/t
    (
        cd %{_builddir}/t/
        find .%{_sysconfdir}/*              ! -type d | sed -e 's,^\./,%config(noreplace) /,'
        find .%{_bindir}/*                  ! -type d | sed -e 's,^\.,,'
        find .%{_datadir}/%{name}           ! -type d | sed -e 's,^\.,,'
        find .%{_localstatedir}/lib/%{name} ! -type d | sed -e 's,^\.,,'
        find .%{_localstatedir}/lib/%{name}/* -type d | sed -e 's,^\.,%dir ,'
    ) > ${1}.rpmfiles
    rm -rf %{_builddir}/t/
    # Install for real
    make install-${1} prefix=%{_prefix} DESTDIR=%{buildroot}
}
# Install core and list common dirs
install_listfiles core
(cd %{buildroot} && \
    find .%{_sysconfdir}/%{name} .%{_datadir}/%{name} -type d \
    | sed -e 's,^.,%dir ,'
    echo %dir %{_localstatedir}/lib/%{name} ) >> core.rpmfiles
%find_lang %{name}
# Install plugins
install_listfiles plugin-authhttpd
install_listfiles plugin-mediawiki

%files common -f core.rpmfiles -f %{name}.lang
%doc AUTHORS* CHANGES COPYING INSTALL.TXT NEWS README
%doc docs/*

%post common
# TODO: split db/apache/etc.
%{_datadir}/%{name}/post-install.d/core.sh


%package plugin-authhttpd
Summary: collaborative development tool - HTTPD authentication plugin
Group: Development/Tools
Requires: %{name}-common >= %{version}
%description plugin-authhttpd
This plugin contains an HTTPD authentication mechanism for
FusionForge. It allows Apache authentication to be reused for
FusionForge, for instance where Kerberos is used.
%files plugin-authhttpd -f plugin-authhttpd.rpmfiles
%post plugin-authhttpd
%{_datadir}/%{name}/post-install.d/plugin.sh authhttpd


%package plugin-mediawiki
Summary: Mediawiki plugin for FusionForge
Group: Development/Tools
Requires: %{name}-common >= %{version}
%description plugin-mediawiki
This plugin allows each project to embed Mediawiki under a tab.
%files plugin-mediawiki -f plugin-mediawiki.rpmfiles
%post plugin-mediawiki
%{_datadir}/%{name}/post-install.d/plugin.sh mediawiki


%changelog
* Tue Aug 19 2014 Sylvain Beucler <sylvain.beucler@inria.fr> - 5.99.50
- Revamp packaging
