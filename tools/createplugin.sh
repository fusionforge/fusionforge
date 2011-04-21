#!/bin/sh

# TODO : missing copyright

# You should instead probably use ../plugins/templates/createplugin.sh that is more up-to-date

usage() {
	echo Usage: $0 PluginName
}

echo "Plugin template creator"
if [ "$#" != "1" ] 
then
	usage
else
	fullname=$1
	minus=`echo $1 | tr '[A-Z]' '[a-z]'`
	plugdir=gforge-plugin-$minus
	echo "Creating $1 plugin"
	echo "Creating directory $plugdir"
	if [ ! -d $plugdir ]
	then
		mkdir $plugdir
		mkdir $plugdir/bin
		mkdir $plugdir/etc
		mkdir $plugdir/etc/plugins
		mkdir $plugdir/etc/plugins/$minus
		mkdir $plugdir/debian
		mkdir $plugdir/include
		mkdir $plugdir/include/languages
		mkdir $plugdir/lib
		mkdir $plugdir/www

echo Creating $plugdir/bin/db-delete.pl
cat > $plugdir/bin/db-delete.pl <<FIN
#!/usr/bin/perl -w
#
# Debian-specific script to delete plugin-specific tables
# Roland Mas <lolando@debian.org>

use strict ;
use diagnostics ;

use DBI ;
use MIME::Base64 ;
use HTML::Entities ;

use vars qw/\$dbh @reqlist \$query/ ;
use vars qw/\$sys_default_domain \$sys_cvs_host \$sys_download_host
    \$sys_shell_host \$sys_users_host \$sys_docs_host \$sys_lists_host
    \$sys_dns1_host \$sys_dns2_host \$FTPINCOMING_DIR \$FTPFILES_DIR
    \$sys_urlroot \$sf_cache_dir \$sys_name \$sys_themeroot
    \$sys_news_group \$sys_dbhost \$sys_dbname \$sys_dbuser \$sys_dbpasswd
    \$sys_ldap_base_dn \$sys_ldap_host \$admin_login \$admin_password
    \$server_admin \$domain_name \$newsadmin_groupid \$statsadmin_groupid
    \$skill_list/ ;
use vars qw/\$pluginname/ ;

sub is_lesser ( \$\$ ) ;
sub is_greater ( \$\$ ) ;
sub debug ( \$ ) ;
sub parse_sql_file ( \$ ) ;

require ("/usr/share/gforge/lib/include.pl") ; # Include a few predefined functions 
require ("/usr/share/gforge/lib/sqlparser.pm") ; # Our magic SQL parser

debug "You'll see some debugging info during this installation." ;
debug "Do not worry unless told otherwise." ;

&db_connect ;

# debug "Connected to the database OK." ;

\$pluginname = "$minus" ;

\$dbh->{AutoCommit} = 0;
\$dbh->{RaiseError} = 1;
eval {
    my (\$sth, @array, \$version, \$action, \$path, \$target, \$rname) ;

    my \$pattern = "plugin_" . \$pluginname . '_%' ;

    \$query = "SELECT relname FROM pg_class WHERE relname LIKE '\$pattern' AND relkind='v'" ;
    \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    while (@array = \$sth->fetchrow_array ()) {
	\$rname = \$array [0] ;
	&drop_view_if_exists (\$rname) ;
    }
    \$sth->finish () ;

    \$query = "SELECT relname FROM pg_class WHERE relname LIKE '\$pattern' AND relkind='r'" ;
    \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    while (@array = \$sth->fetchrow_array ()) {
	\$rname = \$array [0] ;
	&drop_table_if_exists (\$rname) ;
    }
    \$sth->finish () ;

    \$query = "SELECT relname FROM pg_class WHERE relname LIKE '\$pattern' AND relkind='i'" ;
    \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    while (@array = \$sth->fetchrow_array ()) {
	\$rname = \$array [0] ;
	&drop_index_if_exists (\$rname) ;
    }
    \$sth->finish () ;

    \$query = "SELECT relname FROM pg_class WHERE relname LIKE '\$pattern' AND relkind='s'" ;
    \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    while (@array = \$sth->fetchrow_array ()) {
	\$rname = \$array [0] ;
	&drop_sequence_if_exists (\$rname) ;
    }
    \$sth->finish () ;

    \$dbh->commit ();


    debug "It seems your database deletion went well and smoothly.  That's cool." ;
    debug "Please enjoy using Debian GForge." ;

    # There should be a commit at the end of every block above.
    # If there is not, then it might be symptomatic of a problem.
    # For safety, we roll back.
    \$dbh->rollback ();
};

