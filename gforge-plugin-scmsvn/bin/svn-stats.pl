#!/usr/bin/perl -w
#
# $Id$
#
# Gather and calculate Subversion statistics
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

require ("/usr/lib/gforge/lib/include.pl") ; # Include a few predefined functions 
require ("/usr/lib/gforge/lib/sqlparser.pm") ; # Our magic SQL parser
require ("/usr/lib/gforge/lib/sqlhelper.pm") ; # Our SQL functions

&db_connect ;

# &debug ("Connected to the database OK.") ;

$pluginname = "scmsvn" ;

$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;
eval {
    my ($sth, @array, $version, $path, $target) ;
    my (@grouplist, %groupname, %lastdate, %lastrev, %alreadyseen) ;

    my $pluginid = get_plugin_id ($dbh, $pluginname) ;

    # First, get the groups that use Subversion
    $query = "SELECT group_plugin.group_id, groups.unix_group_name
              FROM group_plugin, groups
              WHERE group_plugin.plugin_id = $pluginid
                AND group_plugin.group_id = groups.group_id";
    # &debug ($query) ;
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    while (@array = $sth->fetchrow_array ()) {
	my $group_id = $array[0] ;
	push @grouplist, $group_id ;
	$lastdate{$group_id} = 0 ;
	$lastrev{$group_id} = 0 ;
	$groupname{$group_id} = $array[1] ;
	$alreadyseen{$group_id} = 0 ;
    }
    $sth->finish () ;

    # Then, update hashes with the previous values
    $query = "SELECT group_id, last_check_date, last_repo_version
              FROM plugin_scmsvn_stats" ;
    # &debug ($query) ;
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    while (@array = $sth->fetchrow_array ()) {
	my $group_id = $array[0] ;
	$lastdate{$group_id} = $array[1] ;
	$lastrev{$group_id} = $array[2] ;
	$alreadyseen{$group_id} = 1 ;
    }
    $sth->finish () ;

    # Now examine each repository in turn
    foreach my $group_id (@grouplist) {
	my $svnroot = "/var/lib/gforge/chroot/svnroot/$groupname{$group_id}" ;
	my $currev = qx( svnlook youngest $svnroot ) ;
	my $adds = 0 ;
	my $deletes = 0 ;
	my $updates = 0 ;
	my $commits = 0 ;
	my $rev = $lastrev{$group_id} + 1 ;
	while ($rev <= $currev) {
	    $commits++ ;
	    open SVN, "svnlook changed -r$rev $svnroot |" ;
	    while (<SVN>) {
		chomp ;
		$adds++ if m/^A/ ;
		$deletes++ if m/^D/ ;
		$updates++ if m/^U/ ;
	    }
	    close SVN ;
	    $rev++ ;
	}

	my $time = time () ;
	if ($alreadyseen{$group_id} == 1) {
	    $query = "UPDATE plugin_scmsvn_stats
                      SET last_repo_version = $currev,
                          adds = $adds,
                          deletes = $deletes,
                          commits = $commits,
                          changes = $updates,
                          last_check_date = $time
                      WHERE group_id = $group_id" ;
	} else {
	    $query = "INSERT INTO plugin_scmsvn_stats
                             (last_repo_version, last_check_date, adds, deletes, commits, changes, group_id)
                      VALUES ($currev, $time, $adds, $deletes, $commits, $updates, $group_id)
" ;
	}
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$dbh->commit () ;
    }
};

if ($@) {
    warn "Transaction aborted because $@" ;
    &debug ("Transaction aborted because $@") ;
    &debug ("Last SQL query was:\n$query\n(end of query)") ;
    $dbh->rollback ;
    &debug ("Please report this bug.") ;
    &debug ("Please include the previous messages as well to help debugging.") ;
    &debug ("You should not worry too much about this,") ;
    &debug ("your DB is still in a consistent state and should be usable.") ;
    exit 1 ;
}

$dbh->rollback ;
$dbh->disconnect ;
