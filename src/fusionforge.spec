Name: fusionforge
Version: 5.3.50
Release: 1%{?dist}
Summary: FusionForge collaborative development tool

Group: Development/Tools
BuildArch: noarch
License: GPLv2+
URL: http://www.fusionforge.org/
Source0: http://fusionforge.org/frs/download.php/file/XX/%{name}-%{version}.tar.bz2
Requires: %{name}-standard

%description
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.
%files


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
install_listfiles common
(cd %{buildroot} && \
    find .%{_sysconfdir}/%{name} .%{_datadir}/%{name} -type d \
    | sed -e 's,^.,%dir ,'
    echo %dir %{_localstatedir}/lib/%{name} ) >> common.rpmfiles
%find_lang %{name}
# Install plugins
install_listfiles db
install_listfiles shell
install_listfiles web
install_listfiles plugin-authhttpd
install_listfiles plugin-mediawiki



%package standard
Summary: FusionForge collaborative development tool - standard metapackage
Requires: %{name}-db %{name}-web
%description standard
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This metapackage installs a standard FusionForge site.
%files standard


%package common
Summary: collaborative development tool - shared files
Requires: php-cli
%description common
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This package contains files and programs used by several other
subpackages.
%files common -f common.rpmfiles -f %{name}.lang
%doc AUTHORS* CHANGES COPYING INSTALL.TXT NEWS README
%doc docs/*
%post common
%{_datadir}/%{name}/post-install.d/common/ini.sh


%package db
Summary: collaborative development tool - database (using PostgreSQL)
Requires: %{name}-common >= %{version} postgresql-server php-pgsql
%description db
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This package installs, configures and maintains the FusionForge
database.
%files db -f db.rpmfiles
%post db
%{_datadir}/%{name}/post-install.d/db/db.sh


%package db-remote
Summary: collaborative development tool - database (remote and already installed)
Provides: %{name}-db = %{version}
%description db-remote
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This dummy package tells FusionForge you installed the database on a
separate machine.  It preserves the fusionforge-db virtual dependency,
to configure the database before depending packages in single-server
installs (e.g. plugins activation requires a populated db).
%files db-remote


%package shell
Summary: collaborative development tool - shell accounts (using PostgreSQL)
Requires: %{name}-common >= %{version} php openssh-server nscd
#Requires: libnss-pgsql  # Fedora-only?
%description shell
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This package provides shell accounts authenticated via the PostGreSQL
database to FusionForge users.
%files shell -f shell.rpmfiles
%post shell
%{_datadir}/%{name}/post-install.d/shell/shell.sh configure
%preun shell
if [ $1 -eq 0 ] ; then
    %{_datadir}/%{name}/post-install.d/shell/shell.sh remove
    %{_datadir}/%{name}/post-install.d/shell/shell.sh purge
fi


%package web
Summary: collaborative development tool - web part (using Apache)
Requires: %{name}-common >= %{version} %{name}-db >= %{version} httpd mod_ssl php php-pgsql
%description web
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This package contains the files needed to run the web part of
FusionForge on an Apache webserver.
%files web -f web.rpmfiles
%post web
%{_datadir}/%{name}/post-install.d/web/configure.sh


%package plugin-authhttpd
Summary: collaborative development tool - HTTPD authentication plugin
Group: Development/Tools
Requires: %{name}-web >= %{version}
%description plugin-authhttpd
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This plugin contains an HTTPD authentication mechanism for
FusionForge. It allows Apache authentication to be reused for
FusionForge, for instance where Kerberos is used.
%files plugin-authhttpd -f plugin-authhttpd.rpmfiles
%post plugin-authhttpd
%{_datadir}/%{name}/post-install.d/plugin.sh authhttpd


%package plugin-mediawiki
Summary: Mediawiki plugin for FusionForge
Group: Development/Tools
Requires: %{name}-web >= %{version} mediawiki
%description plugin-mediawiki
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This plugin allows each project to embed Mediawiki under a tab.
%files plugin-mediawiki -f plugin-mediawiki.rpmfiles
%post plugin-mediawiki
%{_datadir}/%{name}/post-install.d/plugin.sh mediawiki


%changelog
* Tue Aug 19 2014 Sylvain Beucler <sylvain.beucler@inria.fr> - 5.3.50
- Revamp packaging