if (\$@) {
    warn "Transaction aborted because \$@" ;
    debug "Transaction aborted because \$@" ;
    debug "Last SQL query was:\n\$query\n(end of query)" ;
    \$dbh->rollback ;
    debug "Please report this bug on the Debian bug-tracking system." ;
    debug "Please include the previous messages as well to help debugging." ;
    debug "You should not worry too much about this," ;
    debug "your DB is still in a consistent state and should be usable." ;
    exit 1 ;
}

\$dbh->rollback ;
\$dbh->disconnect ;

sub debug ( \$ ) {
    my \$v = shift ;
    chomp \$v ;
    print STDERR "\$v\n" ;
}

sub drop_table_if_exists ( \$ ) {
    my \$tname = shift or die  "Not enough arguments" ;
    \$query = "SELECT count(*) FROM pg_class WHERE relname='\$tname' AND relkind='r'" ;
    my \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    my @array = \$sth->fetchrow_array () ;
    \$sth->finish () ;

    if (\$array [0] != 0) {
	# debug "Dropping table \$tname" ;
	\$query = "DROP TABLE \$tname" ;
	# debug \$query ;
	\$sth = \$dbh->prepare (\$query) ;
	\$sth->execute () ;
	\$sth->finish () ;
    }
}

sub drop_sequence_if_exists ( \$ ) {
    my \$sname = shift or die  "Not enough arguments" ;
    \$query = "SELECT count(*) FROM pg_class WHERE relname='\$sname' AND relkind='S'" ;
    my \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    my @array = \$sth->fetchrow_array () ;
    \$sth->finish () ;

    if (\$array [0] != 0) {
	# debug "Dropping sequence \$sname" ;
	\$query = "DROP SEQUENCE \$sname" ;
	# debug \$query ;
	\$sth = \$dbh->prepare (\$query) ;
	\$sth->execute () ;
	\$sth->finish () ;
    }
}

sub drop_index_if_exists ( \$ ) {
    my \$iname = shift or die  "Not enough arguments" ;
    \$query = "SELECT count(*) FROM pg_class WHERE relname='\$iname' AND relkind='i'" ;
    my \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    my @array = \$sth->fetchrow_array () ;
    \$sth->finish () ;

    if (\$array [0] != 0) {
	# debug "Dropping index \$iname" ;
	\$query = "DROP INDEX \$iname" ;
	# debug \$query ;
	\$sth = \$dbh->prepare (\$query) ;
	\$sth->execute () ;
	\$sth->finish () ;
    }
}

sub drop_view_if_exists ( \$ ) {
    my \$iname = shift or die  "Not enough arguments" ;
    \$query = "SELECT count(*) FROM pg_class WHERE relname='\$iname' AND relkind='v'" ;
    my \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    my @array = \$sth->fetchrow_array () ;
    \$sth->finish () ;

    if (\$array [0] != 0) {
	# debug "Dropping view \$iname" ;
	\$query = "DROP VIEW \$iname" ;
	# debug \$query ;
	\$sth = \$dbh->prepare (\$query) ;
	\$sth->execute () ;
	\$sth->finish () ;
    }
}
FIN

echo Creating $plugdir/bin/db-upgrade.pl
cat > $plugdir/bin/db-upgrade.pl <<FIN
#!/usr/bin/perl -w
#
# Debian-specific script to upgrade the database between releases
# Roland Mas <lolando@debian.org>

use strict ;
use diagnostics ;

use DBI ;
use MIME::Base64 ;
use HTML::Entities ;

use vars qw/\$dbh @reqlist \$query/ ;
use vars qw/\$sys_default_domain \$sys_cvs_host \$sys_download_host
    \$sys_shell_host \$sys_users_host \$sys_docs_host \$sys_lists_host
    \$sys_dns1_host \$sys_dns2_host \$FTPINCOMING_DIR \$FTPFILES_DIR
    \$sys_urlroot \$sf_cache_dir \$sys_name \$sys_themeroot
    \$sys_news_group \$sys_dbhost \$sys_dbname \$sys_dbuser \$sys_dbpasswd
    \$sys_ldap_base_dn \$sys_ldap_host \$admin_login \$admin_password
    \$server_admin \$domain_name \$newsadmin_groupid \$statsadmin_groupid
    \$skill_list/ ;
