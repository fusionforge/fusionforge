%define dbhost			localhost
%define dbname			gforge
%define dbuser			gforge
%define dbpassword		gforge

%{!?hostname:%define hostname localhost}
%{!?sitename:%define sitename MyGForge}

%define httpduser		apache
%define gfuser			gforge
%define gfgroup			gforge

%{!?release:%define release 1}

Summary: GForge Collaborative Development Environment
Name: gforge
Version: 4.0.1
Release: %{release}
BuildArch: noarch
License: GPL
Group: Development/Tools
Source0: %{name}-%{version}.tar.bz2

Patch1000: gforge-4.0-deb_rpm.patch

AutoReqProv: off
Requires: /bin/sh, /bin/bash
Requires: perl, perl-DBI, perl-DBD-Pg, perl-HTML-Parser
Requires: httpd
Requires: php, php-mbstring, php-pgsql
Requires: postgresql, postgresql-server
URL: http://www.gforge.org/
BuildRoot: %{_tmppath}/%{name}-%{version}-root

%description
GForge is a web-based Collaborative Development Environment offering
easy access to CVS, mailing lists, bug tracking, message
boards/forums, task management, permanent file archival, and total
web-based administration.

# Macro for generating an environment variable (%1) with %2 random characters
%define randstr() %1=`perl -e 'for ($i = 0, $bit = "!", $key = ""; $i < %2; $i++) {while ($bit !~ /^[0-9A-Za-z]$/) { $bit = chr(rand(90) + 32); } $key .= $bit; $bit = "!"; } print "$key";'`

# Change password for admin user
%define changepassword() echo "UPDATE users SET user_pw='%1' WHERE user_name='admin'" | su -l postgres -s /bin/sh -c "psql %dbname" >/dev/null 2>&1

%prep
%setup
%patch1000 -p1

%build

%install
# cleaning build environment
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

# setting paths
GFORGE_DIR=$RPM_BUILD_ROOT/%{_datadir}/gforge
CACHE_DIR=$RPM_BUILD_ROOT/var/cache/gforge
UPLOAD_DIR=$RPM_BUILD_ROOT/var/lib/gforge/upload
SCM_TARBALLS_DIR=$RPM_BUILD_ROOT/var/lib/gforge/scmtarballs
HTTPD_CONF_DIR=$RPM_BUILD_ROOT/%{_sysconfdir}/httpd
GFORGE_CONF_DIR=$RPM_BUILD_ROOT/%{_sysconfdir}/gforge
GFORGE_LIB_DIR=$RPM_BUILD_ROOT/%{_libdir}/gforge
PLUGINS_DIR=$GFORGE_LIB_DIR/plugins
CROND_DIR=$RPM_BUILD_ROOT/%{_sysconfdir}/cron.d

# installing gforge
mkdir -p $GFORGE_DIR $GFORGE_LIB_DIR
for i in common cronjobs etc rpm-specific utils www ; do
	cp -rp $i $GFORGE_DIR/
done
install -m 750 setup $GFORGE_DIR/
cp -rp db $GFORGE_LIB_DIR/
cp -p deb-specific/sf-2.6-complete.sql $GFORGE_LIB_DIR/db/
mkdir -p $GFORGE_LIB_DIR/lib $GFORGE_LIB_DIR/bin
for i in deb-specific/sqlhelper.pm deb-specific/sqlparser.pm utils/include.pl ; do
	cp -p $i $GFORGE_LIB_DIR/lib/
done
for i in db-upgrade.pl register-plugin unregister-plugin register-theme unregister-theme ; do
	install -m 755 deb-specific/$i $GFORGE_LIB_DIR/bin/
done

# creating required directories
mkdir -p $UPLOAD_DIR
mkdir -p $CACHE_DIR
mkdir -p $SCM_TARBALLS_DIR
mkdir -p $PLUGINS_DIR

# configuring apache
mkdir -p $HTTPD_CONF_DIR/conf.d
install -m 644 rpm-specific/conf/vhost.conf $HTTPD_CONF_DIR/conf.d/gforge.conf

# configuring GForge
mkdir -p $GFORGE_CONF_DIR
install -m 600 rpm-specific/conf/gforge.conf $GFORGE_CONF_DIR/

# setting crontab
mkdir -p $CROND_DIR
install -m 755 rpm-specific/cron.d/gforge $CROND_DIR/

%pre
if ! id -u %gfuser >/dev/null 2>&1; then
	groupadd -r %{gfgroup}
	useradd -r -g %{gfgroup} -d %{_datadir}/gforge -s /bin/bash -c "GForge User" %{gfuser}
fi

