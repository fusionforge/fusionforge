#---------------------------------------------------------------------------
# Novaforge is a registered trade mark from Bull S.A.S
# Copyright (C) 2007 Bull S.A.S.
# 
# http://novaforge.org/
#
#
# This file has been developped within the Novaforge(TM) project from Bull S.A.S
# and contributed back to GForge community.
#
# GForge is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# GForge is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this file; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#---------------------------------------------------------------------------

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
%define cvsgraph_version 1.6.1
%define friendly_name NovaForge
%define jpgraph_patch_level a
%define jpgraph_version 1.20.4
%define novaforge_level 1
%define novaforge_version %{version}.%{novaforge_level}
%define svncommitemail_version 1.0.2
%define svntracker_version 1.0.1
%define viewvc_version 1.0.4

# Constants related to other RPMs we provide
%define getdist_version 1.2
%define libnss_pgsql_version 1.3.1
%define mod_auth_gforge_version 0.5.9.3
%define mod_umask_version 0.1.0
%define perl_ipc_run_version 0.80
%define pwgen_version 2.05
%define subversion_version 1.4.2

# Constants related to the distribution
%define apache_group apache
%define apache_user apache
%if %{dist} == "rhel3"
%define byacc_version 1.9
%define cvs_version 1.11.2
%define docbook_style_xsl_version 1.61.2
%define flex_version 2.5.4a
%define gawk_version 3.1.1
%define gcc_version 3.2.3
%define gd_version 1.8.4
%define grep_version 2.5.1
%define httpd_version 2.0.46
%define libxml2_version 2.5.10
%define libxslt_version 1.0.33
%define logrotate_version 3.6.9
%define mailman_binaries_dir /var/mailman
%define mailman_data_dir /var/mailman
%define mailman_version 2.1.5
%define make_version 3.79
%define openssh_version 3.6.1p2
%define perl_dbd_pg_version 1.21
%define perl_dbi_version 1.32
%define perl_html_parser_version 3.26
%define perl_uri_version 1.21
%define php_version 4.3.2
%define postfix_version 2.0.11
%define postgresql_name rh-postgresql
%define postgresql_service rhdb
%define postgresql_version 7.3.4
%define rcs_version 5.7
%define sed_version 4.0.7
%define setup_version 2.5.27
%define vixie_cron_version 3.0.1
%define xinetd_version 2.3.12
%endif
%if %{dist} == "rhel4"
%define byacc_version 1.9
%define cvs_version 1.11.17
%define docbook_style_xsl_version 1.65.1
%define flex_version 2.5.4a
%define gawk_version 3.1.3
%define gcc_version 3.4.3
%define gd_version 2.0.28
%define grep_version 2.5.1
%define httpd_version 2.0.52
%define libxml2_version 2.6.16
%define libxslt_version 1.1.11
%define logrotate_version 3.7.1
%define mailman_binaries_dir /usr/lib/mailman
%define mailman_data_dir /var/lib/mailman
%define mailman_version 2.1.5
%define make_version 3.80
%define openssh_version 3.9p1
%define perl_dbd_pg_version 1.31
%define perl_dbi_version 1.40
%define perl_html_parser_version 3.35
%define perl_uri_version 1.30
%define php_version 4.3.9
%define postfix_version 2.1.5
%define postgresql_name postgresql
%define postgresql_service postgresql
%define postgresql_version 7.4.6
%define rcs_version 5.7
%define sed_version 4.1.2
%define setup_version 2.5.37
%define vixie_cron_version 4.1
%define xinetd_version 2.3.13
%endif
%if %{dist} == "rhel5"
%define byacc_version 1.9
%define cvs_version 1.11.22
%define docbook_style_xsl_version 1.69.1
%define flex_version 2.5.4a
%define gawk_version 3.1.5
%define gcc_version 4.1.2
%define gd_version 2.0.33
%define grep_version 2.5.1
%define httpd_version 2.2.3
%define libxml2_version 2.6.26
%define libxslt_version 1.1.17
%define logrotate_version 3.7.4
%define mailman_binaries_dir /usr/lib/mailman
%define mailman_data_dir /var/lib/mailman
%define mailman_version 2.1.9
%define make_version 3.81
%define openssh_version 4.3p2
%define perl_dbd_pg_version 1.49
%define perl_dbi_version 1.52
%define perl_html_parser_version 3.55
%define perl_uri_version 1.35
%define php_version 5.1.6
%define postfix_version 2.3.3
%define postgresql_name postgresql
%define postgresql_service postgresql
%define postgresql_version 8.1.11
%define rcs_version 5.7
%define sed_version 4.1.5
%define setup_version 2.5.58
%define vixie_cron_version 4.1
%define xinetd_version 2.3.14
%endif
%if %{dist} == "aurora2"
%define byacc_version 1.9
%define cvs_version 1.11.17
%define docbook_style_xsl_version 1.65.1
%define flex_version 2.5.4a
%define gawk_version 3.1.3
%define gcc_version 3.4.2
%define gd_version 2.0.28
%define grep_version 2.5.1
%define httpd_version 2.0.52
%define libxml2_version 2.6.14
%define libxslt_version 1.1.11
%define logrotate_version 3.7.1
%define mailman_binaries_dir /usr/lib/mailman
%define mailman_data_dir /var/lib/mailman
%define mailman_version 2.1.5
%define make_version 3.80
%define openssh_version 3.9p1
%define perl_dbd_pg_version 1.31
%define perl_dbi_version 1.40
%define perl_html_parser_version 3.35
%define perl_uri_version 1.30
%define php_version 4.3.10
%define postfix_version 2.1.5
%define postgresql_name postgresql
%define postgresql_service postgresql
%define postgresql_version 7.4.6
%define rcs_version 5.7
%define sed_version 4.1.2
%define setup_version 2.5.36
%define vixie_cron_version 4.1
%define xinetd_version 2.3.13
%endif
%if %{unsupported_dist} == 1
%define byacc_version 999
%define cvs_version 999
%define docbook_style_xsl_version 999
%define flex_version 999
%define gawk_version 999
%define gcc_version 999
%define gd_version 999
%define grep_version 999
%define httpd_version 999
%define libxml2_version 999
%define libxslt_version 999
%define logrotate_version 999
%define mailman_binaries_dir /dev/null
%define mailman_data_dir /dev/null
%define mailman_version 999
%define make_version 999
%define openssh_version 999
%define perl_dbd_pg_version 999
%define perl_dbi_version 999
%define perl_html_parser_version 999
%define perl_uri_version 999
%define php_version 999
%define postfix_version 999
%define postgresql_name postgresql
%define postgresql_service postgresql
%define postgresql_version 999
%define rcs_version 999
%define sed_version 999
%define setup_version 999
%define vixie_cron_version 999
%define xinetd_version 999
%endif

# Sources and patches
Source0:	gforge-%{version}.tar.gz

# Packages required for build
BuildRequires:	byacc >= %{byacc_version}
BuildRequires:  docbook-style-xsl >= %{docbook_style_xsl_version}
BuildRequires:	flex >= %{flex_version}
BuildRequires:	gcc >= %{gcc_version}
BuildRequires:	gd-devel >= %{gd_version}
BuildRequires:	getdist >= %{getdist_version}
BuildRequires:  libxml2 >= %{libxml2_version}
BuildRequires:  libxslt >= %{libxslt_version}
BuildRequires:	make >= %{make_version}
BuildRequires:	sed >= %{sed_version}