use vars qw/\$pluginname/ ;

sub is_lesser ( \$\$ ) ;
sub is_greater ( \$\$ ) ;
sub debug ( \$ ) ;
sub parse_sql_file ( \$ ) ;

require ("/usr/share/gforge/lib/include.pl") ; # Include a few predefined functions 
require ("/usr/share/gforge/lib/sqlparser.pm") ; # Our magic SQL parser

debug "You'll see some debugging info during this installation." ;
debug "Do not worry unless told otherwise." ;

&db_connect ;

# debug "Connected to the database OK." ;

\$pluginname = "$minus" ;

\$dbh->{AutoCommit} = 0;
\$dbh->{RaiseError} = 1;
eval {
    my (\$sth, @array, \$version, \$path, \$target) ;

    &create_metadata_table ("0") ;
    
    \$version = &get_db_version ;
    \$target = "0.1" ;
    if (is_lesser \$version, \$target) {
	my @filelist = ( "/usr/share/gforge/plugins/\$pluginname/lib/\$pluginname-init.sql" ) ;
	
	foreach my \$file (@filelist) {
	    debug "Processing \$file" ;
	    @reqlist = @{ &parse_sql_file (\$file) } ;
	    
	    foreach my \$s (@reqlist) {
		\$query = \$s ;
		# debug \$query ;
		\$sth = \$dbh->prepare (\$query) ;
		\$sth->execute () ;
		\$sth->finish () ;
	    }
	}
	@reqlist = () ;
	
	&update_db_version (\$target) ;
	debug "Committing." ;
	\$dbh->commit () ;
    }
    
    \$version = &get_db_version ;
    \$target = "0.2" ;
    if (is_lesser \$version, \$target) {
	debug "Adding local data." ;
	
	do "/etc/gforge/local.pl" or die "Cannot read /etc/gforge/local.pl" ;
	
	my \$ip_address = qx/host \$domain_name | awk '{print \\$3}'/ ;
	
	@reqlist = (
		    "INSERT INTO plugin_".\$pluginname."_sample_data (domain, ip_address) VALUES ('\$domain_name', '\$ip_address')",
		    ) ;
	
	foreach my \$s (@reqlist) {
	    \$query = \$s ;
	    # debug \$query ;
	    \$sth = \$dbh->prepare (\$query) ;
	    \$sth->execute () ;
	    \$sth->finish () ;
	}
	@reqlist = () ;
	
	&update_db_version (\$target) ;
	debug "Committing." ;
	\$dbh->commit () ;
    }

    debug "It seems your database install/upgrade went well and smoothly.  That's cool." ;
    debug "Please enjoy using Debian GForge." ;

    # There should be a commit at the end of every block above.
    # If there is not, then it might be symptomatic of a problem.
    # For safety, we roll back.
    \$dbh->rollback ();
};

if (\$@) {
    warn "Transaction aborted because \$@" ;
    debug "Transaction aborted because \$@" ;
    debug "Last SQL query was:\n\$query\n(end of query)" ;
    \$dbh->rollback ;
    debug "Please report this bug on the Debian bug-tracking system." ;
    debug "Please include the previous messages as well to help debugging." ;
    debug "You should not worry too much about this," ;
    debug "your DB is still in a consistent state and should be usable." ;
    exit 1 ;
}

\$dbh->rollback ;
\$dbh->disconnect ;

sub is_lesser ( \$\$ ) {
    my \$v1 = shift || 0 ;
    my \$v2 = shift || 0 ;

    my \$rc = system "dpkg --compare-versions \$v1 lt \$v2" ;

    return (! \$rc) ;
}

sub is_greater ( \$\$ ) {
    my \$v1 = shift || 0 ;
    my \$v2 = shift || 0 ;

    my \$rc = system "dpkg --compare-versions \$v1 gt \$v2" ;

    return (! \$rc) ;
}

sub debug ( \$ ) {
    my \$v = shift ;
    chomp \$v ;
    print STDERR "\$v\n" ;
}