%post
if [ "$1" -eq "1" ]; then
	# creating the database
	service postgresql status | grep '(pid' >/dev/null 2>&1 || service postgresql start
	su -l postgres -s /bin/sh -c "createdb -E UNICODE %{dbname} >/dev/null 2>&1"
	su -l postgres -s /bin/sh -c "createlang plpgsql %{dbname} >/dev/null 2>&1"

	# generating and updating site admin password
	%randstr SITEADMIN_PASSWORD 8

	echo "$SITEADMIN_PASSWORD" > %{_sysconfdir}/gforge/siteadmin.pass
	chmod 0600 %{_sysconfdir}/gforge/siteadmin.pass
	SITEADMIN_PASSWORD=`echo -n $SITEADMIN_PASSWORD | md5sum | awk '{print $1}'`

	# creating gforge database user
	%randstr GFORGEDATABASE_PASSWORD 8

	su -l postgres -c "psql -c \"CREATE USER %{dbuser} WITH PASSWORD '$GFORGEDATABASE_PASSWORD' NOCREATEUSER\" %{dbname} >/dev/null 2>&1"
	
	# updating PostgreSQL configuration
	if ! grep -i '^ *host.*%{dbname}.*' /var/lib/pgsql/data/pg_hba.conf >/dev/null 2>&1; then
		echo 'host %{dbname} %{dbuser} 127.0.0.1 255.255.255.255 md5' >> /var/lib/pgsql/data/pg_hba.conf
		service postgresql reload
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
		s/HOST_NAME/"%{hostname}"/g" %{_sysconfdir}/gforge/gforge.conf
	perl -pi -e "s/HOST_NAME/%{hostname}/g" /etc/httpd/conf.d/gforge.conf
	
	# initializing configuration
	cd %{_datadir}/gforge && ./setup -confdir %{_sysconfdir}/gforge/ -input %{_sysconfdir}/gforge/gforge.conf -noapache >/dev/null 2>&1
	
	# creating the database
	su -l %{gfuser} -c "%{_libdir}/gforge/bin/db-upgrade.pl >/dev/null 2>&1"
	su -l postgres -c "psql -c 'UPDATE groups SET register_time=EXTRACT(EPOCH FROM NOW());' %{dbname} >/dev/null 2>&1"
	%changepassword $SITEADMIN_PASSWORD

	service httpd graceful >/dev/null 2>&1
else
	# upgrading database
	su -l %{gfuser} -c "%{_libdir}/gforge/bin/db-upgrade.pl >/dev/null 2>&1"

	# updating configuration
        cd %{_datadir}/gforge && ./setup -confdir %{_sysconfdir}/gforge/ -input %{_sysconfdir}/gforge/gforge.conf -noapache >/dev/null 2>&1
fi

%postun
if [ "$1" -eq "0" ]; then
	# dropping gforge database
	#su -l postgres -s /bin/sh -c "dropuser %{dbuser} >/dev/null 2>&1 ; dropdb %{dbname} >/dev/null 2>&1"
	
	for file in siteadmin.pass local.pl httpd.secrets local.inc httpd.conf httpd.vhosts database.inc ; do
		rm -f %{_sysconfdir}/gforge/$file
	done
	# Remove PostgreSQL access
	if grep -i '^ *host.*%{dbname}.*' /var/lib/pgsql/data/pg_hba.conf >/dev/null 2>&1; then
		perl -ni -e 'm@^ *host.*%{dbname}.*@ or print;' /var/lib/pgsql/data/pg_hba.conf >/dev/null 2>&1
		perl -ni -e 'm@^ *local.*%{dbname}.*@ or print;' /var/lib/pgsql/data/pg_hba.conf >/dev/null 2>&1
	fi
	# Remove user/group
	if id -u %{gfuser} >/dev/null 2>&1; then
		userdel %{gfuser} >/dev/null 2>&1
		groupdel %{gfgroup} >/dev/null 2>&1
	fi
	exit 0
fi

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
%doc AUTHORS AUTHORS.sourceforge COPYING ChangeLog INSTALL README*
%doc docs/*
%attr(0660, apache, gforge) %config(noreplace) %{_sysconfdir}/gforge/gforge.conf
%attr(0640, apache, apache) %config(noreplace) %{_sysconfdir}/httpd/conf.d/gforge.conf
%attr(0775, apache, apache) %dir /var/lib/gforge/upload
%{_datadir}/gforge
%{_libdir}/gforge
%{_sysconfdir}/cron.d/gforge
/var/cache/gforge
/var/lib/gforge/scmtarballs

%changelog
* Wed Nov 03 2004 Guillaume Smet <guillaume-gforge@smet.org>
- new RPM packaging
