%define dbhost			localhost
%define dbname			gforge
%define dbuser			gforge

%if %{?hostname:0}%{!?hostname:1}
	%define hostname localhost
%endif
%if %{?sitename:0}%{!?sitename:1}
	%define sitename MyForge
%endif
%if %{?adminemail:0}%{!?adminemail:1}
	%if "%hostname" == "localhost"
		%define adminemail root@localhost.localdomain
	%else
		%define adminemail root@%hostname
	%endif
%endif
%{!?release:%define release 1}

%define gfuser			gforge
%define gfgroup			gforge

Summary: GForge Collaborative Development Environment
Name: gforge
Version: 4.1
Release: %{release}
BuildArch: noarch
License: GPL
Group: Development/Tools
Source0: %{name}-%{version}.tar.bz2
URL: http://www.gforge.org/
BuildRoot: %{_tmppath}/%{name}-%{version}-root

Patch1000: gforge-4.0-deb_rpm.patch

AutoReqProv: off
Requires: /bin/sh, /bin/bash
Requires: perl, perl-DBI, perl-HTML-Parser
Requires: gforge-lib-jpgraph

# RedHat specific - distribution specific (fc = Fedora Core (or RHEL4 and Centos 4) - rh9 = RHL 9 - el3 = RHEL 3 or CentOS 3)
%if "%{_vendor}" == "redhat"
	%if %{?dist:0}%{!?dist:1}
		%define dist fc
	%endif
	
	%define httpduser		apache
	%define httpdgroup		apache
	%define httpddir		httpd

Requires: httpd
Requires: perl-DBD-Pg, php-pgsql
	
	%if "%{dist}" == "fc" 
Requires: php-mbstring
	%endif
	%if "%{dist}" == "el3"
Requires: rh-postgresql, rh-postgresql-server
		%define postgresqlservice rhdb
	%else
Requires: postgresql, postgresql-server
		%define postgresqlservice postgresql
	%endif
	
	%define startpostgresql() service %postgresqlservice status | grep '(pid' >/dev/null 2>&1 || service %postgresqlservice start
	%define reloadpostgresql() service %postgresqlservice reload
	%define gracefulhttpd() service httpd graceful >/dev/null 2>&1
%endif

# SuSE specific
%if "%{_vendor}" == "suse"
	%define httpduser		wwwrun
	%define httpdgroup		www
	%define httpddir		apache2
	
Requires: postgresql, postgresql-server
Requires: pgperl, jpeg
Requires: php5
Requires: php5-pgsql, php5-mbstring

		# Start the postgresql service if needed
		%define startpostgresql() /etc/init.d/postgresql status | grep 'running' >/dev/null 2>&1 || /etc/init.d/postgresql start
		%define reloadpostgresql() /etc/init.d/postgresql reload >/dev/null 2>&1
		%define gracefulhttpd() /etc/init.d/httpd graceful >/dev/null 2>&1
%endif

# Mandrake specific
%if "%{_vendor}" == "MandrakeSoft"
	%define httpduser		apache
	%define httpdgroup		apache
	%define httpddir		httpd
	%define postgresqlservice postgresql
	
Requires: php-mbstring, webserver
Requires: postgresql, postgresql-server
Requires: perl-DBD-Pg, php-pgsql

	%define startpostgresql() service %postgresqlservice status | grep '(pid' >/dev/null 2>&1 || service %postgresqlservice start
	%define reloadpostgresql() service %postgresqlservice reload
	%define gracefulhttpd() service httpd graceful >/dev/null 2>&1
%endif

%description
GForge is a web-based Collaborative Development Environment offering
easy access to CVS, mailing lists, bug tracking, message
boards/forums, task management, permanent file archival, and total
web-based administration.

# Macro for generating an environment variable (%1) with %2 random characters
%define randstr() %1=`perl -e 'for ($i = 0, $bit = "!", $key = ""; $i < %2; $i++) {while ($bit !~ /^[0-9A-Za-z]$/) { $bit = chr(rand(90) + 32); } $key .= $bit; $bit = "!"; } print "$key";'`

# Change password for admin user
%define changepassword() echo "UPDATE users SET user_pw='%1', email='%{adminemail}' WHERE user_name='admin'" | su -l postgres -s /bin/sh -c "psql %dbname" >/dev/null 2>&1