sub create_metadata_table ( \$ ) {
    my \$v = shift || "0" ;
    my \$tablename = "plugin_" .\$pluginname . "_meta_data" ;
    # Do we have the metadata table?

    \$query = "SELECT count(*) FROM pg_class WHERE relname = '\$tablename' and relkind = 'r'";
    # debug \$query ;
    my \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    my @array = \$sth->fetchrow_array () ;
    \$sth->finish () ;

    # Let's create this table if we have it not

    if (\$array [0] == 0) {
	debug "Creating \$tablename table." ;
	\$query = "CREATE TABLE \$tablename (key varchar primary key, value text not null)" ;
	# debug \$query ;
	\$sth = \$dbh->prepare (\$query) ;
	\$sth->execute () ;
	\$sth->finish () ;
    }

    \$query = "SELECT count(*) FROM \$tablename WHERE key = 'db-version'";
    # debug \$query ;
    \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    @array = \$sth->fetchrow_array () ;
    \$sth->finish () ;

    # Empty table?  We'll have to fill it up a bit

    if (\$array [0] == 0) {
	debug "Inserting first data into \$tablename table." ;
	\$query = "INSERT INTO \$tablename (key, value) VALUES ('db-version', '\$v')" ;
	# debug \$query ;
	\$sth = \$dbh->prepare (\$query) ;
	\$sth->execute () ;
	\$sth->finish () ;
    }
}

sub update_db_version ( \$ ) {
    my \$v = shift or die "Not enough arguments" ;
    my \$tablename = "plugin_" .\$pluginname . "_meta_data" ;

    debug "Updating \$tablename table." ;
    \$query = "UPDATE \$tablename SET value = '\$v' WHERE key = 'db-version'" ;
    # debug \$query ;
    my \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    \$sth->finish () ;
}

sub get_db_version () {
    my \$tablename = "plugin_" .\$pluginname . "_meta_data" ;

    \$query = "SELECT value FROM \$tablename WHERE key = 'db-version'" ;
    # debug \$query ;
    my \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    my @array = \$sth->fetchrow_array () ;
    \$sth->finish () ;

    my \$version = \$array [0] ;

    return \$version ;
}

sub drop_table_if_exists ( \$ ) {
    my \$tname = shift or die  "Not enough arguments" ;
    \$query = "SELECT count(*) FROM pg_class WHERE relname='\$tname' AND relkind='r'" ;
    my \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    my @array = \$sth->fetchrow_array () ;
    \$sth->finish () ;

    if (\$array [0] != 0) {
	# debug "Dropping table \$tname" ;
	\$query = "DROP TABLE \$tname" ;
	# debug \$query ;
	\$sth = \$dbh->prepare (\$query) ;
	\$sth->execute () ;
	\$sth->finish () ;
    }
}

sub drop_sequence_if_exists ( \$ ) {
    my \$sname = shift or die  "Not enough arguments" ;
    \$query = "SELECT count(*) FROM pg_class WHERE relname='\$sname' AND relkind='S'" ;
    my \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    my @array = \$sth->fetchrow_array () ;
    \$sth->finish () ;

    if (\$array [0] != 0) {
	# debug "Dropping sequence \$sname" ;
	\$query = "DROP SEQUENCE \$sname" ;
	# debug \$query ;
	\$sth = \$dbh->prepare (\$query) ;
	\$sth->execute () ;
	\$sth->finish () ;
    }
}

sub drop_index_if_exists ( \$ ) {
    my \$iname = shift or die  "Not enough arguments" ;
    \$query = "SELECT count(*) FROM pg_class WHERE relname='\$iname' AND relkind='i'" ;
    my \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    my @array = \$sth->fetchrow_array () ;
    \$sth->finish () ;

    if (\$array [0] != 0) {
	# debug "Dropping index \$iname" ;
	\$query = "DROP INDEX \$iname" ;
	# debug \$query ;
	\$sth = \$dbh->prepare (\$query) ;
	\$sth->execute () ;
	\$sth->finish () ;
    }
}

sub drop_view_if_exists ( \$ ) {
    my \$iname = shift or die  "Not enough arguments" ;
    \$query = "SELECT count(*) FROM pg_class WHERE relname='\$iname' AND relkind='v'" ;
    my \$sth = \$dbh->prepare (\$query) ;
    \$sth->execute () ;
    my @array = \$sth->fetchrow_array () ;
    \$sth->finish () ;

    if (\$array [0] != 0) {
	# debug "Dropping view \$iname" ;
	\$query = "DROP VIEW \$iname" ;
	# debug \$query ;
	\$sth = \$dbh->prepare (\$query) ;
	\$sth->execute () ;
	\$sth->finish () ;
    }
}

