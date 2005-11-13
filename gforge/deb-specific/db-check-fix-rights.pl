#!/usr/bin/perl -w
# vim: sts=4
# Script provided by Raphael Hertzog, use to fix adullact.net site
# If you get troubles with roles, you can use this script
# run with $fix=0 to see (default), set $fix=1 to fix

use strict ;
use diagnostics ;

use DBI ;
use MIME::Base64 ;
use HTML::Entities ;

use vars qw/$dbh @reqlist $query/ ;
use vars qw/$sys_default_domain $sys_scm_host $sys_download_host
    $sys_shell_host $sys_users_host $sys_docs_host $sys_lists_host
    $sys_dns1_host $sys_dns2_host $FTPINCOMING_DIR $FTPFILES_DIR
    $sys_urlroot $sf_cache_dir $sys_name $sys_themeroot
    $sys_news_group $sys_dbhost $sys_dbname $sys_dbuser $sys_dbpasswd
    $sys_ldap_base_dn $sys_ldap_host $admin_login $admin_password
    $server_admin $domain_name $newsadmin_groupid $statsadmin_groupid
    $skill_list/ ;

require ("/etc/gforge/local.pl") ; 

if ( "$sys_dbname" ne "gforge" || "$sys_dbuser" ne "gforge" ) {
$dbh ||= DBI->connect("DBI:Pg:dbname=$sys_dbname","$sys_dbuser","$sys_dbpasswd");
} else {
$dbh ||= DBI->connect("DBI:Pg:dbname=$sys_dbname");
}
die "Cannot connect to database: $!" if ( ! $dbh );

my $fix = 0;

