%define bzip 1
%define gfuser gforge
%define gfgroup gforge

Summary: Collaborative Development Tool
Name: gforge
Version: 3.1
Release: 1
BuildArch: noarch
Copyright: GPL
Group: Development/Tools
%if %{bzip}
Source0: http://gforge.org/download.php/44/%{name}-%{version}.tar.bz2
%else
Source0: %{name}-%{version}.tar.gz
%endif
Source1: gforge.conf
Patch1000: gforge-3.0-local_config.patch
Patch1001: gforge-3.0-php_path.patch
Patch1002: gforge-3.0-init_sql.patch
Patch1003: gforge-3.0-cronjobs.patch
AutoReqProv: off
Requires: /bin/sh /bin/bash /usr/bin/perl /usr/bin/php
Requires: /usr/bin/postmaster /usr/lib/php4/pgsql.so
URL: http://www.gforge.org/
BuildRoot: /var/tmp/%{name}-%{version}-root

%description
GForge is a web-based Collaborative Development Environment offering
easy access to CVS, mailing lists, bug tracking, message
boards/forums, task management, permanent file archival, and total
web-based administration.

NOTE:  After installing this RPM, you will have a working GForge site
at http://localhost/.  However, everything is set up to work locally,
so if you are installing a site-wide instance of GForge, be sure to
customize /etc/gforge/local.inc before use!

# Macro for generating an environment variable (%1) with %2 random characters
%define randstr() %1=`perl -e 'for ($i = 0, $bit = "!", $key = ""; $i < %2; $i++) {while ($bit !~ /^[0-9A-Za-z]$/) { $bit = chr(rand(90) + 32); } $key .= $bit; $bit = "!"; } print "$key";'`

# Change password for "siteadmin"
%define chpass() echo "UPDATE users SET user_pw = '%1' WHERE user_name = 'siteadmin'" | su -l postgres -s /bin/sh -c "psql alexandria" >/dev/null 2>&1

%prep
%setup
%patch1000 -p1
%patch1001 -p1
%patch1002 -p1
%patch1003 -p1

%build

%install
rm -rf $RPM_BUILD_ROOT

mkdir -p $RPM_BUILD_ROOT%{_datadir}/%{name}
for i in backend common cronjobs db monitor utils www ; do
    cp -rp $i $RPM_BUILD_ROOT%{_datadir}/%{name}/
done
mkdir $RPM_BUILD_ROOT%{_datadir}/%{name}/www/incoming

mkdir -p $RPM_BUILD_ROOT/etc/httpd/conf.d
install -m 644 %{SOURCE1} $RPM_BUILD_ROOT/etc/httpd/conf.d/%{name}.conf

mkdir -p $RPM_BUILD_ROOT/etc/%{name}
install -m 600 etc/local.inc $RPM_BUILD_ROOT/etc/%{name}/

mkdir -p $RPM_BUILD_ROOT/etc/cron.daily $RPM_BUILD_ROOT/etc/cron.hourly
install -m 755 utils/gforge-nightly-cronjobs.sh $RPM_BUILD_ROOT/etc/cron.daily/
install -m 755 utils/gforge-hourly-cronjobs.sh $RPM_BUILD_ROOT/etc/cron.hourly/

%pre
if ! id -u %gfuser >/dev/null 2>&1; then
    groupadd -r %{gfgroup}
    useradd -r -g %{gfgroup} -d %{_datadir}/%{name} -s /bin/false -c "GForge User" %{gfuser}
fi

