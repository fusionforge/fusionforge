#
# RPM spec file for FusionForge
#
# Initial work for 4.8 by JL Bond Consulting
# Reworked for 5.x by Alain Peyrat <aljeux@free.fr>
#
# Copyright (C) 2010-2012 Alain Peyrat
# Copyrght 2013, Franck Villaume - TrivialDev
# Copyrght 2014, Roland Mas
#

# Global Definitions
%define dbname          gforge
%define dbuser          gforge

%define gfuser          gforge
%define gfgroup         gforge

%define httpduser       apache
%define httpdgroup      apache

%define fforge_admin    fforgeadmin

%define FORGE_DIR       %{_datadir}/gforge
%define FORGE_CONF_DIR  %{_sysconfdir}/gforge
%define FORGE_LANG_DIR  %{_datadir}/locale
%define FORGE_BINARY_PATH       %{_datadir}/gforge/bin
%define FORGE_DATA_PATH %{_var}/lib/gforge
%define FORGE_CHROOT_PATH %{FORGE_DATA_PATH}/chroot
%define FORGE_PLUGINS_LIB_DIR       %{FORGE_DIR}/plugins
%define FORGE_PLUGINS_CONF_DIR        %{FORGE_CONF_DIR}/plugins

# If that works, then a better way would be the following:
# %define FORGE_DIR       %(src/utils/forge_get_config_basic fhsrh source_path)
# %define FORGE_CONF_DIR  %(src/utils/forge_get_config_basic fhsrh config_path)
# %define FORGE_LANG_DIR  %{_datadir}/locale
# %define FORGE_BINARY_PATH       %(src/utils/forge_get_config_basic fhsrh binary_path)
# %define FORGE_DATA_PATH   %(src/utils/forge_get_config_basic fhsrh data_path)
# %define FORGE_CHROOT_PATH   %(src/utils/forge_get_config_basic fhsrh chroot)
# %define FORGE_PLUGINS_LIB_DIR         %(src/utils/forge_get_config_basic fhsrh plugins_path)
# %define FORGE_PLUGINS_CONF_DIR        %{FORGE_CONF_DIR}/plugins

%define reloadhttpd() /etc/init.d/httpd httpd reload >/dev/null 2>&1

# Disable debug binary detection & generation to speed up process.
%global debug_package %{nil}

# RPM spec preamble
Summary: FusionForge Collaborative Development Environment
Name: fusionforge
Version: @@VERSION@@
Release: 1%{?dist}
BuildArch: noarch
License: GPL
Group: Development/Tools
Source0: %{name}-%{version}.tar.bz2
URL: http://www.fusionforge.org/
BuildRoot: %{_tmppath}/%{name}-%{version}-root
Packager: Alain Peyrat <aljeux@free.fr>

Requires: httpd, mod_dav_svn, mod_ssl, php, php-pgsql, php-gd, php-mbstring, mailman
Requires: postgresql >= 8.3
Requires: postgresql-server >= 8.3
Requires: postfix, openssh, inetd, which

Requires: /bin/sh, /bin/bash
Requires: perl, perl-DBI, perl-HTML-Parser, perl-Text-Autoformat, perl-Mail-Sendmail, perl-Sort-Versions
Requires: cronolog
#Requires: libnss-pgsql >= 1.4
Requires: gettext
Requires: php-htmlpurifier >= 4.0.0
Requires: sed
Requires: coreutils
Requires: /usr/bin/newaliases
Requires: php-pear-HTTP_WebDAV_Server
Requires: php-pecl-zip

# BuildRequires: sed, perl

%define INSTALL_LOG       %{_var}/log/gforge/install-%{version}.log
%define UPGRADE_LOG       %{_var}/log/gforge/upgrade-%{version}.log

Provides: gforge = %{version}

%description
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

%package plugin-admssw
Summary: ADMS.SW profiles for projects URLs for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, postgresql, plugin-doaprdf
%description plugin-admssw
This plugin will provide content-negociation means to export RDF+XML ADMS.SW profiles for projects on /projects URLs,
in addition to the content already provided by doaprdf.
ADMS.SW stands for Asset Description Metadata Schema for Software.
See https://joinup.ec.europa.eu/asset/adms_foss/description for more details.

%package plugin-aselectextauth
Summary: A-select external authentication for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, postgresql
%description plugin-aselectextauth
A system plugin for authenticating users in fusionforge. A-Select is a framework
where users can be authenticated by several means with Authentication
Service Providers.

%package plugin-authcas
Summary: External CAS authentication plugin for FusionForge.
Group: Development/Tools
Requires: %{name} >= %{version}, php, postgresql
%description plugin-authcas
External CAS authentication plugin for FusionForge.

%package plugin-authhttpd
Summary: External HTTPD authentication plugin for FusionForge.
Group: Development/Tools
Requires: %{name} >= %{version}, php, postgresql
%description plugin-authhttpd
External HTTPD authentication plugin for FusionForge.

# %package plugin-authopenid
# Summary: External OpenID authentication plugin for FusionForge.
# Group: Development/Tools
# Requires: %{name} >= %{version}, php, postgresql
# %description plugin-authopenid
# External OpenID authentication plugin for FusionForge.

%package plugin-ckeditor
Summary: CKEditor plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, ckeditor
%description plugin-ckeditor
CKEditor is a WYSIWYG text editor that displays within a web browser.

%package plugin-cvssyncmail
Summary: Provides email notifications of changes to CVS repositories
Group: Development/Tools
Requires: %{name} >= %{version}, %{name}-plugin-scmcvs, python, php
%description plugin-cvssyncmail
This plugin adds the capability to notify users of changes to CVS repositories
in FusionForge.

%package plugin-compactpreview
Summary: Provides a preview mecanism
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-compactpreview
This plugin adds support for user and project compact-preview
(popups) compatible with the OSLC specifications.

%package plugin-cvstracker
Summary: Links CVS log messages to trackers and tasks.
Group: Development/Tools
Requires: %{name} >= %{version}, %{name}-plugin-scmcvs, php, postgresql
%description plugin-cvstracker
This is a fusionforge plugin that allows linking CVS log messages to
trackers and tasks. It will review all commits in a project and search for
specific string to know which task or tracker is related.

%package plugin-doaprdf
Summary: DOAP RDF for projects
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-doaprdf
DOAP RDF for projects