# Build architecture

# Build root
BuildRoot:      %{_tmppath}/%{name}-%{version}-%{release}-buildroot

#
# Main package
#

Summary:	%{friendly_name} Collaborative Development Environment
Name:		gforge
Version:	4.7.1
Release:	%{novaforge_level}.1.%{dist}
License:	GPL
Group:		Applications/Internet
URL:		http://www.gforge.org/
Conflicts:	sendmail
Conflicts:	exim
Requires:	%{name}-auth
Requires:	%{name}-trove
Requires:	%{name}-welcome
Requires:	cvs >= %{cvs_version}
Requires:	gawk >= %{gawk_version}
Requires:	gd >= %{gd_version}
Requires:	getdist >= %{getdist_version}
Requires:	grep >= %{grep_version}
Requires:	httpd >= %{httpd_version}
Requires:	logrotate >= %{logrotate_version}
Requires:	mailman >= %{mailman_version}
Requires:	mod_auth_gforge >= %{mod_auth_gforge_version}
Requires:	mod_ssl >= %{httpd_version}
Requires:	mod_umask >= %{mod_umask_version}
Requires:	openssh-server >= %{openssh_version}
Requires:	perl-DBD-Pg >= %{perl_dbd_pg_version}
Requires:	perl-DBI >= %{perl_dbi_version}
Requires:	perl-HTML-Parser >= %{perl_html_parser_version}
Requires:	perl-IPC-Run >= %{perl_ipc_run_version}
Requires:	perl-URI >= %{perl_uri_version}
Requires:	php >= %{php_version}
Requires:	php-gd >= %{php_version}
%if %{dist} == "rhel4"
Requires:	php-mbstring >= %{php_version}
%endif
%if %{dist} == "rhel5"
Requires:	php-mbstring >= %{php_version}
%endif
%if %{dist} == "aurora2"
Requires:	php-mbstring >= %{php_version}
%endif
Requires:	php-pgsql >= %{php_version}
Requires:	postfix >= %{postfix_version}
Requires:	%{postgresql_name} >= %{postgresql_version}
Requires:	%{postgresql_name}-pl >= %{postgresql_version}
Requires:	%{postgresql_name}-server >= %{postgresql_version}
Requires:	pwgen >= %{pwgen_version}
Requires:	rcs >= %{rcs_version}
Requires:	sed >= %{sed_version}
Requires:	vixie-cron >= %{vixie_cron_version}

%description
%{friendly_name} is a web-based Collaborative Development Environment
offering easy access to CVS, mailing lists, bug tracking, message
boards/forums, task management, permanent file archival, and total
web-based administration.

#
# Sub-package auth-unix
#

%package	auth-unix
Summary:	System authentication through standard UNIX files for %{friendly_name}
Group:		Applications/Internet
Provides:	%{name}-auth
Conflicts:	%{name}-auth-pgsql
Requires:	%{name} = %{version}-%{release}

%description	auth-unix
This RPM installs system authentication of %{friendly_name} users
and groups through standard UNIX files (/etc/passwd, /etc/shadow and
/etc/group).


#
# Sub-package auth-pgsql
#

%package	auth-pgsql
Summary:	System authentication through PostgreSQL for %{friendly_name}
Group:		Applications/Internet
Provides:	%{name}-auth
Conflicts:	%{name}-auth-unix
Requires:	%{name} = %{version}-%{release}
Requires:	gawk >= %{gawk_version}
Requires:	libnss-pgsql >= %{libnss_pgsql_version}

%description	auth-pgsql
This RPM installs system authentication of %{friendly_name} users
and groups through PostgreSQL backend.

#
# Sub-package config-standard
#

%package	config-standard
Summary:        Standard configuration templates for %{friendly_name}
Group:          Applications/Internet
Provides:       %{name}-config
Requires:       %{name} = %{version}-%{release}

%description	config-standard
This RPM contains the configuration templates used by the %{name}-config script
to create the configuration files.

#
# Sub-package trove-standard
#

%package	trove-standard
Summary:	Standard trove catalog for %{friendly_name}
Group:		Applications/Internet
Provides:	%{name}-trove
Requires:	%{name} = %{version}-%{release}

%description	trove-standard
This RPM contains the data used by the %{name}-init script to create
the standard trove catalog.

#
# Sub-package welcome-standard
#

%package	welcome-standard
Summary:	Standard welcome page for %{friendly_name}
Group:	Applications/Internet
Provides:	%{name}-welcome
Requires:	%{name} = %{version}-%{release}

%description	welcome-standard
This RPM contains the standard welcome page for %{friendly_name}.

#
# Sub-package plugin-cvssyncmail
#

%package	plugin-cvssyncmail
Summary:	CVS Syncmail plugin for %{friendly_name}
Group:		Applications/Internet
Requires:	%{name}-plugin-scmcvs = %{version}-%{release}

%description	plugin-cvssyncmail
This RPM installs the CVS Syncmail plugin for %{friendly_name}.
It allows users to be warned by mail when files are commited.

#
# Sub-package plugin-cvstracker
#

%package	plugin-cvstracker
Summary:	CVS Tracker plugin for %{friendly_name}
Group:		Applications/Internet
Requires:	%{name}-plugin-scmcvs = %{version}-%{release}

%description	plugin-cvstracker
This RPM installs the CVS Tracker plugin for %{friendly_name}.
It allows to link CVS logs to trackers and tasks.

#
# Sub-package plugin-scmcvs
#

%package	plugin-scmcvs
Summary:	CVS plugin for %{friendly_name}
Group:		Applications/Internet
Requires:	%{name} = %{version}-%{release}
Requires:	cvs >= %{cvs_version}
Requires:	xinetd >= %{xinetd_version}

%description	plugin-scmcvs
This RPM installs the CVS subsystem for %{friendly_name}.
It allows each project to have its own CVS repository,
and gives some control over it to the project's administrator.

#
# Sub-package plugin-scmsvn
#

%package	plugin-scmsvn
Summary:	Subversion plugin for %{friendly_name}
Group:		Applications/Internet
Requires:	%{name} = %{version}-%{release}
Requires:	mod_dav_svn >= %{subversion_version}
Requires:	setup >= %{setup_version}
Requires:	subversion >= %{subversion_version}
Requires:	xinetd >= %{xinetd_version}

%description	plugin-scmsvn
This RPM installs the Subversion subsystem for %{friendly_name}.
It allows each project to have its own Subversion repository,
and gives some control over it to the project's administrator.

#
# Sub-package plugin-svncommitemail
#

%package	plugin-svncommitemail
Summary:	Subversion CommitEmail plugin for %{friendly_name}
Group:		Applications/Internet
Requires:	%{name}-plugin-scmsvn = %{version}-%{release}

%description    plugin-svncommitemail
This RPM installs the Subversion CommitEmail plugin for %{friendly_name}.
It allows users to be warned by mail when files are commited.

#
# Sub-package plugin-svntracker
#

%package	plugin-svntracker
Summary:	Subversion Tracker plugin for %{friendly_name}
Group:		Applications/Internet
Requires:	%{name}-plugin-scmsvn = %{version}-%{release}

%description    plugin-svntracker
This RPM installs the Subversion Tracker plugin for %{friendly_name}.
It allows to link Subversion logs to trackers and tasks.