sub bump_sequence_to ( \$\$ ) {
    my (\$sth, @array, \$seqname, \$targetvalue) ;

    \$seqname = shift ;
    \$targetvalue = shift ;

    do {
	\$query = "select nextval ('\$seqname')" ;
	\$sth = \$dbh->prepare (\$query) ;
	\$sth->execute () ;
	@array = \$sth->fetchrow_array () ;
	\$sth->finish () ;
    } until \$array[0] >= \$targetvalue ;
}
FIN

echo Creating $plugdir/bin/$minus
cat > $plugdir/bin/$minus <<FIN
#! /usr/bin/perl -w

my \$world ;

do "/etc/gforge/plugins/$minus/$minus.conf"
    or die "Cannot read /etc/gforge/plugins/$minus/$minus.conf" ;

print STDOUT "\$world on STDOUT!\n" ;
print STDERR "\$world on STDERR!\n" ;
FIN

echo Creating $plugdir/etc/plugins/$minus/$minus.conf
cat > $plugdir/etc/plugins/$minus/$minus.conf <<FIN
\$world = 'Earth' ;
FIN

echo Creating $plugdir/etc/plugins/$minus/config.php
cat > $plugdir/etc/plugins/$minus/config.php <<FIN
<?php
\$world = 'Earth';
?>
FIN

echo Creating $plugdir/httpd.conf
cat > $plugdir/httpd.conf <<FIN
<Location /plugins/$minus/>
  SetEnv PLUGINNAME $minus
  SetEnv PLUGINVERSION 0.1-1
</Location>
FIN

echo Creating $plugdir/debian/README.Debian
cat > $plugdir/debian/README.Debian <<FIN
gforge-plugin-$minus for Debian
----------------------------------------

<possible notes regarding this package - if none, delete this file>

 -- Roland Mas <lolando@debian.org>, Tue, 26 Nov 2002 21:31:00 +0100
FIN

echo Creating $plugdir/debian/changelog
cat > $plugdir/debian/changelog <<FIN
gforge-plugin-$minus (0.1-1) unstable; urgency=low

  * Initial Release.

 -- Roland Mas <lolando@debian.org>  Tue, 26 Nov 2002 21:31:00 +0100

FIN

echo Creating $plugdir/debian/control
cat > $plugdir/debian/control <<FIN
Source: gforge-plugin-$minus
Section: devel
Priority: optional
Maintainer: Roland Mas <lolando@debian.org>
Build-Depends-Indep: debhelper (>> 4.0.0)
Standards-Version: 3.5.8

Package: gforge-plugin-$minus
Architecture: all
Depends: gforge-common, gforge-db-postgresql | gforge-db, gforge-web-apache | gforge-web
Description: The ${fullname} plugin for GForge
 This is intended as an example for developers of GForge plugins,
 as well as an experimental area for the GForge plugin subsystem.
FIN

echo Creating $plugdir/debian/copyright
cat > $plugdir/debian/copyright <<FIN
This package was debianized by Roland Mas <lolando@debian.org> on
Tue, 26 Nov 2002 21:31:00 +0100.

It was downloaded from <http://savannah.nongnu.org/projects/debian-sf>

Upstream Author: Roland Mas <lolando@debian.org>

Copyright:

   This package is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; version 2 dated June, 1991.

   This package is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this package; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
   02111-1307, USA.

On Debian GNU/Linux systems, the complete text of the GNU General
Public License can be found in '/usr/share/common-licenses/GPL'.

FIN

echo Creating $plugdir/debian/cron.d
cat > $plugdir/debian/cron.d <<FIN
0 0 * * * gforge [ -x /usr/share/gforge/plugins/$minus/bin/$minus ] && /usr/share/gforge/plugins/$minus/bin/$minus > /dev/null 2>&1
FIN

echo Creating $plugdir/debian/dirs
cat > $plugdir/debian/dirs <<FIN
etc
etc/gforge
etc/gforge/httpd.d
etc/gforge/httpd.secrets.d
etc/gforge/plugins
etc/gforge/plugins/$minus
usr
usr/lib
usr/lib/gforge
usr/lib/gforge/plugins/
usr/lib/gforge/plugins/$minus
usr/lib/gforge/plugins/$minus/bin
usr/lib/gforge/plugins/$minus/include
usr/lib/gforge/plugins/$minus/include/languages
usr/lib/gforge/plugins/$minus/lib
usr/lib/gforge/cgi-bin
usr/share
usr/share/gforge
usr/share/gforge/www
usr/share/gforge/www/plugins
usr/share/gforge/www/plugins/$minus
FIN

