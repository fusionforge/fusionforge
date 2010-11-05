#
# RPM spec file for FusionForge
#
# Initial work for 4.8 by JL Bond Consulting
# Reworked for 5.1 by Alain Peyrat <aljeux@free.fr>
#
# TODO: Generate a random password to avoid sites with default pass.


# Global Definitions
%define dbname          gforge
%define dbuser          gforge

%define gfuser          gforge
%define gfgroup         gforge

%define httpduser       apache
%define httpdgroup      apache

%define fforge_admin    fforgeadmin
%define fforge_passwd   fforgeadmin

%define GFORGE_DIR      %{_datadir}/gforge
%define GFORGE_CONF_DIR %{_sysconfdir}/gforge
%define GFORGE_LANG_DIR %{_datadir}/locale
%define GFORGE_VAR_LIB  %{_var}/lib/gforge

%define reloadhttpd() /etc/init.d/httpd httpd reload >/dev/null 2>&1

# RPM spec preamble
Summary: FusionForge Collaborative Development Environment
Name: fusionforge
Version: @@VERSION@@
Release: 1%{?dist}
BuildArch: noarch
License: GPL
Group: Development/Tools
Source0: %{name}-%{version}.tar.bz2
Source1: README.mediawiki.jlbond
Source2: LocalSettings.php
Patch1: fusionforge-4.8.3-mediawiki.patch
Patch2: fusionforge-4.8.3-register_globals.patch
URL: http://www.fusionforge.org/
BuildRoot: %{_tmppath}/%{name}-%{version}-root
Packager: Alain Peyrat <aljeux@free.fr>

# requirements as derived from fusionforge-install-1-deps.php script
Requires: httpd, mod_dav_svn, mod_ssl, php, php-pgsql, php-gd, php-mbstring, mailman
Requires: postgresql, postgresql-libs, postgresql-server, postgresql-contrib
Requires: postfix, openssh, inetd, which, liberation-fonts

Requires: /bin/sh, /bin/bash
Requires: perl, perl-DBI, perl-HTML-Parser, perl-Text-Autoformat, perl-Mail-Sendmail, perl-Sort-Versions
Requires: cronolog
Requires: php-jpgraph php-gd
#update sys_path_to_jpgraph in gforge.conf if you remove this line
#Requires: /var/www/jpgraph/jpgraph.php
#Requires: libnss-pgsql >= 1.4
Requires: mailman
Requires: gettext
Requires: htmlpurifier >= 4.0.0
Requires: sed
Requires: coreutils
Requires: /usr/bin/newaliases
 
# BuildRequires: sed, perl

Provides: gforge = %{version}

%description
FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

%package plugin-aselectextauth
Summary: A-select external authentication for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, postgresql
%description plugin-aselectextauth
A system plugin for authenticating users in fusionforge. A-Select is a framework
where users can be authenticated by several means with Authentication
Service Providers.

%package plugin-cvssyncmail
Summary: Provides email notifications of changes to CVS repositories
Group: Development/Tools
Requires: %{name} >= %{version}, %{name}-scmcvs, python, php
%description plugin-cvssyncmail
This plugin adds the capability to notify users of changes to CVS repositories
in FusionForge.

%package plugin-cvstracker
Summary: Links CVS log messages to trackers and tasks.
Group: Development/Tools
Requires: %{name} >= %{version}, %{name}-scmcvs, php, postgresql
%description plugin-cvstracker
This is a fusionforge plugin that allows linking CVS log messages to
trackers and tasks. It will review all commits in a project and search for
specific string to know which task or tracker is related.

%package plugin-externalsearch
Summary: external search plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-externalsearch
This plugin adds a new search engine to your FusionForge site. It allows
your users to search your FusionForge site through external search engines
which have indexed it. You can define search engines you want to use in
the configuration file.

%package plugin-fckeditor
Summary: FCKEditor plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-fckeditor
FCKEditor is a WYSIWYG text editor that displays within a web browser.

%package plugin-gravatar
Summary: Gravatar plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-gravatar
This plugin adds faces images to FusionForge users using the gravatar service.

%package plugin-ldapextauth
Summary: external LDAP authentication for FusionForge plugin
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-ldapextauth
This plugin provides LDAP authentication capability for FusionForge.

