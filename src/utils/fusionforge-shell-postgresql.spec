#%define dbhost			localhost
%define dbname			gforge
#%define dbuser			gforge
#%define plugin          scmcvs
%{!?release:%define release 1}

Summary: collaborative development tool - shell accounts (using PostgreSQL)
Name: fusionforge-shell-postgresql
Version: 4.8.2
Release: %{release}
BuildArch: noarch
License: GPL
Group: Development/Tools
Source: %{name}-%{version}.tar.bz2
AutoReqProv: off
Requires: fusionforge >= 4.8
Requires: libnss-pgsql >= 1.4
Requires: nscd
#Requires: perl perl-URI

URL: http://www.fusionforge.org/
BuildRoot: %{_tmppath}/%{name}-%{version}-root

#%define gfuser                  gforge
#%define gfgroup                 gforge

#Requires: perl-IPC-Run

#Globals defines for gforge
%define GFORGE_DIR              %{_datadir}/gforge
%define SBIN_DIR		%{_sbindir}
%define CROND_DIR               %{_sysconfdir}/cron.d
%define GFORGE_CONF_DIR         %{_sysconfdir}/gforge

%define startnscd() service nscd status | grep '(pid' >/dev/null 2>&1 || service nscd start
%define nscdonstart() chkconfig nscd on

%description
GForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.

This package provides shell accounts authenticated via the PostGreSQL
database to GForge users.

%prep
%setup

%build

%install
# cleaning build environment
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

# creating required directories
install -m 755 -d $RPM_BUILD_ROOT/%{GFORGE_DIR}/utils
install -m 755 install-nsspgsql.sh $RPM_BUILD_ROOT/%{GFORGE_DIR}/utils/install-nsspgsql.sh

# installing configuration file
# rien pour le moment

%pre

%post
if [ "$1" = "1" ] ; then
	
	# configuring gforge
	perl -pi -e "
		s/^sys_account_manager_type=.*/sys_account_manager_type=pgsql/g" %{GFORGE_CONF_DIR}/gforge.conf

	# creating gforge database user
	#GFORGEDATABASE_PASSWORD=$(grep ^db_password= %{GFORGE_CONF_DIR}/gforge.conf | cut -d= -f2-)
	#su -l postgres -c "psql -c \"CREATE USER gforge_nss WITH PASSWORD '$GFORGEDATABASE_PASSWORD' NOCREATEUSER\" %{dbname} >/dev/null 2>&1"
	
	# updating PostgreSQL configuration
	#if ! grep -i '^ *host.*gforge_nss.*' /var/lib/pgsql/data/pg_hba.conf >/dev/null 2>&1; then
	#	echo 'host %{dbname} gforge_nss 127.0.0.1 255.255.255.255 trust' >> /var/lib/pgsql/data/pg_hba.conf
	#	%reloadpostgresql
	#fi
	
	#Configuration de libnss-pgsql
	ln -s %{GFORGE_DIR}/utils/install-nsspgsql.sh %{SBIN_DIR}/
	install-nsspgsql.sh setup

	%startnscd
        %nscdonstart

	#if plugin scmcvs is installed, comment the cron usergroup.php
	if [ ! "$(rpm -qa fusionforge-plugin-scmcvs)" = "" ]; then
		#echo "plugin scmcvs installed"
		if [ "$(grep 'usergroup.php' %{CROND_DIR}/fusionforge-plugin-scmcvs | grep '#')" = "" ]; then
			#echo "I comment the cron if it is un comment"
			sed -i "s/^\(.*usergroup.php.*\)/#\1/" %{CROND_DIR}/fusionforge-plugin-scmcvs
		fi
	fi
else
        # upgrade
        :
fi

%postun
if [ "$1" = "0" ] ; then
        #reconfiguration de gforge
	#suppression des fichiers de conf créés par install-nsspgsql.sh
        #suppression du user gforge_nss
        #suppression de gforge_nss 127.0.0.1 255.255.255.255 trust dans /var/lib/pgsql/data/pg_hba.conf
	#activation du cron usergroup.php
else
        # upgrade
        :
fi

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
#%doc AUTHORS COPYING README
%{GFORGE_DIR}/utils/install-nsspgsql.sh

%changelog
* Mon Feb 13 2009 Alexandre NEYMANN <alexandre.neymann@dgfip.finances.gouv.fr>
- 4.7.1
Initial RPM packaging
