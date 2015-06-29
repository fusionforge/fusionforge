#
# RPM spec file for FusionForge
#
# Initial work by Jesse Becker <jbecker@northwestern.edu>
# Reworked for 1.5.x by Alain Peyrat <aljeux@free.fr>
#
# Copyright (C) 2014 Alain Peyrat
#

# Global Definitions
%define WIKI_NAME       PhpWiki
%define ADMIN_USER      admin
%define ADMIN_PASSWD    myadmin

%define DB_NAME         phpwiki
%define DB_USER         phpwiki
%define DB_PASSWD       phpwikipw

%define httpduser       apache
%define httpdgroup      apache

%define ACCESS_LOG      %{_var}/log/%{name}/%{name}_access.log
%define DATABASE_TYPE	SQL
%define DATABASE_DSN	mysql://%{admin_user}:%{admin_passwd}
%define DEBUG		0
%define USER_AUTH_ORDER	"PersonalPage"
%define LDAP_AUTH_USER	""
%define LDAP_AUTH_PASSWORD	""
%define LDAP_SEARCH_FIELD	""
%define IMAP_AUTH_HOST	""
%define POP3_AUTH_HOST	""
%define AUTH_USER_FILE	""
%define AUTH_SESS_USER	""
%define AUTH_SESS_LEVEL	""
%define AUTH_GROUP_FILE	""

# Disable debug binary detection & generation to speed up process.
%global debug_package %{nil}

# RPM spec preamble
Summary: PHP-based Wiki webapplication
Name: phpwiki
Version: 1.5.4
Release: 1
BuildArch: noarch
License: GPL
Group: Applications/Internet
Source: http://easynews.dl.sourceforge.net/sourceforge/phpwiki/%{name}-%{version}.tar.gz
URL: http://sourceforge.net/projects/phpwiki/
BuildRoot: %{_tmppath}/%{name}-%{version}-%{release}-root
Packager: Alain Peyrat <aljeux@free.fr>

#Relocation!
Prefix: /var/www

Requires: httpd, php, php-pear, php-mysql

Autoreq: 0

%define dest %{buildroot}/%{prefix}/%{name}

%description
PhpWiki is a WikiWikiWeb clone in PHP. A WikiWikiWeb is a site where
anyone can edit the pages through an HTML form. Multiple storage
backends, dynamic hyperlinking, themeable, scriptable by plugins, full
authentication, ACL's.

%prep
%setup

%install
%{__rm} -rf %{buildroot}

%{__install} -m 755 -d %{buildroot}%{_var}/log/phpwiki

%{__mkdir} -p %{dest}
%{__cp} -r config lib locale pgsrc themes schemas uploads %{dest}
%{__cp} favicon.ico *.php wiki %{dest}

cd %{dest}/config
perl -p	\
	-e 's,^(WIKI_NAME)\s*=.*,$1 = %{WIKI_NAME},;'	\
	-e 's,^[;\s]*(ADMIN_USER)\s*=.*,$1 = %{ADMIN_USER},;'	\
	-e 's,^[;\s]*(ADMIN_PASSWD)\s*=.*,$1 = %{ADMIN_PASSWD},;'	\
	-e 's,^[;\s]*(ACCESS_LOG)\s*=.*,$1 = %{ACCESS_LOG},;'	\
	-e 's,^[;\s]*(DATABASE_TYPE)\s*=.*,$1 = %{DATABASE_TYPE},;'	\
	-e 's,^[;\s]*(DATABASE_DSN)\s*=.*,$1 = mysql://%{DB_USER}:%{DB_PASSWD}\@localhost/%{DB_NAME},;'	\
	-e 's,^[;\s]*(DEBUG)\s*=.*,$1 = %{DEBUG},;'	\
	-e 's,^[;\s]*(USER_AUTH_ORDER)\s*=.*,$1 = %{USER_AUTH_ORDER},;'	\
	-e 's,^[;\s]*(USER_AUTH_ORDER)\s*=.*,$1 = %{USER_AUTH_ORDER},;'	\
	-e 's,^[;\s]*(LDAP_AUTH_USER)\s*=.*,$1 = %{LDAP_AUTH_USER},;'	\
	-e 's,^[;\s]*(LDAP_AUTH_PASSWORD)\s*=.*,$1 = %{LDAP_AUTH_PASSWORD},;'	\
	-e 's,^[;\s]*(LDAP_SEARCH_FIELD)\s*=.*,$1 = %{LDAP_SEARCH_FIELD},;'	\
	-e 's,^[;\s]*(IMAP_AUTH_HOST)\s*=.*,$1 = %{IMAP_AUTH_HOST},;'	\
	-e 's,^[;\s]*(POP3_AUTH_HOST)\s*=.*,$1 = %{POP3_AUTH_HOST},;'	\
	-e 's,^[;\s]*(AUTH_USER_FILE)\s*=.*,$1 = %{AUTH_USER_FILE},;'	\
	-e 's,^[;\s]*(AUTH_SESS_USER)\s*=.*,$1 = %{AUTH_SESS_USER},;'	\
	-e 's,^[;\s]*(AUTH_SESS_LEVEL)\s*=.*,$1 = %{AUTH_SESS_LEVEL},;'	\
	-e 's,^[;\s]*(AUTH_GROUP_FILE)\s*=.*,$1 = %{AUTH_GROUP_FILE},;'	\
	config-dist.ini > config.ini



%clean
%{__rm} -rf %{buildroot}

%post

cd %{prefix}/%{name}
mysqladmin create %{DB_NAME}

echo 'GRANT select, insert, update, delete, lock tables 
ON %{DB_NAME}.* 
TO %{DB_USER}@localhost 
IDENTIFIED BY "%{DB_PASSWD}"' | mysql

mysqladmin reload

cat schemas/mysql-initialize.sql | mysql %{DB_NAME}

%files
%defattr(-, root, root)
%doc README UPGRADING LICENSE INSTALL doc Makefile
%attr(0775, %{httpduser}, %{httpdgroup}) %dir %{_var}/log/%{name}
%{prefix}/%{name}

%changelog
* Fri Sep 19 2014 - Alain Peyrat <aljeux@free.fr> - 1.5.0-1
- Reworked for 1.5.0

* Tue May 19 2005 Jesse Becker <jbecker@northwestern.edu>
- Initial build