%prep
if [ "%{unsupported_dist}" = "1" ] ; then
	cat <<ENDTEXT
ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR

The Linux distribution of this system is '%{dist}'.
This package can be built on the following distributions:
- Red Hat Enterprise Linux 3 or CentOS 3 (rhel3)
- Red Hat Enterprise Linux 4 or CentOS 4 (rhel4)
- Red Hat Enterprise Linux 5 or CentOS 5 (rhel5)
- Aurora SPARC Linux 2.0 (aurora2)

ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR
ENDTEXT
	exit 1
fi
%setup -q

%build


%install
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && rm -rf %{buildroot}


##################
# Install GForge #
##################

# Install /etc/cron.d
%{__install} -d %{buildroot}%{_sysconfdir}/cron.d
%{__install} crond/* %{buildroot}%{_sysconfdir}/cron.d/
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-e "s|%BINDIR%|%{_bindir}|g" \
	-i %{buildroot}%{_sysconfdir}/cron.d/*

# Install /etc/gforge
%{__install} -d %{buildroot}%{_sysconfdir}/%{name}
touch %{buildroot}%{_sysconfdir}/%{name}/aliases
touch %{buildroot}%{_sysconfdir}/%{name}/aliases.db
touch %{buildroot}%{_sysconfdir}/%{name}/aliases.org
touch %{buildroot}%{_sysconfdir}/%{name}/local.inc
%{__install} -d %{buildroot}%{_sysconfdir}/%{name}/custom
%{__install} gforgeconfig/custom/project_homepage_template.php %{buildroot}%{_sysconfdir}/%{name}/custom/
%{__install} www/index_std.php %{buildroot}%{_sysconfdir}/%{name}/custom/index_std.php
%{__install} -d %{buildroot}%{_sysconfdir}/%{name}/languages-local
%{__install} -d %{buildroot}%{_sysconfdir}/%{name}/plugins
%{__install} -d %{buildroot}%{_sysconfdir}/%{name}/plugins/cvstracker
touch %{buildroot}%{_sysconfdir}/%{name}/plugins/cvstracker/config.php
%{__install} -d %{buildroot}%{_sysconfdir}/%{name}/plugins/scmcvs
touch  %{buildroot}%{_sysconfdir}/%{name}/plugins/scmcvs/config.php
%{__install} -d %{buildroot}%{_sysconfdir}/%{name}/plugins/scmsvn
touch %{buildroot}%{_sysconfdir}/%{name}/plugins/scmsvn/config.php
%{__install} -d %{buildroot}%{_sysconfdir}/%{name}/plugins/svntracker
touch %{buildroot}%{_sysconfdir}/%{name}/plugins/svntracker/config.php
%{__install} -d %{buildroot}%{_sysconfdir}/%{name}/ssl

# Install /etc/httpd/conf.d
%{__install} -d %{buildroot}%{_sysconfdir}/httpd/conf.d
touch %{buildroot}%{_sysconfdir}/httpd/conf.d/%{name}.conf
touch %{buildroot}%{_sysconfdir}/httpd/conf.d/%{name}-plugin-scmsvn.conf

# Install /etc/logrotate.d
%{__install} -d %{buildroot}%{_sysconfdir}/logrotate.d
%{__install} logrotate/* %{buildroot}%{_sysconfdir}/logrotate.d/

# Install /etc/xinetd.d
%{__install} -d %{buildroot}%{_sysconfdir}/xinetd.d
%{__install} xinetd/* %{buildroot}%{_sysconfdir}/xinetd.d/

# Install /svnroot
%{__ln_s} %{_localstatedir}/lib/%{name}/svnroot %{buildroot}/svnroot

# Install /usr/bin
%{__install} -d %{buildroot}%{_bindir}
%{__ln_s} %{_bindir}/php %{buildroot}%{_bindir}/php4
%{__ln_s} %{_bindir}/php %{buildroot}%{_bindir}/php5
%{__install} scripts/cvspserver %{buildroot}%{_bindir}/cvspserver
%{__install} scripts/svnserve %{buildroot}%{_bindir}/svnserve.%{name}

# Install /usr/sbin
%{__install} -d %{buildroot}%{_sbindir}
%{__install} scripts/gforge-* %{buildroot}%{_sbindir}/
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-e "s|%VERSION%|%{version}|g" \
	-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-e "s|%LIBDIR%|%{_libdir}|g" \
	-e "s|%SBINDIR%|%{_sbindir}|g" \
	-e "s|%MAILMANBINARIESDIR%|%{mailman_binaries_dir}|g" \
	-e "s|%MAILMANDATADIR%|%{mailman_data_dir}|g" \
	-e "s|%APACHE_GROUP%|%{apache_group}|g" \
	-e "s|%POSTGRESQL_SERVICE%|%{postgresql_service}|g" \
	-e "s|%NOVAFORGE_LEVEL%|%{novaforge_level}|g" \
        -e "s|%INITRDDIR%|%{_initrddir}|g" \
%if %{dist} == "rhel3"
	-e "s|^#SSHD_CONFIG||" \
%else
	-e "/^#SSHD_CONFIG/d" \
%endif
	-i %{buildroot}%{_sbindir}/*

# Install /usr/share/gforge
%{__install} -d %{buildroot}%{_datadir}/%{name}

# Install /usr/share/gforge/backend
%{__cp} -R backend %{buildroot}%{_datadir}/%{name}/

# Install /usr/share/gforge/common
%{__cp} -R common %{buildroot}%{_datadir}/%{name}/

# Install /usr/share/gforge/config
%{__install} -d %{buildroot}%{_datadir}/%{name}/config
%{__install} -d %{buildroot}%{_datadir}/%{name}/config/scripts
%{__install} -d %{buildroot}%{_datadir}/%{name}/config/scripts/config
for AUTH_NAME in pgsql unix ; do
	%{__install} configscripts/auth-$AUTH_NAME.config %{buildroot}%{_datadir}/%{name}/config/scripts/config/auth-$AUTH_NAME
	%{__sed} \
		-e "s|%AUTH_NAME%|$AUTH_NAME|g" \
		-e "s|%NAME%|%{name}|g" \
		-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
		-e "s|%BINDIR%|%{_bindir}|g" \
		-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
		-e "s|%DATADIR%|%{_datadir}|g" \
		-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
		-e "s|%APACHE_GROUP%|%{apache_group}|g" \
		-i %{buildroot}%{_datadir}/%{name}/config/scripts/config/auth-$AUTH_NAME
done
for PLUGIN_NAME in cvssyncmail cvstracker scmcvs scmsvn svncommitemail svntracker ; do
	%{__install} configscripts/plugin-$PLUGIN_NAME.config %{buildroot}%{_datadir}/%{name}/config/scripts/config/plugin-$PLUGIN_NAME
	%{__sed} \
		-e "s|%PLUGIN_NAME%|$PLUGIN_NAME|g" \
		-e "s|%NAME%|%{name}|g" \
		-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
		-e "s|%BINDIR%|%{_bindir}|g" \
		-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
		-e "s|%DATADIR%|%{_datadir}|g" \
		-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
		-e "s|%APACHE_GROUP%|%{apache_group}|g" \
		-i %{buildroot}%{_datadir}/%{name}/config/scripts/config/plugin-$PLUGIN_NAME
done
%{__install} -d %{buildroot}%{_datadir}/%{name}/config/scripts/destroy
for PLUGIN_NAME in cvssyncmail cvstracker scmcvs scmsvn svncommitemail svntracker ; do
	%{__install} configscripts/plugin-$PLUGIN_NAME.destroy %{buildroot}%{_datadir}/%{name}/config/scripts/destroy/plugin-$PLUGIN_NAME
	%{__sed} \
		-e "s|%PLUGIN_NAME%|$PLUGIN_NAME|g" \
		-e "s|%NAME%|%{name}|g" \
		-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
		-e "s|%BINDIR%|%{_bindir}|g" \
		-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
		-e "s|%DATADIR%|%{_datadir}|g" \
		-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
		-i %{buildroot}%{_datadir}/%{name}/config/scripts/destroy/plugin-$PLUGIN_NAME
done
%{__install} -d %{buildroot}%{_datadir}/%{name}/config/scripts/remove
for AUTH_NAME in pgsql unix ; do
	%{__install} configscripts/auth-$AUTH_NAME.remove %{buildroot}%{_datadir}/%{name}/config/scripts/remove/auth-$AUTH_NAME
	%{__sed} \
		-e "s|%AUTH_NAME%|$AUTH_NAME|g" \
		-e "s|%NAME%|%{name}|g" \
		-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
		-e "s|%BINDIR%|%{_bindir}|g" \
		-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
		-e "s|%DATADIR%|%{_datadir}|g" \
		-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
		-e "s|%APACHE_GROUP%|%{apache_group}|g" \
		-i %{buildroot}%{_datadir}/%{name}/config/scripts/remove/auth-$AUTH_NAME
done
for PLUGIN_NAME in cvssyncmail cvstracker scmcvs scmsvn svncommitemail svntracker ; do
	%{__install} configscripts/plugin-$PLUGIN_NAME.remove %{buildroot}%{_datadir}/%{name}/config/scripts/remove/plugin-$PLUGIN_NAME
	%{__sed} \
		-e "s|%PLUGIN_NAME%|$PLUGIN_NAME|g" \
		-e "s|%NAME%|%{name}|g" \
		-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
		-e "s|%BINDIR%|%{_bindir}|g" \
		-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
		-e "s|%DATADIR%|%{_datadir}|g" \
		-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
		-e "s|%APACHE_GROUP%|%{apache_group}|g" \
		-i %{buildroot}%{_datadir}/%{name}/config/scripts/remove/plugin-$PLUGIN_NAME
done
%{__install} -d %{buildroot}%{_datadir}/%{name}/config/skel
%{__install} config/aliases %{buildroot}%{_datadir}/%{name}/config/skel/
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
	-e "s|%MAILMANBINARIESDIR%|%{mailman_binaries_dir}|g" \
	-i %{buildroot}%{_datadir}/%{name}/config/skel/aliases
%{__install} apacheconfig/gforge.conf %{buildroot}%{_datadir}/%{name}/config/skel/
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-i %{buildroot}%{_datadir}/%{name}/config/skel/gforge.conf
%{__install} gforgeconfig/local.inc %{buildroot}%{_datadir}/%{name}/config/skel/
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-e "s|%MAILMANBINARIESDIR%|%{mailman_binaries_dir}|g" \
	-e "s|%APACHE_GROUP%|%{apache_group}|g" \
	-e "s|%APACHE_USER%|%{apache_user}|g" \
	-i %{buildroot}%{_datadir}/%{name}/config/skel/local.inc
%{__install} -d %{buildroot}%{_datadir}/%{name}/config/skel/auth-pgsql
%{__install} config/nss-pgsql.conf %{buildroot}%{_datadir}/%{name}/config/skel/auth-pgsql/
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-i %{buildroot}%{_datadir}/%{name}/config/skel/auth-pgsql/nss-pgsql.conf
%{__install} -d %{buildroot}%{_datadir}/%{name}/config/skel/plugin-cvstracker
%{__install} plugins/cvstracker/etc/plugins/cvstracker/config.php %{buildroot}%{_datadir}/%{name}/config/skel/plugin-cvstracker/
%{__sed} \
	-e "s|\$use_ssl = false;|\$use_ssl = @sys_use_ssl@;|" \
	-e "s|\$sys_default_domain = \"gforge.company.com\";|\$sys_default_domain = '@fqdn_hostname@';|" \
	-e "s|\$sys_plugins_path = \"/opt/gforge/gforge/plugins/\";|\$sys_plugins_path = '%{_datadir}/%{name}/plugins/';|" \
	-e "s|\$cvs_binary_version = \"1.12\";|\$cvs_binary_version = '1.11';|" \
	-i %{buildroot}%{_datadir}/%{name}/config/skel/plugin-cvstracker/config.php
%{__install} -d %{buildroot}%{_datadir}/%{name}/config/skel/plugin-scmcvs
%{__install} plugins/scmcvs/etc/plugins/scmcvs/config.php %{buildroot}%{_datadir}/%{name}/config/skel/plugin-scmcvs/
%{__sed} \
	-e "s|\$default_cvs_server = \$GLOBALS\['sys_default_domain'\] ;|\$default_cvs_server = '@fqdn_hostname@';|" \
	-e "s|\$cvs_binary_version='1.12';|\$cvs_binary_version = '1.11';|" \
	-e "s|\$use_ssl=false;|\$use_ssl=@sys_use_ssl@;|" \
	-e "s|\$sys_plugins_path='/opt/gforge/gforge/plugins';|\$sys_plugins_path = '%{_datadir}/%{name}/plugins/';|" \
	-e "s|\$sys_default_domain='gforge.company.com';|\$sys_default_domain = '@fqdn_hostname@';|" \
	-i %{buildroot}%{_datadir}/%{name}/config/skel/plugin-scmcvs/config.php
%{__install} -d %{buildroot}%{_datadir}/%{name}/config/skel/plugin-scmsvn
%{__install} apacheconfig/gforge-plugin-scmsvn.conf %{buildroot}%{_datadir}/%{name}/config/skel/plugin-scmsvn/
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-i %{buildroot}%{_datadir}/%{name}/config/skel/plugin-scmsvn/gforge-plugin-scmsvn.conf
%{__install} plugins/scmsvn/etc/plugins/scmsvn/config.php %{buildroot}%{_datadir}/%{name}/config/skel/plugin-scmsvn/
%{__sed} \
	-e "s|\$default_svn_server = \$GLOBALS\['sys_default_domain'\] ;|\$default_svn_server = '@fqdn_hostname@';|" \
	-e "s|\$use_ssh = false;|\$use_ssh = @sys_use_shell@;|" \
	-e "s|\$use_ssl = true;|\$use_ssl = @sys_use_ssl@;|" \
	-e "s|\$enabled_by_default = 0 ;|\$enabled_by_default = 1;|" \
	-i %{buildroot}%{_datadir}/%{name}/config/skel/plugin-scmsvn/config.php
%{__install} -d %{buildroot}%{_datadir}/%{name}/config/skel/plugin-svntracker
%{__install} plugins/svntracker/etc/plugins/svntracker/config.php %{buildroot}%{_datadir}/%{name}/config/skel/plugin-svntracker/
%{__sed} \
	-e "s|\$use_ssl = true;|\$use_ssl = @sys_use_ssl@;|" \
	-e "s|\$sys_default_domain = \"gforge\";|\$sys_default_domain = \"@fqdn_hostname@\";|" \
	-e "s|\$sys_plugins_path = \"/opt/gforge/gforge/plugins/\";|\$sys_plugins_path = \"%{_datadir}/%{name}/plugins/\";|" \
	-e "s|\$sys_svnroot_path = $svndir_prefix;|\$sys_svnroot_path = \"/svnroot\"|" \
	-e "s|\$svn_tracker_debug = true;|\$svn_tracker_debug = false;|" \
	-i %{buildroot}%{_datadir}/%{name}/config/skel/plugin-svntracker/config.php
%{__install} -d %{buildroot}%{_datadir}/%{name}/config/util
%{__install} configscripts/functions %{buildroot}%{_datadir}/%{name}/config/util/
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
	-e "s|%SYSCONFDIR%|%{_sysconfdir}|g" \
	-e "s|%INITRDDIR%|%{_initrddir}|g" \
	-e "s|%DATADIR%|%{_datadir}|g" \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-e "s|%BINDIR%|%{_bindir}|g" \
	-e "s|%MAILMANDATADIR%|%{mailman_data_dir}|g" \
	-e "s|%APACHE_USER%|%{apache_user}|g" \
	-e "s|%APACHE_GROUP%|%{apache_group}|g" \
	-i %{buildroot}%{_datadir}/%{name}/config/util/functions

# Install /usr/share/gforge/cronjobs
%{__install} -d %{buildroot}%{_datadir}/%{name}/cronjobs
%{__install} cronjobs/*.inc %{buildroot}%{_datadir}/%{name}/cronjobs/
%{__install} cronjobs/*.php %{buildroot}%{_datadir}/%{name}/cronjobs/
%{__install} -d %{buildroot}%{_datadir}/%{name}/cronjobs/mail
%{__install} cronjobs/mail/*.php %{buildroot}%{_datadir}/%{name}/cronjobs/mail/
%{__install} cronjobs/mail/*.py %{buildroot}%{_datadir}/%{name}/cronjobs/mail/
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-e "s|%FRIENDLY_NAME%|%{friendly_name}|g" \
	-i %{buildroot}%{_datadir}/%{name}/cronjobs/mail/mailaliases.php
%{__install} cronjobs/auth_unix.php %{buildroot}%{_datadir}/%{name}/cronjobs/
%{__install} cronjobs/create_home_dirs.php %{buildroot}%{_datadir}/%{name}/cronjobs/

# Install /usr/share/locale
if [ -e "locale" ] ; then
	%{__install} -d %{buildroot}%{_datadir}/locale
	%{__cp} -a locale/* %{buildroot}%{_datadir}/locale/
	%find_lang %{name}
fi

# Install /usr/share/gforge/db
%{__install} -d %{buildroot}%{_datadir}/%{name}/db
%{__install} db/gforge-pgsql7.3.sql %{buildroot}%{_datadir}/%{name}/db/%{name}.sql
%{__install} gforgedb/* %{buildroot}%{_datadir}/%{name}/db/
%{__sed} \
	-e "s|%NAME%|%{name}|g" \
	-e "s|%LOCALSTATEDIR%|%{_localstatedir}|g" \
	-i %{buildroot}%{_datadir}/%{name}/db/gforge-auth-pgsql.sql

# Install /usr/share/gforge/plugins
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/cvssyncmail
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/cvssyncmail/bin
%{__install} plugins/cvssyncmail/bin/syncmail-cvs-1.11 %{buildroot}%{_datadir}/%{name}/plugins/cvssyncmail/bin/syncmail
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/cvssyncmail/common
%{__install} plugins/cvssyncmail/common/cvssyncmail-init.php %{buildroot}%{_datadir}/%{name}/plugins/cvssyncmail/common/
%{__install} plugins/cvssyncmail/common/CVSSyncMailPlugin.class.php %{buildroot}%{_datadir}/%{name}/plugins/cvssyncmail/common/
%{__cp} -R plugins/cvssyncmail/common/languages %{buildroot}%{_datadir}/%{name}/plugins/cvssyncmail/include/
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/cvstracker
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/cvstracker/bin
%{__install} plugins/cvstracker/bin/post.php %{buildroot}%{_datadir}/%{name}/plugins/cvstracker/bin/
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/cvstracker/db
%{__cp} -R plugins/cvstracker/common %{buildroot}%{_datadir}/%{name}/plugins/cvstracker/
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/scmcvs
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/scmcvs/bin
%{__install} plugins/scmcvs/bin/aclcheck.php %{buildroot}%{_datadir}/%{name}/plugins/scmcvs/bin/
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/scmcvs/cronjobs
%{__install} plugins/scmcvs/cronjobs/tarballs.php %{buildroot}%{_datadir}/%{name}/plugins/scmcvs/cronjobs/
%{__install} plugins/scmcvs/cronjobs/history_parse.php %{buildroot}%{_datadir}/%{name}/plugins/scmcvs/cronjobs/
%{__cp} -R plugins/scmcvs/common %{buildroot}%{_datadir}/%{name}/plugins/scmcvs/
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/scmsvn
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/scmsvn/cronjobs
%{__install} plugins/scmsvn/cronjobs/tarballs.php %{buildroot}%{_datadir}/%{name}/plugins/scmsvn/cronjobs/
%{__install} plugins/scmsvn/cronjobs/svn-stats.php %{buildroot}%{_datadir}/%{name}/plugins/scmsvn/cronjobs/
%{__cp} -R plugins/scmsvn/common %{buildroot}%{_datadir}/%{name}/plugins/scmsvn/
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/svncommitemail
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/svncommitemail/bin
%{__install} plugins/svncommitemail/bin/commit-email.pl %{buildroot}%{_datadir}/%{name}/plugins/svncommitemail/bin/
%{__cp} -R plugins/svncommitemail/common %{buildroot}%{_datadir}/%{name}/plugins/svncommitemail/
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/svntracker
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/svntracker/bin
%{__install} plugins/svntracker/bin/post.php %{buildroot}%{_datadir}/%{name}/plugins/svntracker/bin/
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/svntracker/db
%{__install} plugins/svntracker/db/svntracker-*.sql %{buildroot}%{_datadir}/%{name}/plugins/svntracker/db/
%{__cp} -R plugins/svntracker/common %{buildroot}%{_datadir}/%{name}/plugins/svntracker/
%{__rm} -rf %{buildroot}%{_datadir}/%{name}/plugins/svntracker/include/CVS
%{__rm} -rf %{buildroot}%{_datadir}/%{name}/plugins/svntracker/include/languages/CVS

# Install /usr/share/gforge/utils
%{__cp} -R utils %{buildroot}%{_datadir}/%{name}/

# Install /usr/share/gforge/www
%{__cp} -R www %{buildroot}%{_datadir}/%{name}/
%{__rm} -f %{buildroot}%{_datadir}/%{name}/index_std.php
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/cvstracker/www
%{__install} plugins/cvstracker/www/newcommit.php %{buildroot}%{_datadir}/%{name}/plugins/cvstracker/www/
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/scmcvs/www
%{__install} plugins/scmcvs/www/acl.php %{buildroot}%{_datadir}/%{name}/plugins/scmcvs/www/
%{__install} -d %{buildroot}%{_datadir}/%{name}/plugins/svntracker/www
%{__install} plugins/svntracker/www/newcommit.php %{buildroot}%{_datadir}/%{name}/plugins/svntracker/www/

# Install /usr/share/gforge/override
%{__install} -d %{buildroot}%{_datadir}/%{name}/override
pushd %{buildroot}%{_datadir}/%{name}
for TOPDIR in common plugins www ; do
        DIRS=`find $TOPDIR -type d -printf "%%P\n"`
        for DIR in $DIRS ; do
                if [ -n "$DIR" ] ; then
                        %{__install} -d %{buildroot}%{_datadir}/%{name}/override/$TOPDIR/$DIR
                fi
        done
done
popd

# Install /var/cache/gforge
%{__install} -d %{buildroot}%{_localstatedir}/cache/%{name}

# Install /var/lib/gforge
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/config
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/config/plugin-cvssyncmail
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/config/plugin-cvstracker
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/config/plugin-scmcvs
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/config/plugin-scmsvn
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/config/plugin-svncommitemail
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/config/plugin-svntracker
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/cvsroot
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/home/groups
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/home/users
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/localizationcache
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/snapshots
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/tarballs
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/svnroot
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/upload_tmp_dir
%{__install} -d %{buildroot}%{_localstatedir}/lib/%{name}/uploads

# Install /var/log/gforge
%{__install} -d %{buildroot}%{_localstatedir}/log/%{name}

%clean
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && %{__rm} -rf %{buildroot}
%{__rm} -rf %{_builddir}/gforge-%{version}

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

%postun
if [ -x %{_initrddir}/crond ] ; then
	%{_initrddir}/crond condrestart >> /dev/null 2>&1
fi

%postun auth-unix
if [ -x %{_initrddir}/crond ] ; then
	%{_initrddir}/crond condrestart >> /dev/null 2>&1
fi

%postun plugin-scmcvs
if [ -x %{_initrddir}/crond ] ; then
	%{_initrddir}/crond condrestart >> /dev/null 2>&1
fi
if [ -x %{_initrddir}/xinetd ] ; then
	%{_initrddir}/xinetd condrestart >> /dev/null 2>&1
fi

%postun plugin-scmsvn
if [ -x %{_initrddir}/crond ] ; then
	%{_initrddir}/crond condrestart >> /dev/null 2>&1
fi
if [ -x %{_initrddir}/xinetd ] ; then
	%{_initrddir}/xinetd condrestart >> /dev/null 2>&1
fi

%files -f %{name}.lang
%defattr(-,root,root)
%doc AUTHORS* Change* COPYING* INSTALL
%attr(0644,root,root) %{_sysconfdir}/cron.d/%{name}
%dir %{_sysconfdir}/%{name}
%verify(not md5 size mtime) %{_sysconfdir}/%{name}/aliases
%verify(not md5 size mtime) %{_sysconfdir}/%{name}/aliases.db
%verify(not md5 size mtime) %{_sysconfdir}/%{name}/aliases.org
%verify(not md5 size mtime) %attr(0640,root,%{apache_group}) %{_sysconfdir}/%{name}/local.inc
%dir %{_sysconfdir}/%{name}/custom
%{_sysconfdir}/%{name}/custom/project_homepage_template.php
%dir %{_sysconfdir}/%{name}/languages-local
%dir %{_sysconfdir}/%{name}/plugins
%dir %{_sysconfdir}/%{name}/ssl
%verify(not md5 size mtime) %attr(0640,root,%{apache_group}) %{_sysconfdir}/httpd/conf.d/%{name}.conf
%attr(0644,root,root) %{_sysconfdir}/logrotate.d/%{name}
%attr(0755,root,root) %{_bindir}/php4
%attr(0755,root,root) %{_bindir}/php5
%attr(0755,root,root) %{_sbindir}/gforge-*
%dir %{_datadir}/%{name}
%{_datadir}/%{name}/backend
%{_datadir}/%{name}/common
%dir %{_datadir}/%{name}/config
%dir %{_datadir}/%{name}/config/scripts
%dir %{_datadir}/%{name}/config/scripts/config
%dir %{_datadir}/%{name}/config/scripts/destroy
%dir %{_datadir}/%{name}/config/scripts/remove
%dir %{_datadir}/%{name}/config/skel
%{_datadir}/%{name}/config/util
%exclude %{_datadir}/%{name}/cronjobs/auth_unix.php
%dir %{_datadir}/%{name}/cronjobs
%attr(0644,root,root) %{_datadir}/%{name}/cronjobs/*.inc
%attr(0644,root,root) %{_datadir}/%{name}/cronjobs/*.php
%dir %{_datadir}/%{name}/cronjobs/mail
%attr(0644,root,root) %{_datadir}/%{name}/cronjobs/mail/*.php
%attr(0644,root,root) %{_datadir}/%{name}/cronjobs/mail/*.py
%dir %{_datadir}/%{name}/db
%{_datadir}/%{name}/db/%{name}.sql
%{_datadir}/%{name}/db/%{name}-reporting.sql
%dir %{_datadir}/%{name}/override
%{_datadir}/%{name}/override/common
%dir %{_datadir}/%{name}/override/plugins
%{_datadir}/%{name}/override/www
%dir %{_datadir}/%{name}/plugins
%{_datadir}/%{name}/utils
%{_datadir}/%{name}/www
%dir %{_localstatedir}/lib/%{name}
%dir %attr(0750,root,root) %{_localstatedir}/lib/%{name}/config
%dir %{_localstatedir}/lib/%{name}/home
%dir %{_localstatedir}/lib/%{name}/home/groups
%dir %{_localstatedir}/lib/%{name}/home/users
%dir %attr(0750,%{apache_user},%{apache_group}) %{_localstatedir}/lib/%{name}/localizationcache
%dir %attr(0750,%{apache_user},%{apache_group}) %{_localstatedir}/lib/%{name}/snapshots
%dir %attr(0750,%{apache_user},%{apache_group}) %{_localstatedir}/lib/%{name}/tarballs
%dir %attr(0750,%{apache_user},%{apache_group}) %{_localstatedir}/lib/%{name}/upload_tmp_dir
%dir %attr(0750,%{apache_user},%{apache_group}) %{_localstatedir}/lib/%{name}/uploads
%dir %attr(0750,root,root) %{_localstatedir}/log/%{name}

%files auth-pgsql
%defattr(-,root,root)
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/config/auth-pgsql
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/remove/auth-pgsql
%{_datadir}/%{name}/config/skel/auth-pgsql
%{_datadir}/%{name}/db/%{name}-auth-pgsql.sql

%files auth-unix
%defattr(-,root,root)
%attr(0644,root,root) %{_sysconfdir}/cron.d/%{name}-auth-unix
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/config/auth-unix
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/remove/auth-unix
%{_datadir}/%{name}/cronjobs/auth_unix.php

%files config-standard
%defattr(-,root,root)
%{_datadir}/%{name}/config/skel/aliases
%{_datadir}/%{name}/config/skel/%{name}.conf
%{_datadir}/%{name}/config/skel/local.inc

%files trove-standard
%defattr(-,root,root)
%{_datadir}/%{name}/db/%{name}-trove_cat.sql

%files welcome-standard
%defattr(-,root,root)
%{_sysconfdir}/%{name}/custom/index_std.php

%files plugin-cvssyncmail
%defattr(-,root,root)
%doc plugins/cvssyncmail/INSTALL
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/config/plugin-cvssyncmail
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/destroy/plugin-cvssyncmail
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/remove/plugin-cvssyncmail
%{_datadir}/%{name}/override/plugins/cvssyncmail
%{_datadir}/%{name}/plugins/cvssyncmail
%dir %attr(0750,root,root) %{_localstatedir}/lib/%{name}/config/plugin-cvssyncmail

%files plugin-cvstracker
%defattr(-,root,root)
%doc plugins/cvstracker/AUTHORS plugins/cvstracker/COPYING plugins/cvstracker/README
%dir %{_sysconfdir}/%{name}/plugins/cvstracker
%verify(not md5 size mtime) %attr(0644,root,root) %{_sysconfdir}/%{name}/plugins/cvstracker/config.php
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/config/plugin-cvstracker
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/destroy/plugin-cvstracker
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/remove/plugin-cvstracker
%{_datadir}/%{name}/config/skel/plugin-cvstracker
%{_datadir}/%{name}/override/plugins/cvstracker
%{_datadir}/%{name}/plugins/cvstracker
%{_datadir}/%{name}/www/plugins/cvstracker
%dir %attr(0750,root,root) %{_localstatedir}/lib/%{name}/config/plugin-cvstracker

%files plugin-scmcvs
%defattr(-,root,root)
%doc plugins/scmcvs/AUTHORS plugins/scmcvs/COPYING plugins/scmcvs/README
%attr(0644,root,root) %{_sysconfdir}/cron.d/%{name}-plugin-scmcvs
%attr(0644,root,root) %{_sysconfdir}/logrotate.d/%{name}-plugin-scmcvs
%dir %{_sysconfdir}/%{name}/plugins/scmcvs
%verify(not md5 size mtime) %attr(0644,root,root) %{_sysconfdir}/%{name}/plugins/scmcvs/config.php
%attr(0755,root,root) %{_bindir}/cvspserver
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/config/plugin-scmcvs
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/destroy/plugin-scmcvs
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/remove/plugin-scmcvs
%{_datadir}/%{name}/config/skel/plugin-scmcvs
%{_datadir}/%{name}/override/plugins/scmcvs
%dir %{_datadir}/%{name}/plugins/scmcvs
%dir %{_datadir}/%{name}/plugins/scmcvs/bin
%attr(0644,root,root) %{_datadir}/%{name}/plugins/scmcvs/bin/*.php
%dir %{_datadir}/%{name}/plugins/scmcvs/cronjobs
%attr(0644,root,root) %{_datadir}/%{name}/plugins/scmcvs/cronjobs/*.php
%{_datadir}/%{name}/plugins/scmcvs
%{_datadir}/%{name}/www/plugins/scmcvs
%dir %attr(0750,root,root) %{_localstatedir}/lib/%{name}/config/plugin-scmcvs
%dir %{_localstatedir}/lib/%{name}/cvsroot

%files plugin-scmsvn
%defattr(-,root,root)
%doc plugins/scmsvn/README
%attr(0644,root,root) %{_sysconfdir}/cron.d/%{name}-plugin-scmsvn
%verify(not md5 size mtime) %attr(0640,root,%{apache_group}) %{_sysconfdir}/httpd/conf.d/%{name}-plugin-scmsvn.conf
%attr(0644,root,root) %{_sysconfdir}/logrotate.d/%{name}-plugin-scmsvn
%dir %{_sysconfdir}/%{name}/plugins/scmsvn
%verify(not md5 size mtime) %attr(0644,root,root) %{_sysconfdir}/%{name}/plugins/scmsvn/config.php
%attr(0644,root,root) %{_sysconfdir}/xinetd.d/svn
/svnroot
%attr(0755,root,root) %{_bindir}/svnserve.%{name}
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/config/plugin-scmsvn
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/destroy/plugin-scmsvn
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/remove/plugin-scmsvn
%{_datadir}/%{name}/config/skel/plugin-scmsvn
%{_datadir}/%{name}/override/plugins/scmsvn
%dir %{_datadir}/%{name}/plugins/scmsvn
%dir %{_datadir}/%{name}/plugins/scmsvn/cronjobs
%attr(0644,root,root) %{_datadir}/%{name}/plugins/scmsvn/cronjobs/*.php
%{_datadir}/%{name}/plugins/scmsvn
%dir %attr(0750,root,root) %{_localstatedir}/lib/%{name}/config/plugin-scmsvn
%dir %{_localstatedir}/lib/%{name}/svnroot

%files plugin-svncommitemail
%defattr(-,root,root)
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/config/plugin-svncommitemail
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/destroy/plugin-svncommitemail
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/remove/plugin-svncommitemail
%{_datadir}/%{name}/override/plugins/svncommitemail
%dir %{_datadir}/%{name}/plugins/svncommitemail
%dir %{_datadir}/%{name}/plugins/svncommitemail/bin
%attr(0755,root,root) %{_datadir}/%{name}/plugins/svncommitemail/bin/commit-email.pl
%{_datadir}/%{name}/plugins/svncommitemail/common
%dir %attr(0750,root,root) %{_localstatedir}/lib/%{name}/config/plugin-svncommitemail

%files plugin-svntracker
%defattr(-,root,root)
%doc plugins/svntracker/AUTHORS plugins/svntracker/COPYING plugins/svntracker/README
%dir %{_sysconfdir}/%{name}/plugins/svntracker
%verify(not md5 size mtime) %attr(0644,root,root) %{_sysconfdir}/%{name}/plugins/svntracker/config.php
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/config/plugin-svntracker
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/destroy/plugin-svntracker
%attr(0755,root,root) %{_datadir}/%{name}/config/scripts/remove/plugin-svntracker
%{_datadir}/%{name}/config/skel/plugin-svntracker
%{_datadir}/%{name}/override/plugins/svntracker
%{_datadir}/%{name}/plugins/svntracker
%{_datadir}/%{name}/www/plugins/svntracker
%attr(0750,root,root) %dir %{_localstatedir}/lib/%{name}/config/plugin-svntracker

%changelog
* Fri Oct 31 2008 Gregory Cuellar <gregory.cuellar@bull.net> 4.7.1-1.1
- Migration PHP 5, GForge 4.7, 

* Thu Jul 10 2008 Gregory Cuellar <gregory.cuellar@bull.net> 4.5.11-31.1
- Correction d'un bug pouvant empecher le commit dans SVN,
  Ajout de 'ErrorDocument 404 default' dans 'gforge-plugin-scmsvn.conf'
  Apache renvoyait le code 302 au lieu de 404 

* Thu Apr 24 2008 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-30.1
- Backup and restore SSL certificates in gforge-backup and gforge-restore
- Improved needed space detection in gforge-backup
- Add gforge-backup-wrapper to call gforge-backup without human input

* Wed Apr 02 2008 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-29.1
- Allow to change hostname in gforge-config
- Add missing % in gforge-remove title
- Correct display of cronjobs names in cronman.php

* Wed Feb 06 2008 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-28.1
- Add call to "fill_cron_arr" hook in www/admin/cronman.php
- Build arch set to binary, due to CvsGraph
- Add BuildRequires for CvsGraph: byacc, flex, gcc, make

* Tue Jan 22 2008 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-27.1
- Modify backup and restore scripts to use custom mode instead of tar mode
  and allow user to set tmp dir
- Update ViewVC to version 1.0.4
- Add CvsGraph 1.6.1
- Add a patch to ViewVC to use CvsGraph 1.6.1
- Add CvsGraph, JpGraph and ViewVC doc files (readme, license, changelog,...)
- Requires gd for CvsGraph
- Correct visual bug in ViewVC due to CSS
- Account and group names: allow dots, size up to 60 chars
- Passwords: min 6 and max 12 chars

* Mon Dec 11 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-26.1
- Modify backup and restore scripts to use pg_dump/pg_restore and better
  manage softlinks

* Mon Nov 26 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-25.1
- Remove group and user names check in plugins/scmcvs/www/acl.php

* Tue Nov 20 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-24.1
- Modify views of libnss-pgsql to create a unix group for every project,
  instead of projects using SCM.

* Wed Nov 14 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-23.1
- Add GPL v2 license
- Requires getdist >= 1.2
- Set value of trove sequence in gforge-init
- Correct check of upload directory in common/include/utils.php

* Fri Oct 12 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-22.1
- Improve gforge-backup and gforge-restore scripts
- Link use_ssl variable in cvstracker, scmcvs, scmsvn svntracker plugins config
  to sys_use_ssl global variable
- Link use_ssh variable in scmsvn plugin config to sys_use_shell global
  variable

* Wed Oct 10 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-21.1
- Add new 'mypage' hook in www/my/index.php
- Move viewvc.php from www/plugins to www/scm
- Minor modifications in gforge-[init|config|remove|destroy] scripts
- Add PHP upload temporary directory
- Allow to enable/disable GForge modules in gforge-config
- Enable SSL in Apache configuration
- Add /etc/gforge/languages-local directory for customization of language files

* Fri Jul 06 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-20.2
- Requires php-gd

* Mon Jun 11 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-20.1
- Modified package/config file
- User service command instead of calling init scripts (HOME envvar not
  reseted by httpd caused mod_dav_svn to fail)

* Wed Jun 06 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-19.1
- Moved to SVN repository
- Correct pwget version from 2.0.5 to 2.05
- Remove modification in /usr/share/gforge/www/plugins/scmcvs/acl.php
  made for novadoc and novafrs when they used SCM.
- Add sys_use_jabber variable in local.inc to avoid a PHP notice.
- Correct cronjobs/ssh_create.php
- Removed in www/include/project_home.php reference to unavailable
  rss.png file
- Remove scripts are not executed anymore at %preun
- Use scripts in initrddir directly instead of service command
- Add NameVirtualHost directive in gforge.conf Apache configuration

* Wed Apr 25 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-18
- Correct gforge-backup and gforge-restore scripts

* Mon Apr 23 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-17
- Add full www tree in override
- Exclude /usr/share/gforge/www/plugins/svntracker from gforge package,
  already owned by gforge-plugin-svntracker package
- Set permissions of config.php for cvstracker, scmcvs, scmsvn and svntracker
  plugins to root.root and 644 so that they can be included by non-Apache PHP scripts
- Modify the config and remove scripts of cvstracker, scmcvs and svntracker
  plugins to set/reset permissions of config.php to root.root and 644
- Correct the create_svn.php script of scmsvn plugin to remove w bit for other
  in every case
- Correct the create_cvs.php script of scmcvs plugin to set correctly the
  permissions of the lock directory and history file
- Correct the create_cvs.php script of scmcvs plugin to map CVS anonymous user
  with system user nobody
- Correct the create_cvs.php script of scmcvs plugin to disable system auth
  and restrict history log in the CVSROOT/config file of repositories
- Correct the cvspserver script called by xinetd to allow the available
  repositories (regresssion since RHEL 3 ?)
- Add the override directories to the PHP include_path in crontabs of
  gforge, gforge-auth-unix, gforge-plugin-scmcvs and gforge-plugin-scmsvn
- Login is not needed for CVS anonymous access, modify the display_scm_page
  of CVSPlugin.class in scmcvs
- Correct the french and base languages files of scmcvs and scmsvn

* Thu Mar 22 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-16
- Add backup and restore scripts
- Remove custom NovaForge index_std.php
- Create RPM gforge-welcome-standard and virtual package gforge-welcome

* Tue Mar 20 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-15
- Add sendmail and exim conflicts to avoid problems with newaliases not
  using the correct aliases file
- Correct sed on sshd_config in gforge-config : PAMAuthenticationViaKbdInt
  is for RHEL3 and UsePAM for newer distributions
- Update gforge-4.5.11.diff to remove several warnings in
  cronjobs/mail/mailaliases.php
- Remove modification of /etc/httpd/conf.d/php.conf (commenting server
  scope LimitRequestBody), directive already exist in
  /etc/httpd/conf.d/gforge.conf
- Remove modification of /etc/php.ini, add directives in
  /etc/httpd/conf.d/gforge.conf
- Add message in config scripts  if /usr/share/gforge/config/util/functions
  is missing

* Tue Mar 13 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-14
- Remove novaforge theme
- Add sendmail conflict to avoid problems between Postfix and Sendmail
- Add php-mbstring requirement for RHEL 4
- Remove directory /usr/share/gforge/monitor
- Spec file modifications for Aurora SPARC Linux 2.0 support
- Do not use stored value of hostname in gforge-config
- Remove x bit of crontab files
- Add missing cronjobs/mail/privatize_list.py

* Fri Feb 23 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-13
- Spec file modifications for RHEL 4 support

* Wed Feb 14 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-12
- Correction of removal scripts

* Fri Feb 02 2007 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-11
- Add destroy scripts mechanism for plugins
- Correct ViewVC roots
- Flush localization cache in gforge-config

* Wed Dec 06 2006 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-10
- Cosmetic modification to gforge-plugin-scmsvn.conf
- Improve MD5 salt for password generation

* Tue Oct 31 2006 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-9
- Correct write access to /svn
- Add write access to /projects and /users
- Add mod_auth_gforge and mod_umask requires

* Wed Oct 25 2006 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-8
- Improved CVS and SVN repositories creation and update
- Correct mod_dav_svn commit failure with new group (SIGHUP to httpd)
- Add default theme selection in gforge-config
- Update administrator in gforge-config with selected default values
- Remove email root in gforge-init
- Add subversion-python requirement to plugin-scmsvn
- Use ViewVC for both CVS and SVN
- Correct project homepage in group creation and update
- Add svncommitemail and svntracker plugins

* Fri Oct 13 2006 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-7
- Add project homepage
- Add per plugin save and restore of group_plugin and user_plugin tables
- Improved configuration scripts

* Sat Oct 06 2006 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-6
- Remove unused wiki plugin
- Move scripts and skeleton files to _datadir
- Correct cvspserver of scmcvs plugin
- Make Subversion work
- Move distribution checking from config script to pre

* Fri Sep 29 2006 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-5
- Added distribution checking before build and in config script
- Added PostgreSQL system authentication with views instead of tables
- Many, many, many improvements

* Thu Aug 24 2006 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-4
- Custom version with dependencies without version

* Wed Jul 26 2006 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-3
- Correction of maximal upload size
- Correction of Postfix configuration

* Mon Jul 10 2006 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-2
- Add aliases of 'mailman' mailing list in /etc/aliases.org

* Fri Jul 07 2006 Gilles Menigot <gilles.menigot@bull.net> 4.5.11-1
- First official release with version 4.5.11