%package plugin-externalsearch
Summary: external search plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-externalsearch
This plugin adds a new search engine to your FusionForge site. It allows
your users to search your FusionForge site through external search engines
which have indexed it. You can define search engines you want to use in
the configuration file.

%package plugin-extsubproj
Summary: external sub project plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-extsubproj
Manages links to external subprojects on remote forges.

# %package plugin-forumml
# Summary: Mailman to forums plugin for FusionForge
# Group: Development/Tools
# Requires: %{name} >= %{version}, php
# %description plugin-forumml
# ForumML integes mailing lists as forums in FusionForge

%package plugin-foafprofiles
Summary: FOAF profile for forge users
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-foafprofiles
The foafprofile plugin manages the generation of a FOAF profile for forge users.

%package plugin-gravatar
Summary: Gravatar plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-gravatar
This plugin adds faces images to FusionForge users using the gravatar service.

%package plugin-headermenu
Summary: Headermenu plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-headermenu
This plugin adds capability to add links right to login/logout.

%package plugin-hudson
Summary: Hudson continous integration plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-hudson
This plugin adds hudson integration to FusionForge.

%package plugin-authldap
Summary: external LDAP authentication for FusionForge plugin
Group: Development/Tools
Requires: %{name} >= %{version}, php, php-ldap
%description plugin-authldap
This plugin provides LDAP authentication capability for FusionForge.

%package plugin-mediawiki
Summary: Mediawiki plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, mediawiki
%description plugin-mediawiki
This is a plugin to integrate MediaWiki within FusionForge.

%package plugin-moinmoin
Summary: MoinMoinWiki plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, postgresql, moin
%description plugin-moinmoin
This is a plugin to integrate MoinMoin wiki within FusionForge.

%package plugin-message
Summary: Global Information Message plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-message
This is a plugin to add a global announce message for FusionForge.
It can be used to warn users for planned or current outage.

%package plugin-online_help
Summary: online_help plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-online_help
This is a online_help plugin within FusionForge.

# %package plugin-oslc
# Summary: OSLC plugin for FusionForge
# Group: Development/Tools
# Requires: %{name} >= %{version}, php, php-ZendFramework > 1.10
# %description plugin-oslc
# OSLC-CM compatible plugin for FusionForge tracker system.
# OSLC-CM is a standard specification for APIs in Change Management
# applications. It is based on Web technologies such as REST, RDF, or AJAX.
# This package provides an OSLC-CM V2 compatible plugin for FusionForge
# tracker system.

# %package plugin-projectimport
# Summary: Project Import plugin for FusionForge
# Group: Development/Tools
# Requires: %{name} >= %{version}, php
# %description plugin-projectimport
# Project import plugin for FusionForge
# This plugin allows the import of a project data previously exported
# with ForgePlucker, or a compatible tool.

# %package plugin-projects-hierarchy
# Summary: projects-hierarchy plugin for FusionForge
# Group: Development/Tools
# Requires: %{name} >= %{version}, php
# %description plugin-projects-hierarchy
# This is a projects-hierarchy plugin within FusionForge.

%package plugin-quota_management
Summary: quota_management plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-quota_management
This is a quota_management plugin within FusionForge.

%package plugin-scmarch
Summary: Arch version control plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, arch
%description plugin-scmarch
This is a plugin to integrate Arch version control system with FusionForge

%package plugin-scmbzr
Summary: Bazaar version control plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, bazaar
%description plugin-scmbzr
This is a plugin to integrate Bazaar version control system with FusionForge

%package plugin-scmdarcs
Summary: DARCS version control plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, darcs
%description plugin-scmdarcs
This is a plugin to integrate DARCS version control system with FusionForge

%package plugin-scmgit
Summary: Git version control plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, git, gitweb
%description plugin-scmgit
This is a plugin to integrate Git version control system with FusionForge

%package plugin-scmhg
Summary: Mercurial (hg) version control plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, mercurial
%description plugin-scmhg
This is a plugin to integrate Mercurial (hg) version control system with FusionForge

%package plugin-scmhook
Summary: Source Code Hooks plugin
Group: Development/Tools
Requires: %{name} >= %{version}
%description plugin-scmhook
This plugin provide a simple hook system for various version control system.
It allows project admins to activate/desactivate predefined hooks on their
repositories.

%package plugin-scmccase
Summary: Clear Case plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-scmccase
This is the Clear Case plugin for FusionForge. It creats Clear Case repositories
for projects within FusionForge.

%package plugin-scmcvs
Summary: CVS plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, cvs
%description plugin-scmcvs
FusionForge is a web-based Collaborative Development Environment offering
easy access to CVS, mailing lists, bug tracking, message
boards/forums, task management, permanent file archival, and total
web-based administration.

This RPM installs SCM CVS plugin for FusionForge and provides CVS support
to FusionForge.

It also provides a specific version of CVSWeb wrapped in FusionForge.

%package plugin-scmsvn
Summary: Subversion plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, subversion, viewvc
%description plugin-scmsvn
This RPM installs SCM SVN plugin for FusionForge and provides svn support
to FusionForge.

%package plugin-blocks
Summary: Blocks plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}
%description plugin-blocks
HTML blocks plugin for FusionForge. 

%package plugin-wiki
Summary: Wiki plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, postgresql, units
%description plugin-wiki
Wiki plugin for FusionForge. Allows for one wiki per project, integrated search,
page edits displayed on activity tab, and multi-project wiki preferences.

%package plugin-projectlabels
Summary: Labels plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, postgresql
%description plugin-projectlabels
Project Labels plugin for FusionForge. 

%package plugin-contribtracker
Summary: contribtracker plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, postgresql
%description plugin-contribtracker
contribtracker plugin for FusionForge. 

%package plugin-globalsearch
Summary: globalsearch plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, postgresql
%description plugin-globalsearch
globalsearch plugin for FusionForge. 

# %package plugin-mailman
# Summary: Mailman plugin for FusionForge
# Group: Development/Tools
# Requires: %{name} >= %{version}, php
# %description plugin-mailman
# Mailman plugin for FusionForge. 

# %package plugin-mantisbt
# Summary: mantisbt plugin for FusionForge
# Group: Development/Tools
# Requires: %{name} >= %{version}, php, postgresql
# %description plugin-mantisbt
# mantisbt plugin for FusionForge. 

