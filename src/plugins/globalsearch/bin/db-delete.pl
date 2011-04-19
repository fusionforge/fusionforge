#!/usr/bin/perl -w
#
# $Id: db-delete.pl,v 1.1.1.1 2004/03/29 01:20:13 vjrj Exp $
#
# Debian-specific script to delete plugin-specific tables
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
    $domain_name $newsadmin_groupid $statsadmin_groupid
    $skill_list/ ;
use vars qw/$pluginname/ ;

sub is_lesser ( $$ ) ;
sub is_greater ( $$ ) ;
sub debug ( $ ) ;
sub parse_sql_file ( $ ) ;

require ("/usr/share/gforge/lib/include.pl") ; # Include a few predefined functions 
require ("/usr/share/gforge/lib/sqlparser.pm") ; # Our magic SQL parser
require ("/usr/share/gforge/lib/sqlhelper.pm") ; # A few SQL helper functions

debug "You'll see some debugging info during this installation." ;
debug "Do not worry unless told otherwise." ;

&db_connect ;

# debug "Connected to the database OK." ;

$pluginname = "globalsearch" ;

$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;
eval {
    my ($sth, @array, $version, $action, $path, $target, $rname) ;

    # Drop a few tables in a specific order
    &drop_table_if_exists ($dbh, "plugin_globalsearch_assoc_site_project") ;
    &drop_table_if_exists ($dbh, "plugin_globalsearch_assoc_site") ;
    &drop_table_if_exists ($dbh, "plugin_globalsearch_assoc_status") ;

    # Then drop any remaining data structures
    my $pattern = "plugin_" . $pluginname . '_%' ;

    $query = "SELECT relname FROM pg_class WHERE relname LIKE '$pattern' AND relkind='v'" ;
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    while (@array = $sth->fetchrow_array ()) {
        $rname = $array [0] ;
        &drop_view_if_exists ($dbh, $rname) ;
    }
    $sth->finish () ;

    $query = "SELECT relname FROM pg_class WHERE relname LIKE '$pattern' AND relkind='r'" ;
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    while (@array = $sth->fetchrow_array ()) {
        $rname = $array [0] ;
        &drop_table_if_exists ($dbh, $rname) ;
    }
    $sth->finish () ;

    $query = "SELECT relname FROM pg_class WHERE relname LIKE '$pattern' AND relkind='i'" ;
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    while (@array = $sth->fetchrow_array ()) {
        $rname = $array [0] ;
        &drop_index_if_exists ($dbh, $rname) ;
    }
    $sth->finish () ;

    $query = "SELECT relname FROM pg_class WHERE relname LIKE '$pattern' AND relkind='S'" ;
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    while (@array = $sth->fetchrow_array ()) {
        $rname = $array [0] ;
        &drop_sequence_if_exists ($dbh, $rname) ;
    }
    $sth->finish () ;

    $dbh->commit ();


    debug "It seems your database deletion went well and smoothly.  That's cool." ;
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