echo Creating $plugdir/debian/postinst
cat > $plugdir/debian/postinst <<FIN
#! /bin/sh
# postinst script for gforge-plugin-$minus
#
# see: dh_installdeb(1)

set -e

# summary of how this script can be called:
#        * <postinst> 'configure' <most-recently-configured-version>
#        * <old-postinst> 'abort-upgrade' <new version>
#        * <conflictor's-postinst> 'abort-remove' 'in-favour' <package>
#          <new-version>
#        * <deconfigured's-postinst> 'abort-deconfigure' 'in-favour'
#          <failed-install-package> <version> 'removing'
#          <conflicting-package> <version>
# for details, see http://www.debian.org/doc/debian-policy/ or
# the debian-policy package
#
# quoting from the policy:
#     Any necessary prompting should almost always be confined to the
#     post-installation script, and should be protected with a conditional
#     so that unnecessary prompting doesn't happen if a package's
#     installation fails and the 'postinst' is called with 'abort-upgrade',
#     'abort-remove' or 'abort-deconfigure'.

case "\$1" in
    configure)
	/usr/share/gforge/plugins/$minus/bin/db-upgrade.pl
	/usr/share/gforge/bin/register-plugin $minus "${fullname}"
	invoke-rc.d apache reload
    ;;

    abort-upgrade|abort-remove|abort-deconfigure)

    ;;

    *)
        echo "postinst called with unknown argument \'\$1'" >&2
        exit 1
    ;;
esac

# dh_installdeb will replace this with shell code automatically
# generated by other debhelper scripts.

#DEBHELPER#

exit 0


FIN

echo Creating $plugdir/debian/postrm.ex
cat > $plugdir/debian/postrm.ex <<FIN
#! /bin/sh
# postrm script for gforge-plugin-$minus
#
# see: dh_installdeb(1)

set -e

# summary of how this script can be called:
#        * <postrm> 'remove'
#        * <postrm> 'purge'
#        * <old-postrm> 'upgrade' <new-version>
#        * <new-postrm> 'failed-upgrade' <old-version>
#        * <new-postrm> 'abort-install'
#        * <new-postrm> 'abort-install' <old-version>
#        * <new-postrm> 'abort-upgrade' <old-version>
#        * <disappearer's-postrm> 'disappear' <r>overwrit>r> <new-version>
# for details, see http://www.debian.org/doc/debian-policy/ or
# the debian-policy package


case "\$1" in
       purge|remove|upgrade|failed-upgrade|abort-install|abort-upgrade|disappear)


        ;;

    *)
        echo "postrm called with unknown argument \'\$1'" >&2
        exit 1

esac

# dh_installdeb will replace this with shell code automatically
# generated by other debhelper scripts.

#DEBHELPER#

exit 0
FIN

echo Creating $plugdir/debian/preinst.ex
cat > $plugdir/debian/preinst.ex <<FIN
#! /bin/sh
# preinst script for gforge-plugin-$minus
#
# see: dh_installdeb(1)

set -e

# summary of how this script can be called:
#        * <new-preinst> 'install'
#        * <new-preinst> 'install' <old-version>
#        * <new-preinst> 'upgrade' <old-version>
#        * <old-preinst> 'abort-upgrade' <new-version>
#
# for details, see http://www.debian.org/doc/debian-policy/ or
# the debian-policy package


case "\$1" in
    install|upgrade)
#        if [ "\$1" = "upgrade" ]
#        then
#            start-stop-daemon --stop --quiet --oknodo  \
#                --pidfile /var/run/gforge-plugin-$minus.pid  \
#                --exec /usr/sbin/gforge-plugin-$minus 2>/dev/null || true
#        fi
    ;;

    abort-upgrade)
    ;;

    *)
        echo "preinst called with unknown argument \'\$1'" >&2
        exit 1
    ;;
esac

# dh_installdeb will replace this with shell code automatically
# generated by other debhelper scripts.

#DEBHELPER#

exit 0


FIN

echo Creating $plugdir/debian/prerm
cat > $plugdir/debian/prerm <<FIN
#! /bin/sh
# prerm script for gforge-plugin-$minus
#
# see: dh_installdeb(1)

set -e