%package plugin-mantis
Summary: MantisBT plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-mantis
A plugin to use the MantisBT web-based bug tracking system with FusionForge.

%package plugin-mediawiki
Summary: Mediawiki plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, mediawiki
%description plugin-mediawiki
This is a plugin to integrate MediaWiki within FusionForge.

%package plugin-online_help
Summary: online_help plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-online_help
This is a online_help plugin within FusionForge.

%package plugin-oslc
Summary: OSLC plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, php-ZendFramework > 1.10
%description plugin-oslc
OSLC-CM compatible plugin for FusionForge tracker system.
OSLC-CM is a standard specification for APIs in Change Management
applications. It is based on Web technologies such as REST, RDF, or AJAX.
This package provides an OSLC-CM V2 compatible plugin for FusionForge
tracker system.

%package plugin-projects_hierarchy
Summary: projects_hierarchy plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php
%description plugin-projects_hierarchy
This is a projects_hierarchy plugin within FusionForge.

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
Summary: Bazaar version control plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, git
%description plugin-scmgit
This is a plugin to integrate Bazaar version control system with FusionForge

%package plugin-scmhg
Summary: Mercurial (hg) version control plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, hg
%description plugin-scmhg
This is a plugin to integrate Mercurial (hg) version control system with FusionForge

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
Requires: %{name} >= %{version}, php, subversion
%description plugin-scmsvn
This RPM installs SCM SVN plugin for FusionForge and provides svn support
to FusionForge.

%package plugin-svncommitemail
Summary: subversion commit email plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, subversion, perl, %{name}-scmsvn >= %{version}
%description plugin-svncommitemail
This RPM installs subversion commit email notification plugin for FusionForge.

%package plugin-svntracker
Summary: SVNTracker plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, subversion, perl, postgresql, %{name}-scmsvn >= %{version}
%description plugin-svntracker
SVNTracker plugin allows linking SVN log messages to Trackers and tasks.
It will review all commits in a project and search for a specific string
to know which task or tracker is related.

%package plugin-blocks
Summary: Blocks plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}
%description plugin-blocks
HTML blocks plugin for FusionForge. 

%package plugin-extratabs
Summary: extratabs plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}
%description plugin-extratabs
HTML extratabs plugin for FusionForge. 

%package plugin-wiki
Summary: Wiki plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, postgresql
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

%package plugin-mantisbt
Summary: mantisbt plugin for FusionForge
Group: Development/Tools
Requires: %{name} >= %{version}, php, postgresql
%description plugin-mantisbt
mantisbt plugin for FusionForge. 

%prep
%setup -q
#%patch1 -p1
#%patch2 -p1
#%patch3 -p1

%build
# empty build section

%install
%{__rm} -rf $RPM_BUILD_ROOT

# creating required directories
%{__install} -m 755 -d $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d
%{__install} -m 755 -d $RPM_BUILD_ROOT%{_sysconfdir}/cron.d
%{__install} -m 755 -d $RPM_BUILD_ROOT/bin
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_DIR}
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_DIR}/lib
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/httpd.d
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/httpd.conf.d
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/config.ini.d
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/plugins
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_LANG_DIR}
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_VAR_LIB}
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_VAR_LIB}/upload
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_VAR_LIB}/scmtarballs
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_VAR_LIB}/scmsnapshots
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_VAR_LIB}/homedirs
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_VAR_LIB}/dumps
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_VAR_LIB}/chroot/scmrepos/svn
%{__install} -m 755 -d $RPM_BUILD_ROOT%{GFORGE_VAR_LIB}/chroot/scmrepos/cvs
%{__install} -m 755 -d $RPM_BUILD_ROOT/home/groups
# mock mediawiki directory because we symlink GForge skin to Monobook
%{__install} -m 755 -d $RPM_BUILD_ROOT/usr/share/mediawiki/skins

# we define a search and replace function, we'll be using this a lot
# to fix several parts of the installation
search_and_replace()
{
    /usr/bin/find . -type f | xargs grep -l ${1} | xargs %{__sed} -i -e "s+${1}+${2}+g"
}

# we need to fix up the fusionforge-install-3-db.php script to ref %{GFORGE_DIR}
search_and_replace "/opt/gforge" "%{GFORGE_DIR}"

