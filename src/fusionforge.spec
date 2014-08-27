Name: fusionforge
Version: 5.3.50
Release: 1%{?dist}
Summary: collaborative development tool

Group: Development/Tools
BuildArch: noarch
License: GPLv2+
URL: http://www.fusionforge.org/
Source0: http://fusionforge.org/frs/download.php/file/XX/%{name}-%{version}.tar.bz2
Requires: %{name}-db-local = %{version}, %{name}-web = %{version}

%description
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This metapackage installs a stand-alone FusionForge site.
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
    echo %dir %{_localstatedir}/lib/%{name}  # avoid duplicate dir in all packages
    echo %dir %{_localstatedir}/log/%{name}  # only exists in -common, warning otherwise
) >> common.rpmfiles
%find_lang %{name}
# Install plugins
install_listfiles db-local
install_listfiles web
install_listfiles shell
install_listfiles mta-postfix
install_listfiles mta-exim4
install_listfiles plugin-scmgit
install_listfiles plugin-scmsvn
install_listfiles plugin-scmbzr
install_listfiles plugin-authhttpd
install_listfiles plugin-blocks
install_listfiles plugin-mediawiki
install_listfiles plugin-moinmoin
install_listfiles plugin-online_help



%package common
Summary: collaborative development tool - shared files
Requires: php-cli, php-pgsql, php-htmlpurifier-htmlpurifier, cronie
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
%{_datadir}/%{name}/post-install.d/common/common.sh


%package db-local
Summary: collaborative development tool - database (using PostgreSQL)
Requires: %{name}-common = %{version}, postgresql-server
%description db-local
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This package installs, configures and maintains the FusionForge
database.
%files db-local -f db-local.rpmfiles
%post db-local
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
Requires: %{name}-common = %{version}, php, openssh-server nscd
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
Requires: %{name}-common = %{version}, %{name}-db = %{version}, httpd, mod_ssl, php, php-pgsql
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


%package mta-postfix
Summary: collaborative development tool - mail tools (using Postfix)
Requires: %{name}-common = %{version}, postfix
Provides: mta
Conflicts: mta
%description mta-postfix
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This package configures the Postfix mail transfer agent to run
FusionForge.
%files mta-postfix -f mta-postfix.rpmfiles
%post mta-postfix
%{_datadir}/%{name}/post-install.d/mta-postfix/mta-postfix.sh


%package mta-exim4
Summary: collaborative development tool - mail tools (using Exim 4)
Requires: %{name}-common = %{version}, exim
Provides: mta
Conflicts: mta
%description mta-exim4
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This package configures the Exim 4 mail transfer agent to run
FusionForge.
%files mta-exim4 -f mta-exim4.rpmfiles
%post mta-exim4
%{_datadir}/%{name}/post-install.d/mta-exim4/mta-exim4.sh


%package plugin-scmgit
Summary: collaborative development tool - Git plugin
Group: Development/Tools
Requires: %{name}-web = %{version}, git, gitweb
%description plugin-scmgit
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This plugin contains the Git subsystem of FusionForge. It allows each
FusionForge project to have its own Git repository, and gives some
control over it to the project's administrator.
%files plugin-scmgit -f plugin-scmgit.rpmfiles
%post plugin-scmgit
%{_datadir}/%{name}/post-install.d/common/plugin.sh scmgit configure
%preun plugin-scmgit
%{_datadir}/%{name}/post-install.d/common/plugin.sh scmgit remove


%package plugin-scmsvn
Summary: collaborative development tool - Subversion plugin
Group: Development/Tools
Requires: %{name}-web = %{version}, subversion
%description plugin-scmsvn
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This plugin contains the Subversion subsystem of FusionForge. It allows
each FusionForge project to have its own Subversion repository, and gives
some control over it to the project's administrator.
%files plugin-scmsvn -f plugin-scmsvn.rpmfiles
%post plugin-scmsvn
%{_datadir}/%{name}/post-install.d/common/plugin.sh scmsvn configure
%preun plugin-scmsvn
%{_datadir}/%{name}/post-install.d/common/plugin.sh scmsvn remove


%package plugin-scmbzr
Summary: collaborative development tool - Bazaar plugin
Group: Development/Tools
Requires: %{name}-web = %{version}, bazaar, mod_wsgi, loggerhead
%description plugin-scmbzr
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This plugin contains the Bazaar subsystem of FusionForge. It allows each
FusionForge project to have its own Bazaar repository, and gives some control
over it to the project's administrator.
%files plugin-scmbzr -f plugin-scmbzr.rpmfiles
%post plugin-scmbzr
%{_datadir}/%{name}/post-install.d/common/plugin.sh scmbzr configure
%preun plugin-scmbzr
%{_datadir}/%{name}/post-install.d/common/plugin.sh scmbzr remove



%package plugin-authhttpd
Summary: collaborative development tool - HTTPD authentication plugin
Group: Development/Tools
Requires: %{name}-web = %{version}
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
%{_datadir}/%{name}/post-install.d/common/plugin.sh authhttpd configure
%preun plugin-authhttpd
%{_datadir}/%{name}/post-install.d/common/plugin.sh authhttpd remove


%package plugin-blocks
Summary: collaborative development tool - Blocks plugin
Group: Development/Tools
Requires: %{name}-web = %{version}
%description plugin-blocks
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This plugin contains the Blocks subsystem of FusionForge. It allows each
FusionForge project to have its own Blocks, and gives some
control over it to the project's administrator.
%files plugin-blocks -f plugin-blocks.rpmfiles
%post plugin-blocks
%{_datadir}/%{name}/post-install.d/common/plugin.sh blocks configure
%preun plugin-blocks
%{_datadir}/%{name}/post-install.d/common/plugin.sh blocks remove


%package plugin-mediawiki
Summary: collaborative development tool - Mediawiki plugin
Group: Development/Tools
Requires: %{name}-web = %{version}, mediawiki
%description plugin-mediawiki
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This plugin allows each project to embed Mediawiki under a tab.
%files plugin-mediawiki -f plugin-mediawiki.rpmfiles
%post plugin-mediawiki
%{_datadir}/%{name}/post-install.d/common/plugin.sh mediawiki configure
%preun plugin-mediawiki
%{_datadir}/%{name}/post-install.d/common/plugin.sh mediawiki remove


%package plugin-moinmoin
Summary: collaborative development tool - Bazaar plugin
Group: Development/Tools
Requires: %{name}-web = %{version}, moin, mod_wsgi, python-psycopg2
%description plugin-moinmoin
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This plugin allows each project to embed MoinMoinWiki under a tab.
%files plugin-moinmoin -f plugin-moinmoin.rpmfiles
%post plugin-moinmoin
%{_datadir}/%{name}/post-install.d/common/plugin.sh moinmoin configure
%preun plugin-moinmoin
%{_datadir}/%{name}/post-install.d/common/plugin.sh moinmoin remove


%package plugin-online_help
Summary: collaborative development tool - online_help plugin
Group: Development/Tools
Requires: %{name}-web = %{version}
%description plugin-online_help
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This is a online_help plugin within FusionForge.
%files plugin-online_help -f plugin-online_help.rpmfiles
%post plugin-online_help
%{_datadir}/%{name}/post-install.d/common/plugin.sh online_help configure
%preun plugin-online_help
%{_datadir}/%{name}/post-install.d/common/plugin.sh online_help remove


%changelog
* Tue Aug 19 2014 Sylvain Beucler <sylvain.beucler@inria.fr> - 5.3.50
- Revamp packaging