# summary of how this script can be called:
#        * <prerm> 'remove'
#        * <old-prerm> 'upgrade' <new-version>
#        * <new-prerm> 'failed-upgrade' <old-version>
#        * <conflictor's-prerm> 'remove' 'in-favour' <package> <new-version>
#        * <deconfigured's-prerm> 'deconfigure' 'in-favour'
#          <package-being-installed> <version> 'removing'
#          <conflicting-package> <version>
# for details, see http://www.debian.org/doc/debian-policy/ or
# the debian-policy package


case "\$1" in
    remove|deconfigure)
	/usr/share/gforge/bin/unregister-plugin $minus
	invoke-rc.d apache reload
	/usr/share/gforge/plugins/$minus/bin/db-delete.pl
        ;;
    upgrade|failed-upgrade)
        ;;
    *)
        echo "prerm called with unknown argument \'\$1'" >&2
        exit 1
    ;;
esac

# dh_installdeb will replace this with shell code automatically
# generated by other debhelper scripts.

#DEBHELPER#

exit 0


FIN

echo Creating $plugdir/debian/rules
cat > $plugdir/debian/rules <<FIN
#!/usr/bin/make -f
# Sample debian/rules that uses debhelper.
# GNU copyright 1997 to 1999 by Joey Hess.

# Uncomment this to turn on verbose mode.
#export DH_VERBOSE=1

# This is the debhelper compatibility version to use.
export DH_COMPAT=4

export PLUGIN=$minus

configure: configure-stamp
configure-stamp:
	dh_testdir
	# Add here commands to configure the package.
	touch configure-stamp

build: build-stamp

build-stamp: configure-stamp
	dh_testdir
	touch build-stamp

clean:
	dh_testdir
	dh_testroot
	rm -f build-stamp configure-stamp
	dh_clean

DESTDIR=\$(CURDIR)/debian/gforge-plugin-\$(PLUGIN)

install: build
	dh_testdir
	dh_testroot
	dh_clean -k
	dh_installdirs

	cp -r bin/* \$(DESTDIR)/usr/share/gforge/plugins/\$(PLUGIN)/bin/
	cp -r include/* \$(DESTDIR)/usr/share/gforge/plugins/\$(PLUGIN)/include/
	cp -r lib/* \$(DESTDIR)/usr/share/gforge/plugins/\$(PLUGIN)/lib/
	# cp -r cgi-bin/* \$(DESTDIR)/usr/share/gforge/plugins/\$(PLUGIN)/cgi-bin/
	cp -r etc/* \$(DESTDIR)/etc/gforge/plugins/\$(PLUGIN)/
	cp -r www/* \$(DESTDIR)/usr/share/gforge/www/plugins/\$(PLUGIN)/
	install -m 0644 httpd.conf \$(DESTDIR)/etc/gforge/httpd.d/50\$(PLUGIN)
	# install -m 0600 httpd.secrets \$(DESTDIR)/etc/gforge/httpd.secrets.d/50\$(PLUGIN)
	find \$(DESTDIR)/ -name CVS -type d | xargs rm -rf
	find \$(DESTDIR)/usr/share/gforge/plugins/\$(PLUGIN)/bin/ -type f | xargs chmod 0755
	find \$(DESTDIR)/usr/share/gforge/plugins/\$(PLUGIN)/include/ -type f | xargs chmod 0644
	find \$(DESTDIR)/usr/share/gforge/plugins/\$(PLUGIN)/lib/ -type f | xargs chmod 0644
	# find \$(DESTDIR)/usr/share/gforge/plugins/\$(PLUGIN)/cgi-bin/ -type f | xargs chmod 0755
	find \$(DESTDIR)/etc/gforge/plugins/\$(PLUGIN)/ -type f | xargs chmod 0644
	find \$(DESTDIR)/usr/share/gforge/www/plugins/\$(PLUGIN)/ -type f | xargs chmod 0644


# Build architecture-independent files here.
binary-indep: build install
# We have nothing to do by default.

# Build architecture-dependent files here.
binary-arch: build install
	dh_testdir
	dh_testroot
#	dh_installdebconf
	dh_installdocs
	dh_installexamples
	dh_installmenu
#	dh_installlogrotate
#	dh_installemacsen
#	dh_installpam
#	dh_installmime
#	dh_installinit
	dh_installcron
	dh_installman
	dh_installinfo
#	dh_undocumented
	dh_installchangelogs 
	dh_link
	dh_strip
	dh_compress
	dh_fixperms
#	dh_makeshlibs
	dh_installdeb
#	dh_perl
	dh_shlibdeps
	dh_gencontrol
	dh_md5sums
	dh_builddeb

binary: binary-indep binary-arch
.PHONY: build clean binary-indep binary-arch binary install configure
FIN

echo Creating $plugdir/include/${fullname}Plugin.class
cat > $plugdir/include/${fullname}Plugin.class <<FIN
<?php

class ${fullname}Plugin extends Plugin {
	function ${fullname}Plugin () {
		\$this->Plugin() ;
		\$this->name = "$minus" ;
		\$this->hooks[] = "usermenu" ;
	}

	function CallHook (\$hookname, \$params) {
		global \$G_SESSION, \$HTML ;
		if (\$hookname == "usermenu") {
			\$text = "${fullname}" ;
			if (\$G_SESSION->usesPlugin("$minus")) {
				\$text .= ' [on]' ;
			} else {
				\$text .= ' [off]' ;
			}
			echo ' | ' . \$HTML->PrintSubMenu (array (\$text),
						  array ('/plugins/$minus/index.php?user_id=' . \$G_SESSION->getId()));
		} elseif (\$hookname == "blahblahblah") {
			// ...
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
FIN

echo Creating $plugdir/include/$minus-init.php
cat > $plugdir/include/$minus-init.php <<FIN
<?php

require_once (\$GLOBALS['sys_plugins_path'].'/$minus/include/${fullname}Plugin.class') ;

\$${fullname}PluginObject = new ${fullname}Plugin() ;

register_plugin (\$${fullname}PluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
FIN

echo Creating $plugdir/lib/$minus-init.sql
cat > $plugdir/lib/$minus-init.sql <<FIN
CREATE TABLE plugin_${minus}_sample_data (
	domain text,
	ip_address text
) ;
FIN

echo Creating $plugdir/www/index.php
cat > $plugdir/www/index.php <<FIN
<?php
/*
 * ${fullname} plugin
 *
 * Roland Mas <lolando@debian.org>
 */