# installing gforge
%{__cp} -a * $RPM_BUILD_ROOT/%{GFORGE_DIR}/

# create project vhost space symlink
%{__ln_s} /home/groups $RPM_BUILD_ROOT/%{GFORGE_VAR_LIB}/homedirs/groups
# install restricted shell for cvs accounts
%{__cp} -a plugins/scmcvs/bin/cvssh.pl $RPM_BUILD_ROOT/bin/

# Fix configuration files entries (various sys_* variables)
%{__cp} -a etc/local.inc.example $RPM_BUILD_ROOT/%{GFORGE_CONF_DIR}/local.inc
%{__sed} -i -e "s!/path/to/gforge!%{GFORGE_DIR}!g" $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/local.inc
%{__sed} -i -e "s/\$sys_dbname=.*/\$sys_dbname='%{dbname}';/g" $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/local.inc
%{__sed} -i -e "s/\$sys_dbuser=.*/\$sys_dbuser='%{dbuser}';/g" $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/local.inc
%{__sed} -i -e "s/\$sys_apache_user=.*/\$sys_apache_user='%{httpduser}';/g" $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/local.inc
%{__sed} -i -e "s/\$sys_apache_group=.*/\$sys_apache_group='%{httpdgroup}';/g" $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/local.inc
%{__sed} -i -e "s|\$sys_plugins_path=.*|\$sys_plugins_path=\"%{GFORGE_DIR}/plugins\";|g" $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/local.inc
%{__sed} -i -e "s|\$sys_upload_dir=.*|\$sys_upload_dir=\"\$sys_var_path/upload\";|g" $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/local.inc
%{__sed} -i -e "s|\$sys_urlroot=.*|\$sys_urlroot=\"%{GFORGE_DIR}/www\";|g" $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/local.inc

# Replace sys_localinc, sys_gfdbname, sys_gfdbuser
%{__cp} -a etc/httpd.secrets.example $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/httpd.secrets
%{__sed} -i -e "s|sys_localinc.*$|sys_localinc %{GFORGE_CONF_DIR}/local.inc|g" $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/httpd.secrets
%{__sed} -i -e "s|sys_gfdbname.*$|sys_gfdbname %{dbname}|g" $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/httpd.secrets
%{__sed} -i -e "s|sys_gfdbuser.*$|sys_gfdbname %{dbuser}|g" $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/httpd.secrets

# Apache configuration file
%{__cp} -a etc/gforge-httpd.conf.example $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d/gforge.conf
%{__sed} -i -e 's|.*php_value[[:space:]]*include_path.*$|\tphp_value\tinclude_path ".:/usr/share/gforge/www/include:/usr/share/gforge:/etc/gforge:/usr/share/gforge/common:/usr/share/gforge/www:/usr/share/gforge/plugins"|' $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d/gforge.conf
# install fusionforge crontab
%{__install} -m 644 packaging/cron.d/cron.fusionforge $RPM_BUILD_ROOT%{_sysconfdir}/cron.d/%{name}

%{__install} -m 644 deb-specific/sqlhelper.pm $RPM_BUILD_ROOT%{GFORGE_DIR}/lib/sqlhelper.pm