# %package plugin-oauthprovider
# Summary: oauthprovider plugin for FusionForge
# Group: Development/Tools
# Requires: %{name} >= %{version}, php, postgresql
# %description plugin-oauthprovider
# oauthprovider plugin for FusionForge.

%package plugin-webanalytics
Summary: webanalytics plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, postgresql
%description plugin-webanalytics
webanalytics plugin for FusionForge.

%package plugin-sysauthldap
Summary: sysauthldap plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, postgresql
%description plugin-sysauthldap
sysauthldap plugin for FusionForge. 

%prep
%setup -q

%build
# empty build section

%install
%{__rm} -rf $RPM_BUILD_ROOT

# creating required directories
%{__install} -m 755 -d $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d
%{__install} -m 755 -d $RPM_BUILD_ROOT%{_sysconfdir}/cron.d
%{__install} -m 755 -d $RPM_BUILD_ROOT/bin
%{__install} -m 755 -d $RPM_BUILD_ROOT/usr/bin
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_DIR}
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_DIR}/vendor
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_DIR}/www
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_CONF_DIR}
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/httpd.d
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/httpd.conf.d
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_PLUGINS_CONF_DIR}
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_LANG_DIR}
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_DATA_PATH}
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_DATA_PATH}/upload
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_DATA_PATH}/scmtarballs
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_DATA_PATH}/scmsnapshots
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_DATA_PATH}/homedirs
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_DATA_PATH}/dumps
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_DATA_PATH}/etc
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_CHROOT_PATH}/scmrepos/svn
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_CHROOT_PATH}/scmrepos/cvs
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_DATA_PATH}/plugins/mediawiki
%{__install} -m 755 -d $RPM_BUILD_ROOT/home/groups
%{__install} -m 755 -d $RPM_BUILD_ROOT%{_var}/log/gforge
# mock mediawiki directory because we symlink GForge skin to Monobook
%{__install} -m 755 -d $RPM_BUILD_ROOT/usr/share/mediawiki/skins

# we define a search and replace function, we'll be using this a lot
# to fix several parts of the installation
search_and_replace()
{
    /usr/bin/find . -type f | xargs grep -l ${1} | xargs %{__sed} -i -e "s+${1}+${2}+g"
}

# we need to fix up the fusionforge-install-3-db.php script to ref %{FORGE_DIR}
search_and_replace "/opt/gforge" "%{FORGE_DIR}"

# Installing gforge
%{__cp} -a * $RPM_BUILD_ROOT/%{FORGE_DIR}/

# Install utils
%{__ln_s} %{FORGE_DIR}/utils ${RPM_BUILD_ROOT}%{FORGE_BINARY_PATH}
%{__ln_s} %{FORGE_BINARY_PATH}/forge_wrapper ${RPM_BUILD_ROOT}/usr/bin/forge_get_config
%{__ln_s} %{FORGE_BINARY_PATH}/forge_wrapper ${RPM_BUILD_ROOT}/usr/bin/forge_run_job
%{__ln_s} %{FORGE_BINARY_PATH}/forge_wrapper ${RPM_BUILD_ROOT}/usr/bin/forge_run_plugin_job

# Create project vhost space symlink
%{__ln_s} /home/groups $RPM_BUILD_ROOT/%{FORGE_DATA_PATH}/homedirs/groups
# install restricted shell for cvs accounts
%{__cp} -a plugins/scmcvs/bin/cvssh.pl $RPM_BUILD_ROOT/bin/