require_once('pre.php');

if (!\$user_id) {
	exit_error('Error','No User Id Provided');
}

\$user = user_get_object(\$user_id);


if (!\$user || !is_object(\$user) || \$user->isError() || !\$user->isActive()) {
	exit_error("Invalid User", "That user does not exist.");
} else {
	print \$HTML->header(array('title'=>'${fullname}','pagename'=>'$minus'));
	\$user_name = \$user->getRealName();

	if (\$user->usesPlugin("$minus")) {
		print \$HTML->boxTop("\$user_name says ${fullname}!");
	} else {
		print \$HTML->boxTop("\$user_name does not say ${fullname}...");
	}
	print '<A HREF="toggle.php?user_id='.\$user_id.'">Toggle!</A>' ;
	print "This is the $minus plugin.  I hope you enjoy it." ;
	print '<A HREF="/my/">Back to My Peronal Page.</A>' ;
	print \$HTML->boxBottom();
	print \$HTML->footer(array());
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
FIN

echo Creating $plugdir/www/toggle.php
cat > $plugdir/www/toggle.php <<FIN
<?php
/*
 * ${fullname} plugin
 *
 * Roland Mas <lolando@debian.org>
 */

require_once('pre.php');

if (!\$user_id) {
	exit_error('Error','No User Id Provided');
}

\$user = user_get_object(\$user_id);


if (!\$user || !is_object(\$user) || \$user->isError() || !\$user->isActive()) {
	exit_error("Invalid User", "That user does not exist.");
} else {
	print \$HTML->header(array('title'=>'${fullname}','pagename'=>'$minus'));
	\$user_name = \$user->getRealName();

	if (\$user->usesPlugin("$minus")) {
		print \$HTML->boxTop("\$user_name says ${fullname}!");
	} else {
		print \$HTML->boxTop("\$user_name does not say ${fullname}...");
	}
	print "And now, I'm toggling the use of the ${fullname} plugin...\n" ;
	\$user->setPluginUse("$minus", !\$user->usesPlugin("$minus")) ;
	print "done.  Let's try it out.\n" ;

	if (\$user->usesPlugin("$minus")) {
		print \$HTML->boxMiddle("\$user_name now says ${fullname}!");
	} else {
		print \$HTML->boxMiddle("\$user_name now does not say ${fullname}...");
	}
	print '<A HREF="index.php?user_id='.\$user_id.'">Back to index.</A>' ;
	print \$HTML->boxBottom();
	print \$HTML->footer(array());
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
FIN










		
	else 
		echo $minus already exist, Aborting....
		exit 1
	fi
fi
