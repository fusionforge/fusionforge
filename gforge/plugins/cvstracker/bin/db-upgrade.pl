#!/usr/bin/perl -w
#
# $Id$
#
# Debian-specific script to upgrade the database between releases
# Roland Mas <lolando@debian.org>

use strict ;
use diagnostics ;

use DBI ;
use MIME::Base64 ;
use HTML::Entities ;

use vars qw/$dbh @reqlist $query/ ;
use vars qw/$sys_default_domain $sys_cvs_host $sys_download_host
    $sys_shell_host $sys_users_host $sys_docs_host $sys_lists_host
    $sys_dns1_host $sys_dns2_host $FTPINCOMING_DIR $FTPFILES_DIR
    $sys_urlroot $sf_cache_dir $sys_name $sys_themeroot
    $sys_news_group $sys_dbhost $sys_dbname $sys_dbuser $sys_dbpasswd
    $sys_ldap_base_dn $sys_ldap_host $admin_login $admin_password
    $server_admin $domain_name $newsadmin_groupid $statsadmin_groupid
    $skill_list/ ;
use vars qw/$pluginname/ ;

sub is_lesser ( $$ ) ;
sub is_greater ( $$ ) ;
sub debug ( $ ) ;
sub parse_sql_file ( $ ) ;

require ("/usr/lib/gforge/lib/include.pl") ; # Include a few predefined functions 
require ("/usr/lib/gforge/lib/sqlparser.pm") ; # Our magic SQL parser
require ("/usr/lib/gforge/lib/sqlhelper.pm") ; # Our SQL functions

debug "You'll see some debugging info during this installation." ;
debug "Do not worry unless told otherwise." ;

&db_connect ;

# debug "Connected to the database OK." ;

$pluginname = "cvstracker" ;

$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;
eval {
    my ($sth, @array, $version, $path, $target) ;

    &create_plugin_metadata_table ($dbh, $pluginname);
    
    $version = &get_db_version ;
    $target = "0.1" ;
    if (is_lesser $version, $target) {
		my @filelist = ( "/usr/lib/gforge/plugins/$pluginname/db/$pluginname-init.sql" ) ;
		
		foreach my $file (@filelist) {
		    debug "Processing $file" ;
		    @reqlist = @{ &parse_sql_file ($file) } ;
		    
		    foreach my $s (@reqlist) {
				$query = $s ;
				# debug $query ;
				$sth = $dbh->prepare ($query) ;
				$sth->execute () ;
				$sth->finish () ;
		    }
		}
		@reqlist = () ;
		
		&update_db_version ($target) ;
		debug "Committing." ;
		$dbh->commit () ;
    }
    
    $version = &get_db_version ;
    $target = "0.3" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20050305.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/plugins/$pluginname/db/20050305.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    debug "It seems your database install/upgrade went well and smoothly.  That's cool." ;
    debug "Please enjoy using Debian GForge." ;

    # There should be a commit at the end of every block above.
    # If there is not, then it might be symptomatic of a problem.
    # For safety, we roll back.
    $dbh->rollback ();
};

if ($@) {
    warn "Transaction aborted because $@" ;
    debug "Transaction aborted because $@" ;
    debug "Last SQL query was:\n$query\n(end of query)" ;
    $dbh->rollback ;
    debug "Please report this bug on the Debian bug-tracking system." ;
    debug "Please include the previous messages as well to help debugging." ;
    debug "You should not worry too much about this," ;
    debug "your DB is still in a consistent state and should be usable." ;
    exit 1 ;
}

$dbh->rollback ;
$dbh->disconnect ;

sub update_db_version ( $ ) {
    my $v = shift or die "Not enough arguments" ;
    my $tablename = "plugin_" .$pluginname . "_meta_data" ;

    debug "Updating $tablename table." ;
    $query = "UPDATE $tablename SET value = '$v' WHERE key = 'db-version'" ;
    # debug $query ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    $sth->finish () ;
}

sub get_db_version () {
    my $tablename = "plugin_" .$pluginname . "_meta_data" ;

    $query = "SELECT value FROM $tablename WHERE key = 'db-version'" ;
    # debug $query ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    my @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    my $version = $array [0] ;

    return $version ;
}