# Apache configuration file
%{__cp} -a etc/httpd.conf.d-fhsrh/* $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/httpd.conf.d/
%{__cp} -a etc/config.ini.d/defaults.ini $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d/
%{__cp} -a etc/config.ini-fhsrh $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini
%{__cp} -a etc/httpd.conf-fhsrh $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d/z-gforge.conf
#%{__cp} -a etc/gforge-httpd.conf.example $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d/z-gforge.conf
#%{__sed} -i -e 's|.*php_value[[:space:]]*include_path.*$|\tphp_value\tinclude_path ".:%{FORGE_DIR}/www/include:%{FORGE_DIR}:%{FORGE_CONF_DIR}:%{FORGE_DIR}/common:%{FORGE_DIR}/www:%{FORGE_PLUGINS_LIB_DIR}"|' $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d/z-gforge.conf

%{__sed} -i -e 's!www-data!apache!g' $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d/defaults.ini
%{__sed} -i -e 's!lists.$core/web_host!$core/web_host!g' $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d/defaults.ini
%{__sed} -i -e 's!scm.$core/web_host!$core/web_host!g' $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d/defaults.ini
%{__sed} -i -e 's!users.$core/web_host!$core/web_host!g' $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d/defaults.ini
%{__sed} -i -e 's!use_webdav = no!use_webdav = yes!g' $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d/defaults.ini
%{__sed} -i -e 's!use_shell = yes!use_shell = no!g' $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d/defaults.ini
%{__sed} -i -e 's!use_ftp = yes!use_ftp = no!g' $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d/defaults.ini
%{__sed} -i -e 's!use_people = yes!use_people = no!g' $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d/defaults.ini
%{__sed} -i -e 's!use_project_vhost = yes!use_project_vhost = no!g' $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d/defaults.ini
%{__sed} -i -e 's!use_snippet = yes!use_snippet = no!g' $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d/defaults.ini
%{__sed} -i -e 's!use_ratings = yes!use_ratings = no!g' $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d/defaults.ini

# install fusionforge crontab
%{__sed} -e 's/\$FFUSER/%{gfuser}/g' packaging/cron.d/cron.fusionforge > $RPM_BUILD_ROOT%{_sysconfdir}/cron.d/%{name}

# Install locale files in Redhat standard location
%{__cp} -a locales/* $RPM_BUILD_ROOT/%{FORGE_LANG_DIR}/

%{__rm} -f $RPM_BUILD_ROOT%{FORGE_DIR}/utils/fusionforge-shell-postgresql.spec

# Identify this FusionForge version
# keep type intact and change forge in derivates,
# unless there are deep changes (type is used for
# the Forge-Identification meta header)
WHICH_TYPE=FusionForge
WHICH_FORGE=FusionForge
WHICH_VERSION=%{version}-%{release}
%{__sed} \
    -e "s!@PKGNAME@!${WHICH_FORGE}!g" \
    -e "s!@PKGVERSION@!${WHICH_VERSION}!g" \
    -e "s!@PLUCKERNAME@!${WHICH_TYPE}!g" \
    <$RPM_BUILD_ROOT/%{FORGE_DIR}/common/pkginfo.inc.php.template \
    >$RPM_BUILD_ROOT/%{FORGE_DIR}/common/pkginfo.inc.php

%{__rm} -f $RPM_BUILD_ROOT%{FORGE_DIR}/COPYING.php
%{__rm} -fr $RPM_BUILD_ROOT/%{FORGE_DIR}/packaging
%{__rm} -fr $RPM_BUILD_ROOT/%{FORGE_DIR}/deb-specific
%{__rm} -fr $RPM_BUILD_ROOT/%{FORGE_DIR}/rpm-specific
%{__rm} -fr $RPM_BUILD_ROOT/%{FORGE_PLUGINS_LIB_DIR}/*/packaging
%{__rm} -fr $RPM_BUILD_ROOT/%{FORGE_PLUGINS_LIB_DIR}/*/*.spec


### Plugin setup ###
for i in $(utils/list-enabled-plugins.sh --disabled) ; do
    %{__rm} -rf $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/$i
    %{__rm} -rf $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d/$i.ini
done

%{__cp} $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/*/etc/*.ini $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/config.ini.d/
%{__cp} $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/*/etc/cron.d/* $RPM_BUILD_ROOT%{_sysconfdir}/cron.d/
%{__cp} $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/*/etc/httpd.d/* $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/httpd.d/
%{__cp} $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/*/etc/httpd.conf.d/* $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/httpd.conf.d/
%{__cp} -rp $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/*/etc/plugins/* $RPM_BUILD_ROOT%{FORGE_PLUGINS_CONF_DIR}/

# plugin: authbuiltin (internal plugin)
%{__ln_s} ../../plugins/authbuiltin/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/authbuiltin

# plugin: authcas
%{__ln_s} ../../plugins/authcas/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/authcas

# plugin: authhttpd
%{__ln_s} ../../plugins/authhttpd/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/authhttpd

# plugin: authopenid
# %{__ln_s} ../../plugins/authopenid/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/authopenid

# plugin: compactpreview
%{__ln_s} ../../plugins/compactpreview/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/compactpreview

# plugin: cvssyncmail

# plugin: cvstracker
%{__ln_s} ../../plugins/cvstracker/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/cvstracker

# plugin: externalsearch

# plugin: extsubproj
%{__ln_s} ../../plugins/extsubproj/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/extsubproj

# plugin: forumml
# %{__ln_s} ../../plugins/forumml/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/forumml

# plugin: hudson
%{__ln_s} ../../plugins/hudson/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/hudson

# plugin: mediawiki
%{__ln_s} ../../plugins/mediawiki/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/mediawiki
%{__ln_s} /usr/share/mediawiki/api.php $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/mediawiki/www/
%{__ln_s} /usr/share/mediawiki/extensions $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/mediawiki/www/
%{__ln_s} /usr/share/mediawiki/img_auth.php $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/mediawiki/www/
%{__ln_s} /usr/share/mediawiki/includes $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/mediawiki/www/
%{__ln_s} /usr/share/mediawiki/index.php $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/mediawiki/www/
%{__ln_s} /usr/share/mediawiki/languages $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/mediawiki/www/
%{__ln_s} /usr/share/mediawiki/maintenance/ $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/mediawiki/www/
%{__ln_s} /usr/share/mediawiki/opensearch_desc.php $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/mediawiki/www/
%{__ln_s} /usr/share/mediawiki/profileinfo.php $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/mediawiki/www/
%{__ln_s} /usr/share/mediawiki/redirect.php $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/mediawiki/www/
%{__ln_s} /usr/share/mediawiki/StartProfiler.php $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/mediawiki/www/
%{__ln_s} /usr/share/mediawiki/thumb.php $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/mediawiki/www/
%{__ln_s} /usr/share/mediawiki/trackback.php $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/mediawiki/www/
%{__ln_s} /usr/share/mediawiki/skins $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/mediawiki/www/
%{__ln_s} /usr/share/mediawiki $RPM_BUILD_ROOT%{FORGE_DATA_PATH}/plugins/mediawiki/master
%{__ln_s} %{FORGE_PLUGINS_LIB_DIR}/mediawiki/mediawiki-skin/FusionForge.php $RPM_BUILD_ROOT/usr/share/mediawiki/skins/
%{__ln_s} %{FORGE_PLUGINS_LIB_DIR}/mediawiki/mediawiki-skin/fusionforge $RPM_BUILD_ROOT/usr/share/mediawiki/skins/

# plugin: moinmoin
%{__ln_s} ../../plugins/moinmoin/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/moinmoin

# plugin: message
%{__ln_s} ../../plugins/message/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/message

# plugin: online_help
%{__ln_s} ../../plugins/online_help/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/online_help

# plugin: projects-hierarchy
# %{__ln_s} ../../plugins/projects-hierarchy/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/projects-hierarchy

# plugin: quota_management
%{__ln_s} ../../plugins/quota_management/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/quota_management

# plugin: scmarch

# plugin: scmbzr

# plugin: scmccase

# plugin: scmcvs
%{__ln_s} ../../plugins/scmcvs $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/scmcvs
%{__install} -m 644 plugins/scmcvs/cron.d/%{name}-plugin-scmcvs $RPM_BUILD_ROOT%{_sysconfdir}/cron.d

# plugin: scmdarcs

# plugin: scmsvn
# this is pre-activated, so create the config symlink
%{__ln_s} ../../plugins/scmsvn/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/scmsvn

# plugin: scmgit
%{__ln_s} ../../plugins/scmgit/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/scmgit
%{__install} -m 755 -d $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/scmgit/www/cgi-bin
%{__ln_s} /usr/share/gitweb/gitweb.cgi $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/scmgit/www/cgi-bin/gitweb.cgi
%{__ln_s} /usr/share/gitweb/static/gitweb.css $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/scmgit/www/gitweb.css
%{__ln_s} /usr/share/gitweb/static/gitweb.js $RPM_BUILD_ROOT%{FORGE_PLUGINS_LIB_DIR}/scmgit/www/gitweb.js
%{__rm} $RPM_BUILD_ROOT%{FORGE_CONF_DIR}/httpd.conf.d/plugin-scmgit-dav.inc
# plugin: scmhg

# plugin: blocks
%{__ln_s} ../../plugins/blocks/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/blocks

# plugin: headermenu
%{__ln_s} ../../plugins/headermenu/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/headermenu

# plugin: wiki
%{__ln_s} ../plugins/wiki/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/wiki

# plugin: oslc
#%{__ln_s} ../../plugins/oslc/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/oslc

# plugin : projectimport
# %{__ln_s} ../../plugins/projectimport/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/projectimport

# plugin: projectlabels
%{__ln_s} ../../plugins/projectlabels/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/projectlabels

# plugin: contribtracker
%{__ln_s} ../../plugins/contribtracker/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/contribtracker

# plugin: globalsearch
%{__ln_s} ../../plugins/globalsearch/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/globalsearch

# plugin: mailman
# %{__ln_s} ../../plugins/mailman/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/mailman

# plugin: mantisbt
# %{__ln_s} ../../plugins/mantisbt/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/mantisbt

# plugin: oauthprovider
#%{__ln_s} ../../plugins/oauthprovider/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/oauthprovider

# plugin: webanalytics
%{__ln_s} ../../plugins/webanalytics/www $RPM_BUILD_ROOT%{FORGE_DIR}/www/plugins/webanalytics

### END OF PLUGIN SETUP ###

%pre
[ -d %{_var}/log/gforge ] || mkdir -p %{_var}/log/gforge

if [ ! -d "/var/lib/pgsql/data/base" ]; then
	/sbin/service postgresql initdb  >>%{INSTALL_LOG} 2>&1
fi

# we will need postgresql to be running. we start it, even if it already is running
# this won't hurt anything, just ensure we have a running database
/sbin/service postgresql start >>%{INSTALL_LOG} 2>&1

if [ "$1" -eq "1" ]; then
	# setup user/group for gforge
	if [ `/usr/bin/getent passwd | /bin/cut -d: -f1 | /bin/grep -c %{gfuser}` -eq 0 ] ; then
		/usr/sbin/groupadd -r %{gfgroup}
		/usr/sbin/useradd -r -g %{gfgroup} -d %{FORGE_DIR} -s /bin/bash -c "FusionForge User" %{gfuser}
	fi
fi

%post
if [ "$1" -eq "1" ]; then
	# check to see if the database already exists. if not, we proceed to create it.
	# if so, we print a warning message.
	echo "\q" | su - postgres -c "/usr/bin/psql %{dbname}" 1>/dev/null 2>&1
	ret=$?
	if [ $ret -ne 0 ] ; then
	    FFORGE_DB=%{dbname}
	    FFORGE_USER=%{dbuser}
	    if [ "x${FFORGE_ADMIN_USER}" = "x" ]
	    then
	        FFORGE_ADMIN_USER=%{fforge_admin}
	    fi
	    if [ "x${FFORGE_ADMIN_PASSWORD}" = "x" ]
	    then
	        FFORGE_ADMIN_PASSWORD=$(/bin/dd if=/dev/urandom bs=32 count=1 2>/dev/null | /usr/bin/sha1sum | cut -c1-8)
	    fi
	    export FFORGE_DB FFORGE_USER FFORGE_ADMIN_USER FFORGE_ADMIN_PASSWORD
	    %{FORGE_DIR}/install-ng --config --database >>%{INSTALL_LOG} 2>&1
	else
	    echo "Database %{dbname} already exists. Will not proceed with database setup." >>%{INSTALL_LOG} 2>&1
	    echo "Please see %{FORGE_DIR}/install-ng --database and run it manually" >>%{INSTALL_LOG} 2>&1
	    echo "if deemed necessary." >>%{INSTALL_LOG} 2>&1
	    %{FORGE_DIR}/install-ng --config >>%{INSTALL_LOG} 2>&1
	fi

	HOSTNAME=`hostname -f`
	#%{__sed} -i -e "s!gforge.company.com!$HOSTNAME!g" %{FORGE_CONF_DIR}/local.inc
	#%{__sed} -i -e "s!gforge.company.com!$HOSTNAME!g" /etc/httpd/conf.d/z-gforge.conf
	[ -d %{FORGE_DATA_PATH}/etc ] || mkdir %{FORGE_DATA_PATH}/etc
	touch %{FORGE_DATA_PATH}/etc/httpd.vhosts

	%{__sed} -i -e "s/^#ServerName (.*):80/ServerName $HOSTNAME:80/" /etc/httpd/conf/httpd.conf

	mv %{FORGE_CONF_DIR}/httpd.conf.d/ssl-really-on.inc %{FORGE_CONF_DIR}/httpd.conf.d/ssl-on.inc
	%{__sed} -i -e "s!%{FORGE_CONF_DIR}/ssl-cert.pem!/etc/pki/tls/certs/localhost.crt!g" %{FORGE_CONF_DIR}/httpd.conf.d/ssl-on.inc
	%{__sed} -i -e "s!%{FORGE_CONF_DIR}/ssl-cert.key!/etc/pki/tls/private/localhost.key!g" %{FORGE_CONF_DIR}/httpd.conf.d/ssl-on.inc

	/etc/init.d/httpd restart >>%{INSTALL_LOG} 2>&1

	chkconfig postgresql on >>%{INSTALL_LOG} 2>&1

	# generate random hash for session_key
	HASH=$(/bin/dd if=/dev/urandom bs=32 count=1 2>/dev/null | /usr/bin/sha1sum | cut -c1-40)
	#%{__sed} -i -e "s/sys_session_key = 'foobar'/sys_session_key = '$HASH'/g" %{FORGE_CONF_DIR}/local.inc

	# Mailman initial setup
	/usr/lib/mailman/bin/newlist -q mailman $FFORGE_ADMIN_USER@$HOSTNAME $FFORGE_ADMIN_PASSWORD >>%{INSTALL_LOG} 2>&1
	chkconfig mailman on >>%{INSTALL_LOG} 2>&1
	/etc/init.d/mailman restart >>%{INSTALL_LOG} 2>&1

	# add noreply mail alias
	echo "noreply: /dev/null" >> /etc/aliases
	/usr/bin/newaliases >>%{INSTALL_LOG} 2>&1

	/usr/bin/php %{FORGE_DIR}/db/upgrade-db.php >>%{INSTALL_LOG} 2>&1
	/usr/bin/php %{FORGE_DIR}/utils/normalize_roles.php >>%{INSTALL_LOG} 2>&1

	if [ $ret -ne 0 ] ; then
		# display message about default admin account
		echo ""
		echo "You can now connect to your FusionForge installation using:"
		echo ""
		echo "   http://$HOSTNAME/"
		echo ""
		echo "The FusionForge administrator account and password is:"
		echo ""
		echo "Account Name = $FFORGE_ADMIN_USER"
		echo "Password = $FFORGE_ADMIN_PASSWORD"
		#echo "Please change it to something appropriate upon initial login."
		# give user a few seconds to read the message
		sleep 10
	fi
else
	/usr/bin/php %{FORGE_DIR}/db/upgrade-db.php >>%{UPGRADE_LOG} 2>&1
	/usr/bin/php %{FORGE_DIR}/utils/normalize_roles.php >>%{UPGRADE_LOG} 2>&1
fi

%preun

%postun
if [ "$1" -eq "0" ]; then
	# Remove user/group
	if [ `/usr/bin/getent passwd | /bin/cut -d: -f1 | /bin/grep -c %{gfuser}` -ne 0 ] ; then
	    echo "Removing fusionforge user..."
	    /usr/sbin/userdel %{gfuser}
	fi

	if [ `/usr/bin/getent group | /bin/cut -d: -f1 | /bin/grep -c %{gfuser}` -ne 0 ] ; then
	    echo "Removing fusionforge group..."
	    /usr/sbin/groupdel %{gfgroup}
	fi
fi

%post plugin-aselectextauth
/usr/bin/psql -U %{dbuser} %{dbname} -f %{FORGE_PLUGINS_LIB_DIR}/aselectextauth/db/install_aselectextauth.psql

%preun plugin-aselectextauth
/usr/bin/psql -U %{dbuser} %{dbname} -f %{FORGE_PLUGINS_LIB_DIR}/aselectextauth/db/uninstall_aselectextauth.psql

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && %{__rm} -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
%doc AUTHORS* CHANGES COPYING* INSTALL* NEWS README*
%doc docs/*
#%attr(0660, %{httpduser}, gforge) %config(noreplace) %{FORGE_CONF_DIR}/local.inc
#%attr(0640, %{httpduser}, %{httpdgroup}) %config(noreplace) %{_sysconfdir}/httpd/conf.d/z-gforge.conf
%attr(0644, root, root) %{_sysconfdir}/cron.d/%{name}
%attr(0775, %{httpduser}, %{httpdgroup}) %dir %{FORGE_DATA_PATH}/upload
%attr(755, root, %{httpdgroup}) %dir %{FORGE_DIR}
# Files under %{FORGE_DIR}
%{FORGE_DIR}/AUTHORS*
%{FORGE_DIR}/CHANGES
%{FORGE_DIR}/COPYING*
%{FORGE_DIR}/INSTALL*
%{FORGE_DIR}/Makefile
%{FORGE_DIR}/NEWS
%{FORGE_DIR}/README*
%{FORGE_DIR}/fusionforge.spec
%{FORGE_DIR}/install-ng
%{FORGE_PLUGINS_LIB_DIR}/README
# Directories under %{FORGE_DIR}
%{FORGE_BINARY_PATH}
%{FORGE_DIR}/backend
%{FORGE_DIR}/common
#%{FORGE_DIR}/contrib
%{FORGE_DIR}/cronjobs
%{FORGE_DIR}/db
%{FORGE_DIR}/docs
%{FORGE_DIR}/etc
%{FORGE_DIR}/image-sources
%{FORGE_DIR}/install
%{FORGE_DIR}/locales
%{FORGE_DIR}/translations
%{FORGE_DIR}/utils
%{FORGE_DIR}/vendor
#%{FORGE_DIR}/setup
%dir %{FORGE_DIR}/www
# files under %{FORGE_DIR}/www
%{FORGE_DIR}/www/*.php
%{FORGE_DIR}/www/users
%{FORGE_DIR}/www/favicon.ico
%{FORGE_DIR}/www/projects
# directories under %{FORGE_DIR}/www
%{FORGE_DIR}/www/account
%{FORGE_DIR}/www/activity
%{FORGE_DIR}/www/admin
%{FORGE_DIR}/www/developer
%{FORGE_DIR}/www/docman
%{FORGE_DIR}/www/DTD
%{FORGE_DIR}/www/export
%{FORGE_DIR}/www/forum
%{FORGE_DIR}/www/frs
%{FORGE_DIR}/www/images
%{FORGE_DIR}/www/include
%{FORGE_DIR}/www/js
%{FORGE_DIR}/www/mail
%{FORGE_DIR}/www/my
%{FORGE_DIR}/www/new
%{FORGE_DIR}/www/news
%{FORGE_DIR}/www/people
%{FORGE_DIR}/www/plugins
%{FORGE_DIR}/www/pm
%{FORGE_DIR}/www/project
%{FORGE_DIR}/www/register
%{FORGE_DIR}/www/reporting
%{FORGE_DIR}/www/scm
%{FORGE_DIR}/www/search
%{FORGE_DIR}/www/snippet
%{FORGE_DIR}/www/soap
%{FORGE_DIR}/www/softwaremap
%{FORGE_DIR}/www/squal
%{FORGE_DIR}/www/stats
%{FORGE_DIR}/www/support
%{FORGE_DIR}/www/survey
%{FORGE_DIR}/www/tabber
%{FORGE_DIR}/www/themes
%{FORGE_DIR}/www/top
%{FORGE_DIR}/www/tracker
%{FORGE_DIR}/www/trove
%{FORGE_DIR}/www/widgets
#%{FORGE_DIR}/www/plugins/online_help
#%{FORGE_DIR}/www/plugins/projects-hierarchy
#%{FORGE_DIR}/www/plugins/quota_management
%dir %{FORGE_PLUGINS_LIB_DIR}
%{FORGE_PLUGINS_LIB_DIR}/env.inc.php
#%{FORGE_PLUGINS_LIB_DIR}/online_help
#%{FORGE_PLUGINS_LIB_DIR}/projects-hierarchy
#%{FORGE_PLUGINS_LIB_DIR}/quota_management
%{FORGE_LANG_DIR}
%dir %{FORGE_CONF_DIR}
#%config(noreplace) %{FORGE_CONF_DIR}/httpd.secrets
%dir %{FORGE_CONF_DIR}/httpd.d
%dir %{FORGE_CONF_DIR}/httpd.conf.d
%{FORGE_CONF_DIR}/httpd.conf.d/*
%{_sysconfdir}/httpd/conf.d/z-gforge.conf
%{FORGE_CONF_DIR}/config.ini.d/defaults.ini
%{FORGE_CONF_DIR}/config.ini
%dir %attr(0775,root,%{httpdgroup}) %{FORGE_PLUGINS_CONF_DIR}
%dir %{FORGE_DATA_PATH}/scmtarballs
%dir %{FORGE_DATA_PATH}/scmsnapshots
%dir %{FORGE_DATA_PATH}/dumps
%{FORGE_DATA_PATH}/homedirs
%dir %{_var}/log/gforge
/home/groups
/bin/cvssh.pl
/usr/bin/forge_get_config
/usr/bin/forge_run_job
/usr/bin/forge_run_plugin_job
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/authbuiltin.ini
%{FORGE_PLUGINS_LIB_DIR}/authbuiltin

%files plugin-admssw
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/admssw.ini
%{FORGE_PLUGINS_LIB_DIR}/admssw

%files plugin-aselectextauth
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/aselectextauth.ini
%{FORGE_PLUGINS_LIB_DIR}/aselectextauth

%files plugin-authcas
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/authcas.ini
%{FORGE_PLUGINS_LIB_DIR}/authcas
%{FORGE_DIR}/www/plugins/authcas

%files plugin-authhttpd
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/authhttpd.ini
%{FORGE_PLUGINS_LIB_DIR}/authhttpd
%{FORGE_DIR}/www/plugins/authhttpd

# %files plugin-authopenid
# %config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/authopenid.ini
# %{FORGE_PLUGINS_LIB_DIR}/authopenid

%files plugin-ckeditor
%{FORGE_PLUGINS_LIB_DIR}/ckeditor
%{FORGE_CONF_DIR}/httpd.conf.d/plugin-ckeditor.inc
%{FORGE_CONF_DIR}/config.ini.d/ckeditor.ini

%files plugin-cvssyncmail
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/cvssyncmail.ini
%{FORGE_PLUGINS_LIB_DIR}/cvssyncmail

%files plugin-cvstracker
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/cvstracker.ini
%{FORGE_PLUGINS_LIB_DIR}/cvstracker
%{FORGE_DIR}/www/plugins/cvstracker
# %attr(-,%{httpduser},%{httpdgroup}) %{FORGE_PLUGINS_CONF_DIR}/cvstracker

%files plugin-compactpreview
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/compactpreview.ini
%{FORGE_PLUGINS_LIB_DIR}/compactpreview

%files plugin-doaprdf
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/doaprdf.ini
%{FORGE_PLUGINS_LIB_DIR}/doaprdf

%files plugin-externalsearch
%config(noreplace) %{FORGE_PLUGINS_CONF_DIR}/externalsearch/
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/externalsearch.ini
%{FORGE_PLUGINS_LIB_DIR}/externalsearch

%files plugin-extsubproj
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/extsubproj.ini
%{FORGE_PLUGINS_LIB_DIR}/extsubproj

%files plugin-foafprofiles
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/foafprofiles.ini
%{FORGE_PLUGINS_LIB_DIR}/foafprofiles

# %files plugin-forumml
# %config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/forumml.ini
# %{FORGE_PLUGINS_LIB_DIR}/forumml
# %{FORGE_DIR}/www/plugins/forumml

%files plugin-gravatar
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/gravatar.ini
%{FORGE_PLUGINS_LIB_DIR}/gravatar

%files plugin-headermenu
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/headermenu.ini
%{FORGE_PLUGINS_LIB_DIR}/headermenu
%{FORGE_DIR}/www/plugins/headermenu

%files plugin-hudson
%config(noreplace) %{FORGE_PLUGINS_CONF_DIR}/hudson/
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/hudson.ini
%{FORGE_CONF_DIR}/httpd.d/62plugin-hudson
%{FORGE_PLUGINS_LIB_DIR}/hudson
%{FORGE_DIR}/www/plugins/hudson

%files plugin-authldap
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/authldap.ini
%{FORGE_PLUGINS_LIB_DIR}/authldap

%files plugin-mediawiki
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/mediawiki.ini
%{_sysconfdir}/cron.d/fusionforge-plugin-mediawiki
%{FORGE_CONF_DIR}/httpd.d/61plugin-mediawiki
%{FORGE_PLUGINS_LIB_DIR}/mediawiki/
%{FORGE_DIR}/www/plugins/mediawiki
%{FORGE_DATA_PATH}/plugins/mediawiki
/usr/share/mediawiki/skins/FusionForge.php
/usr/share/mediawiki/skins/fusionforge
%config(noreplace) %{FORGE_PLUGINS_CONF_DIR}/mediawiki/

%files plugin-moinmoin
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/moinmoin.ini
%config(noreplace) %{FORGE_PLUGINS_CONF_DIR}/moinmoin/
%{FORGE_PLUGINS_LIB_DIR}/moinmoin/
%{FORGE_DIR}/www/plugins/moinmoin

%files plugin-message
%{FORGE_PLUGINS_LIB_DIR}/message
%{FORGE_DIR}/www/plugins/message
%{FORGE_CONF_DIR}/config.ini.d/message.ini

%files plugin-online_help
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/online_help.ini
%{FORGE_PLUGINS_LIB_DIR}/online_help
%{FORGE_DIR}/www/plugins/online_help

# %files plugin-oslc
# %config(noreplace) %{FORGE_PLUGINS_CONF_DIR}/oslc/
# %config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/oslc.ini
# %{FORGE_CONF_DIR}/httpd.d/plugin-oslc.inc
# %{FORGE_PLUGINS_LIB_DIR}/oslc
# %{FORGE_DIR}/www/plugins/oslc

# %files plugin-projectimport
# %config(noreplace) %{FORGE_PLUGINS_CONF_DIR}/projectimport/
# %config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/projectimport.ini
# %{FORGE_PLUGINS_LIB_DIR}/projectimport
# %{FORGE_DIR}/www/plugins/projectimport

# %files plugin-projects-hierarchy
# %config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/projects-hierarchy.ini
# %{FORGE_PLUGINS_LIB_DIR}/projects-hierarchy
# %{FORGE_DIR}/www/plugins/projects-hierarchy

%files plugin-quota_management
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/quota_management.ini
%{FORGE_PLUGINS_LIB_DIR}/quota_management
%{FORGE_DIR}/www/plugins/quota_management

%files plugin-scmarch
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/scmarch.ini
%{FORGE_PLUGINS_LIB_DIR}/scmarch

%files plugin-scmbzr
%config(noreplace) %{FORGE_PLUGINS_CONF_DIR}/scmbzr/
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/scmbzr.ini
%{FORGE_PLUGINS_LIB_DIR}/scmbzr

%files plugin-scmdarcs
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/scmdarcs.ini
%{FORGE_PLUGINS_LIB_DIR}/scmdarcs

%files plugin-scmgit
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/scmgit.ini
# %{FORGE_CONF_DIR}/httpd.conf.d/plugin-scmgit-dav.inc
%{FORGE_PLUGINS_LIB_DIR}/scmgit
%{FORGE_DIR}/www/plugins/scmgit

%files plugin-scmhg
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/scmhg.ini
%{FORGE_PLUGINS_LIB_DIR}/scmhg

%files plugin-scmhook
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/scmhook.ini
%{FORGE_PLUGINS_LIB_DIR}/scmhook

%files plugin-scmccase
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/scmccase.ini
%{FORGE_PLUGINS_LIB_DIR}/scmccase

%files plugin-scmcvs
%config(noreplace) %{FORGE_PLUGINS_CONF_DIR}/scmcvs/
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/scmcvs.ini
%{_sysconfdir}/cron.d/%{name}-plugin-scmcvs
%{FORGE_CONF_DIR}/httpd.d/30virtualcvs
%{FORGE_CONF_DIR}/httpd.d/31virtualcvs.ssl
%{FORGE_PLUGINS_LIB_DIR}/scmcvs
%{FORGE_DIR}/www/plugins/scmcvs
%{FORGE_CHROOT_PATH}/scmrepos/cvs

%files plugin-scmsvn
%config(noreplace) %{FORGE_PLUGINS_CONF_DIR}/scmsvn/
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/scmsvn.ini
%{FORGE_PLUGINS_LIB_DIR}/scmsvn
%{FORGE_DIR}/www/plugins/scmsvn
%{FORGE_CHROOT_PATH}/scmrepos/svn

%files plugin-blocks
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/blocks.ini
%{FORGE_PLUGINS_LIB_DIR}/blocks
%{FORGE_DIR}/www/plugins/blocks

%files plugin-wiki
%config(noreplace) %{FORGE_PLUGINS_CONF_DIR}/wiki/
%{_sysconfdir}/cron.d/%{name}-plugin-wiki
%{FORGE_CONF_DIR}/httpd.conf.d/plugin-wiki.inc
%{FORGE_PLUGINS_LIB_DIR}/wiki
%{FORGE_DIR}/www/wiki

%files plugin-projectlabels
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/projectlabels.ini
%{FORGE_PLUGINS_LIB_DIR}/projectlabels
%{FORGE_DIR}/www/plugins/projectlabels

%files plugin-contribtracker
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/contribtracker.ini
%{FORGE_PLUGINS_LIB_DIR}/contribtracker
%{FORGE_DIR}/www/plugins/contribtracker

%files plugin-globalsearch
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/globalsearch.ini
%{FORGE_PLUGINS_LIB_DIR}/globalsearch
%{FORGE_DIR}/www/plugins/globalsearch

# %files plugin-mailman
# %config(noreplace) %{FORGE_PLUGINS_CONF_DIR}/mailman/
# %config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/mailman.ini
# %{FORGE_CONF_DIR}/httpd.d/plugin-oslc.inc
# %{FORGE_CONF_DIR}/httpd.d/62plugin-list-mailman
# %{FORGE_CONF_DIR}/httpd.d/200list.vhost
# %{FORGE_CONF_DIR}/httpd.d/20list
# %{FORGE_CONF_DIR}/httpd.d/20zlist.vhost
# %{FORGE_CONF_DIR}/httpd.d/21list.vhost.ssl
# %{FORGE_PLUGINS_LIB_DIR}/mailman
# %{FORGE_DIR}/www/plugins/mailman

# %files plugin-mantisbt
# %config(noreplace) %{FORGE_PLUGINS_CONF_DIR}/mantisbt/
# %config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/mantisbt.ini
# %{FORGE_PLUGINS_LIB_DIR}/mantisbt
# %{FORGE_DIR}/www/plugins/mantisbt

# %files plugin-oauthprovider
# %config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/oauthprovider.ini
# %config(noreplace) %{FORGE_PLUGINS_CONF_DIR}/oauthprovider/
# %{FORGE_CONF_DIR}/httpd.d/62plugin-oauthprovider
# %{FORGE_PLUGINS_LIB_DIR}/oauthprovider

%files plugin-webanalytics
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/webanalytics.ini
%{FORGE_PLUGINS_LIB_DIR}/webanalytics
%{FORGE_DIR}/www/plugins/webanalytics

%files plugin-sysauthldap
%config(noreplace) %{FORGE_CONF_DIR}/config.ini.d/sysauthldap.ini
%{FORGE_PLUGINS_LIB_DIR}/sysauthldap

%changelog
* Mon Aug 04 2014 - Roland Mas <lolando@debian.org> - 5.3.1-1
- Adapted for 5.3

* Thu Jun 07 2012 - Alain Peyrat <aljeux@free.fr> - 5.1.90-1
- Adapted for 5.2 with new install scripts.

* Tue May 17 2011 - Thorsten Glaser <t.glaser@tarent.de> - 5.0.50-2
- Adapted for versioning of the forge via the packaging

* Fri May 28 2010 - Alain Peyrat <aljeux@free.fr> - 5.0.50-1
- Ported to 5.1 tree.
- Reworked logic with rights on configuration files.
- Adapted to changes like scm refactoring.
- Adapted to changes to .ini configuration file.
- Lots of new plugins added.

* Thu May 13 2010 - Bond Masuda <bond.masuda@JLBond.com> - 4.8.3-2
- fixed plugin symlinks and plugin directory permissions
- patched mediawiki, webcalendar plugins
- patch to fix various references to global variables
- add symlinks to use mediawiki Monobook skin as GForge
- patch to replace ereg_replace() with preg_replace()
- added jpgraph symlink
- setup httpd.secrets
- delete obsolete mediawiki plugin code

* Fri Apr 16 2010 - Bond Masuda <bond.masuda@JLBond.com> - 4.8.3-1
- My first packaging of fusionforge 4.8.3-1 and plugins