# Install locale files in Redhat standard location
%{__cp} -a locales/* $RPM_BUILD_ROOT/%{GFORGE_LANG_DIR}/

# create symlink for jpgraph
%{__ln_s} /usr/share/jpgraph $RPM_BUILD_ROOT%{GFORGE_DIR}/jpgraph

%{__rm} -f $RPM_BUILD_ROOT%{GFORGE_DIR}/utils/fusionforge-shell-postgresql.spec

%{__rm} -f $RPM_BUILD_ROOT%{GFORGE_DIR}/COPYING.php
%{__rm} -fr $RPM_BUILD_ROOT/%{GFORGE_DIR}/packaging
%{__rm} -fr $RPM_BUILD_ROOT/%{GFORGE_DIR}/deb-specific
%{__rm} -fr $RPM_BUILD_ROOT/%{GFORGE_DIR}/rpm-specific
%{__rm} -fr $RPM_BUILD_ROOT/%{GFORGE_DIR}/plugins/*/packaging
%{__rm} -fr $RPM_BUILD_ROOT/%{GFORGE_DIR}/plugins/*/*.spec

### Plugin setup ###
%{__cp} $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/*/etc/*.ini $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/config.ini.d/
%{__cp} $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/*/etc/cron.d/* $RPM_BUILD_ROOT%{_sysconfdir}/cron.d/
%{__cp} $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/*/etc/httpd.d/* $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d/
%{__cp} -rp $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/*/etc/plugins/* $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/plugins/
%{__rm} -f $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/README

# plugin: aselectextauth

# plugin: cvssyncmail

# plugin: cvstracker
# delete stuff that is clearly outdated/obsolete so we don't package this and confuse others
%{__rm} -f $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/cvstracker/httpd.conf
%{__rm} -f $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/cvstracker/Makefile
%{__rm} -rf $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/cvstracker/rpm-specific

# plugin: externalsearch

# plugin: fckeditor

# plugin: ldapextauth
%{__rm} -rf $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/ldapextauth/rpm-specific

# plugin: mantis

# plugin: mediawiki
# create symlink for apache configuration for mediawiki plugin
## first, delete the php_admin_value include_path
%{__sed} -i -e "/^.*php_admin_value[[:space:]]*include_path.*/d" $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/mediawiki/etc/httpd.d/61plugin-mediawiki
%{__ln_s} %{GFORGE_DIR}/plugins/mediawiki/etc/httpd.d/61plugin-mediawiki $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/httpd.d/03mediawiki.conf
# this is pre-activated, so create the config symlink
#%{__ln_s} %{GFORGE_DIR}/plugins/mediawiki/etc/plugins/mediawiki $RPM_BUILD_ROOT%{GFORGE_CONF_DIR}/plugins/mediawiki
# create symlinks to use MonoBook as the GForge skin
%{__ln_s} monobook $RPM_BUILD_ROOT/usr/share/mediawiki/skins/gforge
%{__ln_s} MonoBook.deps.php $RPM_BUILD_ROOT/usr/share/mediawiki/skins/GForge.deps.php
%{__ln_s} MonoBook.php $RPM_BUILD_ROOT/usr/share/mediawiki/skins/GForge.php
# sort out the GForge skin files and remove obsolete code
%{__rm} -rf $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/mediawiki/mediawiki-skin
%{__rm} -rf $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/mediawiki/usr/share/gforge
%{__rm} -rf $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/mediawiki/usr/share/mediawiki/skins
# insert our own LocalSettings.php
#%{__cp} -f %{SOURCE2} $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/mediawiki/usr/share/mediawiki/LocalSettings.php
# insert our own README file
%{__cp} -f %{SOURCE1} $RPM_BUILD_ROOT%{GFORGE_DIR}/plugins/mediawiki/README.jlbond

# plugin: online_help

# plugin: projects_hierarchy

# plugin: quota_management

# plugin: scmarch

# plugin: scmbzr

# plugin: scmccase

# plugin: scmcvs
%{__ln_s} ../../plugins/scmcvs $RPM_BUILD_ROOT%{GFORGE_DIR}/www/plugins/scmcvs
%{__install} -m 644 plugins/scmcvs/cron.d/%{name}-plugin-scmcvs $RPM_BUILD_ROOT%{_sysconfdir}/cron.d

# plugin: scmdarcs

# plugin: scmsvn
# this is pre-activated, so create the config symlink
%{__ln_s} ../../plugins/scmsvn $RPM_BUILD_ROOT%{GFORGE_DIR}/www/plugins/scmsvn

# plugin: scmgit
%{__ln_s} ../../plugins/scmgit $RPM_BUILD_ROOT%{GFORGE_DIR}/www/plugins/scmgit

# plugin: scmhg

# plugin: svncommitemail

# plugin: svntracker
# install crontab
%{__install} -m 644 plugins/svntracker/rpm-specific/cron.d/gforge-plugin-svntracker $RPM_BUILD_ROOT%{_sysconfdir}/cron.d

# plugin: blocks
%{__ln_s} ../../plugins/blocks/www/ $RPM_BUILD_ROOT%{GFORGE_DIR}/www/plugins/blocks

# plugin: extratabs
%{__ln_s} ../../plugins/extratabs/www/ $RPM_BUILD_ROOT%{GFORGE_DIR}/www/plugins/extratabs