$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;
eval {

    my $query = "SELECT group_id, use_tracker, use_forum, use_pm FROM groups WHERE status='A'";
    &debug("QUERY: $query\n");
    my $groups = $dbh->selectall_arrayref($query);

    $query = "SELECT group_artifact_id FROM artifact_group_list";
    my @valid_g_artifact_id = map { $_->[0] } @{ $dbh->selectall_arrayref($query) };
    $query = "SELECT group_forum_id FROM forum_group_list";
    my @valid_g_forum_id = map { $_->[0] } @{ $dbh->selectall_arrayref($query) };
    $query = "SELECT group_project_id FROM project_group_list";
    my @valid_g_project_id = map { $_->[0] } @{ $dbh->selectall_arrayref($query) };
    
    #print "Valid trackers: @valid_g_artifact_id\n";
    #print "Valid forums: @valid_g_forum_id\n";
    #print "Valid projects: @valid_g_project_id\n";
    
    foreach my $group (@{$groups}) {
	print "Doing group $group->[0]...\n";
	# Fetch rights associated to roles
	$query = "SELECT rs.role_id, section_name, ref_id, value 
		  FROM role r, role_setting rs WHERE 
		  r.role_id = rs.role_id AND
		  r.group_id='$group->[0]'";
	&debug("QUERY: $query\n");
	my $role_setting = $dbh->selectall_arrayref($query);
	my %roles;
	foreach my $setting (@{$role_setting}) {
	    # Roles may list default objects which have been suppressed ...
	    # We need to skip them
	    next if (($setting->[1] eq "tracker") and (! grep { /^$setting->[2]$/ } @valid_g_artifact_id));
	    next if (($setting->[1] eq "forum") and (! grep { /^$setting->[2]$/ } @valid_g_forum_id));
	    next if (($setting->[1] eq "pm") and (! grep { /^$setting->[2]$/ } @valid_g_project_id));
	    $roles{$setting->[0]}{$setting->[1]}{$setting->[2]} = $setting->[3];
	    #print "Setting role_id sec_name ref_id value: @{$setting}\n";
	}
	# Loop over the group members
	$query = "SELECT user_id, role_id FROM user_group WHERE group_id='$group->[0]'";
	&debug("QUERY: $query\n");
	my $users = $dbh->selectall_arrayref($query);
	foreach my $user (@{$users}) {
	    if ($group->[1] and				    #use_tracker 
		keys %{$roles{$user->[1]}{'tracker'}}) {    #role has right on trackers
		# Get a list of the user's perm on trackers from this group
		$query = "SELECT group_artifact_id, perm_level FROM artifact_perm
		    WHERE user_id='$user->[0]' AND
		    group_artifact_id IN (";
		$query .= join(", ", keys %{$roles{$user->[1]}{'tracker'}}) . ")";
		&debug("QUERY: $query\n");
		my $list_rights = $dbh->selectall_arrayref($query);
		my %rights = map { $_->[0] => $_->[1] } @{$list_rights};
		foreach my $aid (keys %{$roles{$user->[1]}{'tracker'}}) {
		    if (grep { /^$aid$/ } keys %rights) {
			# User is registered, check the rights
			if ($roles{$user->[1]}{'tracker'}{$aid} != $rights{$aid}) {
			    # Right differs !
			    print "PROBLEM: Right on user $user->[0], group_aid $aid differs.\n";
			    my $level = $roles{$user->[1]}{'tracker'}{$aid};
			    $query = "UPDATE artifact_perm SET perm_level='$level' " .
				     "WHERE group_artifact_id='$aid' AND user_id='$user->[0]'";
			    &debug("FIX: $query\n");
			    $dbh->do($query) if $fix;
			}
		    } else {
			# User is not registered in this artifact type !
			print "PROBLEM: User $user->[0] is not registered for group_aid $aid.\n";
			my $level = $roles{$user->[1]}{'tracker'}{$aid};
			$query = "INSERT INTO artifact_perm (group_artifact_id, user_id, perm_level)" .
				 " VALUES('$aid', '$user->[0]', '$level')";
			&debug("FIX: $query\n");
			$dbh->do($query) if $fix;
		    }
		}
	    }
	    
	    $dbh->commit();
	    
	    if ($group->[2] and
		keys %{$roles{$user->[1]}{'forum'}}) { #use_forum
		$query = "SELECT group_forum_id, perm_level FROM forum_perm
		    WHERE user_id='$user->[0]' AND
		    group_forum_id IN (";
		$query .= join(", ", keys %{$roles{$user->[1]}{'forum'}}) . ")";
		&debug("QUERY: $query\n");
		my $list_rights = $dbh->selectall_arrayref($query);
		my %rights = map { $_->[0] => $_->[1] } @{$list_rights};
		foreach my $aid (keys %{$roles{$user->[1]}{'forum'}}) {
		    if (grep { /^$aid$/ } keys %rights) {
			# User is registered, check the rights
			if ($roles{$user->[1]}{'forum'}{$aid} != $rights{$aid}) {
			    # Right differs !
			    print "PROBLEM: Right on user $user->[0], group_forum_id $aid differs.\n";
			    my $level = $roles{$user->[1]}{'forum'}{$aid};
			    $query = "UPDATE forum_perm SET perm_level='$level' " .
				     "WHERE group_forum_id='$aid' AND user_id='$user->[0]'";
			    &debug("FIX: $query\n");
			    $dbh->do($query) if $fix;
			}
		    } else {
			# User is not registered in this artifact type !
			print "PROBLEM: User $user->[0] is not registered for group_forum_id $aid.\n";
			my $level = $roles{$user->[1]}{'forum'}{$aid};
			$query = "INSERT INTO forum_perm (group_forum_id, user_id, perm_level)" .
				 " VALUES('$aid', '$user->[0]', '$level')";
			&debug("FIX: $query\n");
			$dbh->do($query) if $fix;
		    }
		}
	    }

	    $dbh->commit();
	    
	    if ($group->[3] and
		keys %{$roles{$user->[1]}{'pm'}}) { #use_pm project_manager
		$query = "SELECT group_project_id, perm_level FROM project_perm
		    WHERE user_id='$user->[0]' AND
		    group_project_id IN (";
		$query .= join(", ", keys %{$roles{$user->[1]}{'pm'}}) . ")";
		&debug("QUERY: $query\n");
		my $list_rights = $dbh->selectall_arrayref($query);
		my %rights = map { $_->[0] => $_->[1] } @{$list_rights};
		foreach my $aid (keys %{$roles{$user->[1]}{'pm'}}) {
		    if (grep { /^$aid$/ } keys %rights) {
			# User is registered, check the rights
			if ($roles{$user->[1]}{'pm'}{$aid} != $rights{$aid}) {
			    # Right differs !
			    print "PROBLEM: Right on user $user->[0], group_project_id $aid differs.\n";
			    my $level = $roles{$user->[1]}{'pm'}{$aid};
			    $query = "UPDATE project_perm SET perm_level='$level' " .
				     "WHERE group_project_id='$aid' AND user_id='$user->[0]'";
			    &debug("FIX: $query\n");
			    $dbh->do($query) if $fix;
			}
		    } else {
			# User is not registered in this artifact type !
			print "PROBLEM: User $user->[0] is not registered for group_project_id $aid.\n";
			my $level = $roles{$user->[1]}{'pm'}{$aid};
			$query = "INSERT INTO project_perm (group_project_id, user_id, perm_level)" .
				 " VALUES('$aid', '$user->[0]', '$level')";
			&debug("FIX: $query\n");
			$dbh->do($query) if $fix;
		    }
		}
	    }

	    $dbh->commit();
	}
    }

    # There should be a commit at the end of every block above.
    # If there is not, then it might be symptomatic of a problem.
    # For safety, we roll back.
    $dbh->rollback ();
};

if ($@) {
    warn "Transaction aborted because $@" ;
    &debug ("Transaction aborted because $@") ;
    &debug ("Last SQL query was:\n$query\n(end of query)") ;
    $dbh->rollback ;
    &debug ("Please report this bug on the Debian bug-tracking system.") ;
    &debug ("Please include the previous messages as well to help debugging.") ;
    &debug ("You should not worry too much about this,") ;
    &debug ("your DB is still in a consistent state and should be usable.") ;
    exit 1 ;
}

$dbh->rollback ;
$dbh->disconnect ;

sub debug($) {
    my $log = shift;
    print $log;
}