%define GFORGE_DIR		%{_datadir}/gforge
%define GFORGE_CONF_DIR		%{_sysconfdir}/gforge
%define GFORGE_LANG_DIR		%{GFORGE_CONF_DIR}/languages-local
%define GFORGE_LIB_DIR		%{_libdir}/gforge/lib
%define GFORGE_DB_DIR		%{_libdir}/gforge/db
%define GFORGE_BIN_DIR		%{_libdir}/gforge/bin
%define PLUGINS_LIB_DIR		%{_libdir}/gforge/plugins
%define PLUGINS_CONF_DIR	%{GFORGE_CONF_DIR}/plugins
%define CACHE_DIR		/var/cache/gforge
%define UPLOAD_DIR		/var/lib/gforge/upload
%define SCM_TARBALLS_DIR	/var/lib/gforge/scmtarballs
%define SCM_SNAPSHOTS_DIR	/var/lib/gforge/scmsnapshots
%define CROND_DIR		/%{_sysconfdir}/cron.d
%define HTTPD_CONF_DIR		/%{_sysconfdir}/%{httpddir}
%define SBIN_DIR		%{_sbindir}

%prep
%setup
%patch1000 -p1

%build

%install
# cleaning build environment
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

# creating required directories
install -m 755 -d $RPM_BUILD_ROOT/%{GFORGE_DIR}
install -m 755 -d $RPM_BUILD_ROOT/%{GFORGE_CONF_DIR}
install -m 755 -d $RPM_BUILD_ROOT/%{GFORGE_LANG_DIR}
install -m 755 -d $RPM_BUILD_ROOT/%{GFORGE_BIN_DIR}
install -m 755 -d $RPM_BUILD_ROOT/%{GFORGE_LIB_DIR}
install -m 755 -d $RPM_BUILD_ROOT/%{UPLOAD_DIR}
install -m 755 -d $RPM_BUILD_ROOT/%{CACHE_DIR}
install -m 755 -d $RPM_BUILD_ROOT/%{SCM_TARBALLS_DIR}
install -m 755 -d $RPM_BUILD_ROOT/%{PLUGINS_LIB_DIR}

install -m 755 -d $RPM_BUILD_ROOT/%{SBIN_DIR}
install -m 755 -d $RPM_BUILD_ROOT/%{HTTPD_CONF_DIR}/conf.d
install -m 755 -d $RPM_BUILD_ROOT/%{CROND_DIR}

# installing gforge
for i in common cronjobs etc rpm-specific utils www ; do
	cp -rp $i $RPM_BUILD_ROOT/%{GFORGE_DIR}/
done
install -m 750 setup $RPM_BUILD_ROOT/%{GFORGE_DIR}/
chmod 755 $RPM_BUILD_ROOT/%{GFORGE_DIR}/utils/fill-in-the-blanks.pl

cp -rp db/. $RPM_BUILD_ROOT/%{GFORGE_DB_DIR}/
cp -p deb-specific/sf-2.6-complete.sql $RPM_BUILD_ROOT/%{GFORGE_DB_DIR}/

for i in deb-specific/sqlhelper.pm deb-specific/sqlparser.pm utils/include.pl ; do
	cp -p $i $RPM_BUILD_ROOT/%{GFORGE_LIB_DIR}/
done
for i in db-upgrade.pl register-plugin unregister-plugin register-theme unregister-theme ; do
	install -m 755 deb-specific/$i $RPM_BUILD_ROOT/%{GFORGE_BIN_DIR}/
done

# configuring apache
install -m 644 rpm-specific/conf/vhost.conf $RPM_BUILD_ROOT/%{HTTPD_CONF_DIR}/conf.d/gforge.conf