# plugin: wiki
%{__ln_s} ../plugins/wiki/www/ $RPM_BUILD_ROOT%{GFORGE_DIR}/www/wiki

# plugin: oslc
%{__ln_s} ../../plugins/oslc/www/ $RPM_BUILD_ROOT%{GFORGE_DIR}/www/plugins/oslc

# plugin: projectlabels
%{__ln_s} ../../plugins/projectlabels/www/ $RPM_BUILD_ROOT%{GFORGE_DIR}/www/plugins/projectlabels

# plugin: contribtracker
%{__ln_s} ../../plugins/contribtracker/www/ $RPM_BUILD_ROOT%{GFORGE_DIR}/www/plugins/contribtracker

# plugin: globalsearch
%{__ln_s} ../../plugins/globalsearch/www/ $RPM_BUILD_ROOT%{GFORGE_DIR}/www/plugins/globalsearch

# plugin: mantisbt
%{__ln_s} ../../plugins/mantisbt/www/ $RPM_BUILD_ROOT%{GFORGE_DIR}/www/plugins/mantisbt

### END OF PLUGIN SETUP ###

%pre
# we will need postgresql to be running. we start it, even if it already is running
# this won't hurt anything, just ensure we have a running database
/sbin/service postgresql start >>/var/log/%{name}-install.log 2>&1

if [ "$1" -eq "1" ]; then
	# setup user/group for gforge
	if [ `/usr/bin/getent passwd | /bin/cut -d: -f1 | /bin/grep -c %{gfuser}` -eq 0 ] ; then
		echo "Did not find existing fusionforge user. Adding fusionforge group and user..." >>/var/log/%{name}-install.log 2>&1
		/usr/sbin/groupadd -r %{gfgroup}
		/usr/sbin/useradd -r -g %{gfgroup} -d %{GFORGE_DIR} -s /bin/bash -c "FusionForge User" %{gfuser}
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
	    FFORGE_ADMIN_USER=%{fforge_admin}
	    FFORGE_ADMIN_PASSWORD=%{fforge_passwd}
	    export FFORGE_DB FFORGE_USER FFORGE_ADMIN_USER FFORGE_ADMIN_PASSWORD
	    /usr/bin/php %{GFORGE_DIR}/fusionforge-install-3-db.php >>/var/log/%{name}-install.log 2>&1
	else
	    echo "Database %{dbname} already exists. Will not proceed with database setup." >>/var/log/%{name}-install.log 2>&1
	    echo "Please see %{GFORGE_DIR}/fusionforge-install-3-db.php and run it manually" >>/var/log/%{name}-install.log 2>&1
	    echo "if deemed necessary." >>/var/log/%{name}-install.log 2>&1
	fi

	/usr/bin/php %{GFORGE_DIR}/db/upgrade-db.php >>/var/log/%{name}-install.log 2>&1
	/usr/bin/php %{GFORGE_DIR}/fusionforge-install-4-config.php >>/var/log/%{name}-install.log 2>&1

	HOSTNAME=`hostname -f`
	%{__sed} -i -e "s!gforge.company.com!$HOSTNAME!g" %{GFORGE_CONF_DIR}/local.inc
	%{__sed} -i -e "s!gforge.company.com!$HOSTNAME!g" /etc/httpd/conf.d/gforge.conf

	/etc/init.d/httpd restart >/dev/null 2>&1

	chkconfig postgresql on >/dev/null 2>&1

	# generate random hash for session_key
	HASH=$(/bin/dd if=/dev/urandom bs=32 count=1 2>/dev/null | /usr/bin/sha1sum | cut -c1-40)
	%{__sed} -i -e "s/sys_session_key = 'foobar'/sys_session_key = '$HASH'/g" %{GFORGE_CONF_DIR}/local.inc

	# add noreply mail alias
	echo "noreply: /dev/null" >> /etc/aliases
	/usr/bin/newaliases >/dev/null 2>&1

	# display message about default admin account
	echo ""
	echo "You can now connect to your FusionForge installation using:"
	echo ""
	echo "   http://$HOSTNAME/"
	echo ""
	echo "The default fusionforge administrator account and password is:"
	echo ""
	echo "Account Name = %{fforge_admin}"
	echo "Password = %{fforge_passwd}"
	#echo "Please change it to something appropriate upon initial login."
	# give user a few seconds to read the message
	sleep 10