%post
if [ $1 -eq 1 ]; then
    # Initial install.  Create and populate DB.
    service postgresql status | grep 'is running' >/dev/null 2>&1 || service postgresql start
    su -l postgres -s /bin/sh -c "createdb alexandria >/dev/null 2>&1"
    su -l postgres -s /bin/sh -c "psql alexandria < %{_datadir}/%{name}/db/%{name}3.sql >/tmp/gforge.log 2>&1"
    %randstr GFPASS 8

    (echo $GFPASS ; echo $GFPASS) | su -l postgres -s /bin/sh -c "createuser -D -a -P -E gforge >/dev/null 2>&1"
    %randstr SAPASS 8

    echo "$SAPASS" > /etc/%{name}/siteadmin.pass
    chmod 0600 /etc/%{name}/siteadmin.pass
    SAPASS=`echo -n $SAPASS | md5sum | awk '{print $1}'`
    %chpass $SAPASS

    # Update apache config
    if test -f /etc/httpd/conf/httpd.conf; then
        if ! grep 'Include .*%{name}.conf' /etc/httpd/conf/httpd.conf >/dev/null 2>&1; then
            echo '# Added by %{name} package' >> /etc/httpd/conf/httpd.conf
            echo "Include /etc/httpd/conf.d/%{name}.conf" >> /etc/httpd/conf/httpd.conf
            service httpd restart >/dev/null 2>&1
        fi
    fi

    # Update PHP config
    if grep -i '^ *register_globals *=' /etc/php.ini >/dev/null 2>&1; then
        if ! grep -i '^ *register_globals *= *on' /etc/php.ini >/dev/null 2>&1; then
            perl -pi.pkgsave.%{name} -e 's/^\s*register_globals\s*=.*$/register_globals = On/gi;' /etc/php.ini
        fi
    else
        echo 'register_globals = On' >> /etc/php.ini
    fi
    if grep -i '^ *include_path *=' /etc/php.ini >/dev/null 2>&1; then
        if ! grep -i '^ *include_path *=.*%{name}' /etc/php.ini >/dev/null 2>&1; then
            perl -pi.pkgsave.%{name} -e 's@^\s*include_path\s*=.*$@include_path = ".:%{_datadir}/%{name}/:%{_datadir}/%{name}/www/include/"@gi;' /etc/php.ini
        fi
    else
        echo 'include_path = ".:%{_datadir}/%{name}/:%{_datadir}/%{name}/www/include/"' >> /etc/php.ini
    fi

    # Update PostgreSQL config
    if ! grep -i '^ *host.*alexandria.*' /var/lib/pgsql/data/pg_hba.conf >/dev/null 2>&1; then
        echo 'host alexandria 127.0.0.1 255.255.255.255 md5' >> /var/lib/pgsql/data/pg_hba.conf
        service postgresql restart
    fi

    # Add "noreply" alias
    for i in /etc/postfix/aliases /etc/mail/aliases /etc/aliases ; do
        if [ -f $i ]; then
            if ! grep -i '^ *noreply:' $i >/dev/null 2>&1; then
                echo 'noreply: /dev/null' >> $i
                newaliases
            fi
            break
        fi
    done

    # Generate random session ID
    %randstr SESSID 32

    perl -pi -e "s/DBPASSHERE/$GFPASS/g; s/RANDOMIDHERE/$SESSID/g;" /etc/%{name}/local.inc

else
    # Upgrade
    :
fi

%postun
if [ $1 -eq 0 ]; then
    # Uninstall everything
    su -l postgres -s /bin/sh -c "dropuser gforge ; dropdb alexandria"
    rm -f /etc/%{name}/siteadmin.pass

    # Remove apache config
    if test -f /etc/httpd/conf/httpd.conf; then
        if grep '^ *Include /etc/httpd/conf.d/%{name}.conf' /etc/httpd/conf/httpd.conf > /dev/null; then
            perl -ni -e 'm@^\# Added by %{name}.*$@ or m@^ *Include /etc/httpd/conf.d/%{name}.conf@ or print;' /etc/httpd/conf/httpd.conf
            service httpd restart
        fi
    fi

    # Remove PHP include path
    if grep -i '^ *include_path *=.*%{name}' /etc/php.ini >/dev/null 2>&1; then
        perl -ni -e 'm@^ *include_path *=.*%{name}@ or print;' /etc/php.ini
    fi

    # Remove PostgreSQL access
    if grep -i '^ *host.*alexandria.*' /var/lib/pgsql/data/pg_hba.conf >/dev/null 2>&1; then
        perl -ni -e 'm@^ *host.*alexandria.*@ or print;' /var/lib/pgsql/data/pg_hba.conf
    fi

    # Remove user/group
    if id -u %gfuser >/dev/null 2>&1; then
        userdel %gfuser
        groupdel %gfgroup 2>/dev/null || :
    fi

else
    # Upgrade
    :
fi

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
%doc AUTHORS COPYING ChangeLog INSTALL README*
%doc docs/*
%attr(0600, apache, apache) %config(noreplace) /etc/%{name}/local.inc
%attr(0640, apache, apache) %config(noreplace) /etc/httpd/conf.d/%{name}.conf
%attr(0775, apache, apache) %dir %{_datadir}/%{name}/www/incoming
%{_datadir}/%{name}/backend
%{_datadir}/%{name}/common
%{_datadir}/%{name}/cronjobs
%{_datadir}/%{name}/db
%{_datadir}/%{name}/monitor
%{_datadir}/%{name}/utils
%{_datadir}/%{name}/www
/etc/cron.daily/gforge-nightly-cronjobs.sh
/etc/cron.hourly/gforge-hourly-cronjobs.sh

%changelog