# configuring GForge
install -m 600 rpm-specific/conf/gforge.conf $RPM_BUILD_ROOT/%{GFORGE_CONF_DIR}/
install -m 750 rpm-specific/scripts/gforge-config $RPM_BUILD_ROOT/%{SBIN_DIR}/
if ls rpm-specific/languages/*.tab &> /dev/null; then
	cp rpm-specific/languages/*.tab $RPM_BUILD_ROOT/%{GFORGE_LANG_DIR}/
fi
cp -rp rpm-specific/custom $RPM_BUILD_ROOT/%{GFORGE_CONF_DIR}

# setting crontab
install -m 664 rpm-specific/cron.d/gforge $RPM_BUILD_ROOT/%{CROND_DIR}/

%pre
%startpostgresql
if su -l postgres -s /bin/sh -c 'psql template1 -c "SHOW tcpip_socket;"' | grep " off" &> /dev/null; then
	echo "###"
	echo "# You should set tcpip_socket = true in your /var/lib/pgsql/data/postgresql.conf"
	echo "# before installing GForge and restart PostgreSQL."
	echo "# Then you should be able to install GForge RPM."
	echo "###"
	exit 1
fi
if ! id -u %gfuser >/dev/null 2>&1; then
	groupadd -r %{gfgroup}
	useradd -r -g %{gfgroup} -d %{GFORGE_DIR} -s /bin/bash -c "GForge User" %{gfuser}
fi

%post
if [ "$1" -eq "1" ]; then
	# creating the database
	%startpostgresql
	su -l postgres -s /bin/sh -c "createdb -E UNICODE %{dbname} >/dev/null 2>&1"
	su -l postgres -s /bin/sh -c "createlang plpgsql %{dbname} >/dev/null 2>&1"

	# generating and updating site admin password
	%randstr SITEADMIN_PASSWORD 8

	echo "$SITEADMIN_PASSWORD" > %{GFORGE_CONF_DIR}/siteadmin.pass
	chmod 0600 %{GFORGE_CONF_DIR}/siteadmin.pass
	SITEADMIN_PASSWORD=`echo -n $SITEADMIN_PASSWORD | md5sum | awk '{print $1}'`

	# creating gforge database user
	%randstr GFORGEDATABASE_PASSWORD 8

	su -l postgres -c "psql -c \"CREATE USER %{dbuser} WITH PASSWORD '$GFORGEDATABASE_PASSWORD' NOCREATEUSER\" %{dbname} >/dev/null 2>&1"
	su -l postgres -c "psql -c \"CREATE USER gforge_nss WITH PASSWORD '$GFORGEDATABASE_PASSWORD' NOCREATEUSER\" %{dbname} >/dev/null 2>&1"
	su -l postgres -c "psql -c \"CREATE USER gforge_mta WITH PASSWORD '$GFORGEDATABASE_PASSWORD' NOCREATEUSER\" %{dbname} >/dev/null 2>&1"
	
	# updating PostgreSQL configuration
	if ! grep -i '^ *host.*%{dbname}.*' /var/lib/pgsql/data/pg_hba.conf >/dev/null 2>&1; then
		echo 'host %{dbname} %{dbuser} 127.0.0.1 255.255.255.255 md5' >> /var/lib/pgsql/data/pg_hba.conf
		echo 'local %{dbname} gforge_nss md5' >> /var/lib/pgsql/data/pg_hba.conf
		echo 'local %{dbname} gforge_mta md5' >> /var/lib/pgsql/data/pg_hba.conf
		%reloadpostgresql
	fi

	# adding "noreply" alias
	for i in /etc/postfix/aliases /etc/mail/aliases /etc/aliases ; do
		if [ -f $i ]; then
			if ! grep -i '^ *noreply:' $i >/dev/null 2>&1; then
				echo 'noreply: /dev/null' >> $i
				newaliases
			fi
			break
		fi
	done

	# generating random session ID
	%randstr SESSID 32

	# replacing variables in configuration files
	perl -pi -e "
		s/DB_HOST/"%{dbhost}"/g;
		s/DB_NAME/"%{dbname}"/g;
		s/DB_USER/"%{dbuser}"/g;
		s/DB_PASSWORD/"$GFORGEDATABASE_PASSWORD"/g;
		s/SYSTEM_NAME/"%{sitename}"/g;
		s/RANDOM_ID/"$SESSID"/g;
		s/HOST_NAME/"%{hostname}"/g" %{GFORGE_CONF_DIR}/gforge.conf
	perl -pi -e "s/HOST_NAME/%{hostname}/g" %{HTTPD_CONF_DIR}/conf.d/gforge.conf
	
	# initializing configuration
	%{SBIN_DIR}/gforge-config
	
	# creating the database
	su -l %{gfuser} -c "%{GFORGE_BIN_DIR}/db-upgrade.pl 2>&1" | grep -v ^NOTICE
	su -l postgres -c "psql -c 'UPDATE groups SET register_time=EXTRACT(EPOCH FROM NOW());' %{dbname} >/dev/null 2>&1"
	%changepassword $SITEADMIN_PASSWORD

	%gracefulhttpd
else
	# upgrading database
	su -l %{gfuser} -c "%{GFORGE_BIN_DIR}/db-upgrade.pl 2>&1" | grep -v ^NOTICE

	# updating configuration
	%{SBIN_DIR}/gforge-config || :
fi

%postun
if [ "$1" -eq "0" ]; then
	# dropping gforge users
	su -l postgres -s /bin/sh -c "dropuser %{dbuser} >/dev/null 2>&1 ; dropuser gforge_nss >/dev/null 2>&1 ; dropuser gforge_mta >/dev/null 2>&1"
	
	for file in siteadmin.pass local.pl httpd.secrets local.inc httpd.conf httpd.vhosts database.inc ; do
		rm -f %{GFORGE_CONF_DIR}/$file
	done
	# Remove PostgreSQL access
	if grep -i '^ *host.*%{dbname}.*' /var/lib/pgsql/data/pg_hba.conf >/dev/null 2>&1; then
		perl -ni -e 'm@^ *host.*%{dbname}.*@ or print;' /var/lib/pgsql/data/pg_hba.conf >/dev/null 2>&1
		perl -ni -e 'm@^ *local.*%{dbname}.*@ or print;' /var/lib/pgsql/data/pg_hba.conf >/dev/null 2>&1
	fi
	# Remove user/group
	if id -u %{gfuser} >/dev/null 2>&1; then
		userdel %{gfuser} >/dev/null 2>&1
		groupdel %{gfgroup} 2>/dev/null || :
	fi
fi

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
%doc AUTHORS AUTHORS.sourceforge COPYING ChangeLog INSTALL README*
%doc docs/*
%attr(0660, %{httpduser}, gforge) %config(noreplace) %{GFORGE_CONF_DIR}/gforge.conf
%attr(0750, root, root) %{SBIN_DIR}/gforge-config
%attr(0640, %{httpduser}, %{httpdgroup}) %config(noreplace) %{HTTPD_CONF_DIR}/conf.d/gforge.conf
%attr(0664, root, root) %config(noreplace) %{CROND_DIR}/gforge
%attr(0775, %{httpduser}, %{httpdgroup}) %dir %{UPLOAD_DIR}
%attr(0775, %{httpduser}, %{httpdgroup}) %dir %{CACHE_DIR}
%{GFORGE_DIR}
%{GFORGE_BIN_DIR}
%{GFORGE_LIB_DIR}
%{GFORGE_DB_DIR}
%{GFORGE_LANG_DIR}
%{GFORGE_CONF_DIR}/custom
%{SCM_TARBALLS_DIR}

%changelog
* Wed Jun 29 2005 Open Wide <guillaume.smet@openwide.fr>
- fixed Xavier's patch
- added Mandrake support based on patch [#1194] by Kevin R. Bulgrien
* Wed Apr 27 2005 Rameau Xavier <xrameau@gmail.com> (for e-LaSer : http://www.e-laser.fr)
- Adding specification for SuSE Linux Enterprise Server 9 (in .spec)
- Moving all static definitions to global variables (in .spec)
* Thu Mar 03 2005 Guillaume Smet <guillaume-gforge@smet.org>
- removed useless stuff thanks to Christian's work on db-upgrade.pl
- s/refresh.sh/gforge-config to improve consistency with debian packaging
- it's better to display the output of db-upgrade.pl
* Sun Feb 20 2005 Guillaume Smet <guillaume-gforge@smet.org>
- added a dependency on gforge-lib-jpgraph
- added gforge-4.1-project_task_sql.patch
* Sat Feb 19 2005 Guillaume Smet <guillaume-gforge@smet.org>
- 4.1
- forced the vhost on port 80
- modified the db-upgrade.pl patch to keep nss stuff
- detects if tcpip_socket is set to true before installing the RPM
- fixed dependencies problem for RH9 and RHEL3
- creates gforge_nss and gforge_mta postgresql users
- drops created postgresql users on uninstall
- replaced -f test with ls
* Fri Jan 28 2005 Thales Information Systems <guillaume.smet@openwide.fr>
- fixed default values for release, sitename and hostname
- fixed remaining issues on upgrade
* Thu Jan 27 2005 Thales Information Systems <guillaume.smet@openwide.fr>
- it's now possible to add custom stuff in /etc/gforge/custom/
* Thu Dec 30 2004 Guillaume Smet <guillaume-gforge@smet.org>
- added Allow from all in vhost config
* Wed Dec 29 2004 Guillaume Smet <guillaume-gforge@smet.org>
- added the magic_quotes_gpc On in vhost as the default value for FC3 is now Off
* Sat Dec 25 2004 Guillaume Smet <guillaume-gforge@smet.org>
- it's now possible to add specific language files in the RPM
* Fri Dec 03 2004 Dassault Aviation <guillaume.smet@openwide.fr>
- fixed the vhost configuration
- fixed the default crontab
- the crontab is now a config file and is not replaced on update
- added refresh.sh in /etc/gforge/ to refresh the configuration easily
* Wed Nov 03 2004 Guillaume Smet <guillaume-gforge@smet.org>
- new RPM packaging