else
	/usr/bin/php %{GFORGE_DIR}/db/upgrade-db.php >>/var/log/%{name}-upgrade.log 2>&1
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
/usr/bin/psql -U %{dbuser} %{dbname} -f %{GFORGE_DIR}/plugins/aselectextauth/db/install_aselectextauth.psql

%preun plugin-aselectextauth
/usr/bin/psql -U %{dbuser} %{dbname} -f %{GFORGE_DIR}/plugins/aselectextauth/db/uninstall_aselectextauth.psql

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && %{__rm} -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
%doc AUTHORS* CHANGES COPYING INSTALL* NEWS README*
%doc docs/*
%attr(0660, %{httpduser}, gforge) %config(noreplace) %{GFORGE_CONF_DIR}/local.inc
%attr(0640, %{httpduser}, %{httpdgroup}) %config(noreplace) %{_sysconfdir}/httpd/conf.d/gforge.conf
%attr(0644, root, root) %{_sysconfdir}/cron.d/%{name}
%attr(0775, %{httpduser}, %{httpdgroup}) %dir %{GFORGE_VAR_LIB}/upload
%attr(755, root, %{httpdgroup}) %dir %{GFORGE_DIR}
# Files under %{GFORGE_DIR}
%{GFORGE_DIR}/AUTHORS*
%{GFORGE_DIR}/CHANGES
%{GFORGE_DIR}/COPYING
%{GFORGE_DIR}/INSTALL*
%{GFORGE_DIR}/NEWS
%{GFORGE_DIR}/README*
%{GFORGE_DIR}/fusionforge.spec
%{GFORGE_DIR}/fusionforge-install*
%{GFORGE_DIR}/gforge-restricted.sh
%{GFORGE_DIR}/install.sh
%{GFORGE_DIR}/install-common.inc
%{GFORGE_DIR}/jpgraph
# Directories under %{GFORGE_DIR}
%{GFORGE_DIR}/backend
%{GFORGE_DIR}/common
%{GFORGE_DIR}/contrib
%{GFORGE_DIR}/cronjobs
%{GFORGE_DIR}/db
%{GFORGE_DIR}/docs
%{GFORGE_DIR}/etc
%{GFORGE_DIR}/image-sources
%{GFORGE_DIR}/lib
%{GFORGE_DIR}/locales
%{GFORGE_DIR}/monitor
%{GFORGE_DIR}/translations
%{GFORGE_DIR}/utils
%{GFORGE_DIR}/setup
%dir %{GFORGE_DIR}/www
# files under %{GFORGE_DIR}/www
%{GFORGE_DIR}/www/*.php
%{GFORGE_DIR}/www/users
%{GFORGE_DIR}/www/favicon.ico
%{GFORGE_DIR}/www/projects
# directories under %{GFORGE_DIR}/www
%{GFORGE_DIR}/www/account
%{GFORGE_DIR}/www/activity
%{GFORGE_DIR}/www/admin
%{GFORGE_DIR}/www/developer
%{GFORGE_DIR}/www/docman
%{GFORGE_DIR}/www/export
%{GFORGE_DIR}/www/forum
%{GFORGE_DIR}/www/frs
%{GFORGE_DIR}/www/images
%{GFORGE_DIR}/www/include
%{GFORGE_DIR}/www/jscook
%{GFORGE_DIR}/www/js
%{GFORGE_DIR}/www/mail
%{GFORGE_DIR}/www/my
%{GFORGE_DIR}/www/new
%{GFORGE_DIR}/www/news
%{GFORGE_DIR}/www/people
%{GFORGE_DIR}/www/pm
%{GFORGE_DIR}/www/project
%{GFORGE_DIR}/www/register
%{GFORGE_DIR}/www/reporting
%{GFORGE_DIR}/www/scm
%{GFORGE_DIR}/www/scripts
%{GFORGE_DIR}/www/search
%{GFORGE_DIR}/www/snippet
%{GFORGE_DIR}/www/soap
%{GFORGE_DIR}/www/softwaremap
%{GFORGE_DIR}/www/squal
%{GFORGE_DIR}/www/stats
%{GFORGE_DIR}/www/survey
%{GFORGE_DIR}/www/tabber
%{GFORGE_DIR}/www/themes
%{GFORGE_DIR}/www/top
%{GFORGE_DIR}/www/tracker
%{GFORGE_DIR}/www/trove
%{GFORGE_DIR}/www/widgets
#%{GFORGE_DIR}/www/plugins/online_help
#%{GFORGE_DIR}/www/plugins/projects_hierarchy
#%{GFORGE_DIR}/www/plugins/quota_management
%dir %{GFORGE_DIR}/plugins
%{GFORGE_DIR}/plugins/env.inc.php
#%{GFORGE_DIR}/plugins/online_help
#%{GFORGE_DIR}/plugins/projects_hierarchy
#%{GFORGE_DIR}/plugins/quota_management
%{GFORGE_LANG_DIR}
%dir %{GFORGE_CONF_DIR}
%config(noreplace) %{GFORGE_CONF_DIR}/httpd.secrets
%dir %{GFORGE_CONF_DIR}/httpd.d
%dir %attr(0775,root,%{httpdgroup}) %{GFORGE_CONF_DIR}/plugins
%dir %{GFORGE_VAR_LIB}/scmtarballs
%dir %{GFORGE_VAR_LIB}/scmsnapshots
%dir %{GFORGE_VAR_LIB}/dumps
%{GFORGE_VAR_LIB}/homedirs
/home/groups
/bin/cvssh.pl

%files plugin-aselectextauth
%{GFORGE_DIR}/plugins/aselectextauth

%files plugin-cvssyncmail
%{GFORGE_DIR}/plugins/cvssyncmail

%files plugin-cvstracker
%{GFORGE_DIR}/plugins/cvstracker
%{GFORGE_DIR}/www/plugins/cvstracker
%attr(-,%{httpduser},%{httpdgroup}) %{GFORGE_CONF_DIR}/plugins/cvstracker

%files plugin-externalsearch
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/externalsearch/
%config(noreplace) %{GFORGE_CONF_DIR}/config.ini.d/externalsearch.ini
%{GFORGE_DIR}/plugins/externalsearch

%files plugin-fckeditor
%{GFORGE_DIR}/plugins/fckeditor
%{GFORGE_DIR}/www/plugins/fckeditor

%files plugin-gravatar
%{GFORGE_DIR}/plugins/gravatar

%files plugin-ldapextauth
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/ldapextauth/
%{GFORGE_DIR}/plugins/ldapextauth

%files plugin-mantis
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/mantis/
%config(noreplace) %{GFORGE_CONF_DIR}/config.ini.d/mantis.ini
%{GFORGE_DIR}/plugins/mantis
%{GFORGE_DIR}/www/plugins/mantis

%files plugin-mediawiki
%config(noreplace) %{GFORGE_CONF_DIR}/config.ini.d/mediawiki.ini
%config(noreplace) %{GFORGE_CONF_DIR}/httpd.d/03mediawiki.conf
%{_sysconfdir}/httpd/conf.d/61plugin-mediawiki
%{GFORGE_DIR}/plugins/mediawiki/
%{GFORGE_DIR}/www/plugins/mediawiki
/usr/share/mediawiki/skins/gforge
/usr/share/mediawiki/skins/GForge.deps.php
/usr/share/mediawiki/skins/GForge.php

%files plugin-online_help
%{GFORGE_DIR}/plugins/online_help
%{GFORGE_DIR}/www/plugins/online_help

%files plugin-oslc
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/oslc/
%{_sysconfdir}/httpd/conf.d/62plugin-oslc
%{GFORGE_DIR}/plugins/oslc
%{GFORGE_DIR}/www/plugins/oslc

%files plugin-projects_hierarchy
%{GFORGE_DIR}/plugins/projects_hierarchy
%{GFORGE_DIR}/www/plugins/projects_hierarchy

%files plugin-quota_management
%{GFORGE_DIR}/plugins/quota_management
%{GFORGE_DIR}/www/plugins/quota_management

%files plugin-scmarch
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/scmarch/
%config(noreplace) %{GFORGE_CONF_DIR}/config.ini.d/scmarch.ini
%{GFORGE_DIR}/plugins/scmarch

%files plugin-scmbzr
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/scmbzr/
%config(noreplace) %{GFORGE_CONF_DIR}/config.ini.d/scmbzr.ini
%{GFORGE_DIR}/plugins/scmbzr

%files plugin-scmdarcs
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/scmdarcs/
%config(noreplace) %{GFORGE_CONF_DIR}/config.ini.d/scmdarcs.ini
%{GFORGE_DIR}/plugins/scmdarcs

%files plugin-scmgit
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/scmgit/
%config(noreplace) %{GFORGE_CONF_DIR}/config.ini.d/scmgit.ini
%{GFORGE_DIR}/plugins/scmgit
%{GFORGE_DIR}/www/plugins/scmgit

%files plugin-scmhg
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/scmhg/
%config(noreplace) %{GFORGE_CONF_DIR}/config.ini.d/scmhg.ini
%{GFORGE_DIR}/plugins/scmhg

%files plugin-scmccase
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/scmccase/
%config(noreplace) %{GFORGE_CONF_DIR}/config.ini.d/scmccase.ini
%{GFORGE_DIR}/plugins/scmccase

%files plugin-scmcvs
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/scmcvs/
%config(noreplace) %{GFORGE_CONF_DIR}/config.ini.d/scmcvs.ini
%{_sysconfdir}/cron.d/%{name}-plugin-scmcvs
%{_sysconfdir}/httpd/conf.d/30virtualcvs
%{_sysconfdir}/httpd/conf.d/31virtualcvs.ssl
%{GFORGE_DIR}/plugins/scmcvs
%{GFORGE_DIR}/www/plugins/scmcvs
%{GFORGE_VAR_LIB}/chroot/scmrepos/cvs

%files plugin-scmsvn
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/scmsvn/
%config(noreplace) %{GFORGE_CONF_DIR}/config.ini.d/scmsvn.ini
%{GFORGE_DIR}/plugins/scmsvn
%{GFORGE_DIR}/www/plugins/scmsvn
%{GFORGE_VAR_LIB}/chroot/scmrepos/svn

%files plugin-svncommitemail
%{GFORGE_DIR}/plugins/svncommitemail

%files plugin-svntracker
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/svntracker/
%{_sysconfdir}/cron.d/gforge-plugin-svntracker
%{GFORGE_DIR}/plugins/svntracker
%{GFORGE_DIR}/www/plugins/svntracker

%files plugin-blocks
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/blocks/
%config(noreplace) %{GFORGE_CONF_DIR}/config.ini.d/blocks.ini
%{GFORGE_DIR}/plugins/blocks
%{GFORGE_DIR}/www/plugins/blocks

%files plugin-extratabs
%{GFORGE_DIR}/plugins/extratabs
%{GFORGE_DIR}/www/plugins/extratabs

%files plugin-wiki
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/wiki/
%{_sysconfdir}/cron.d/cron.wiki
%{_sysconfdir}/httpd/conf.d/03wiki.conf
%{GFORGE_DIR}/plugins/wiki
%{GFORGE_DIR}/www/wiki

%files plugin-projectlabels
%{GFORGE_DIR}/plugins/projectlabels
%{GFORGE_DIR}/www/plugins/projectlabels

%files plugin-contribtracker
%{GFORGE_DIR}/plugins/contribtracker
%{GFORGE_DIR}/www/plugins/contribtracker

%files plugin-globalsearch
%{GFORGE_DIR}/plugins/globalsearch
%{GFORGE_DIR}/www/plugins/globalsearch

%files plugin-mantisbt
%config(noreplace) %{GFORGE_CONF_DIR}/plugins/mantisbt/
%{GFORGE_DIR}/plugins/mantisbt
%{GFORGE_DIR}/www/plugins/mantisbt

%changelog
* Fri May 28 2010 - Alain Peyrat <aljeux@free.fr> - 5.0.50-1
- Ported to 5.1 tree.
- Reworked logic with rights on configuration files.
- Adapted to changes like scm refactoring.
- Adapted to changes to .ini configuration file.
- Lots of new plugins added.

* Tue May 13 2010 - Bond Masuda <bond.masuda@JLBond.com> - 4.8.3-2
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
