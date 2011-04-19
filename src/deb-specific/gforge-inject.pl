#!/usr/bin/perl -w
#
# Debian-specific script to inject local user/group data into the Sourceforge database
# Roland Mas <lolando@debian.org>

use strict ;
use warnings ;


use vars qw/ %passwd %group %shadow
    %uid2login %gid2groupname
    %users %groups %user_group
    $domain_name $datafiles_prefix $organisation_name / ;


use DBI ;
use Data::Dumper ;
use vars qw/$dbh @reqlist $query/ ;
use vars qw/$sys_default_domain $sys_scm_host $sys_download_host
    $sys_shell_host $sys_users_host $sys_docs_host $sys_lists_host
    $sys_dns1_host $sys_dns2_host $FTPINCOMING_DIR $FTPFILES_DIR
    $sys_urlroot $sf_cache_dir $sys_name $sys_themeroot
    $sys_news_group $sys_dbhost $sys_dbname $sys_dbuser $sys_dbpasswd
    $sys_ldap_base_dn $sys_ldap_host $admin_login $admin_password
    $domain_name $newsadmin_groupid $statsadmin_groupid
    $skill_list/ ;

sub debug ( $ ) ;
require ("/usr/share/gforge/lib/include.pl") ;

###
# Local customisation
###

$datafiles_prefix = "." ;
$domain_name = "alioth.debian.org" ;
$organisation_name = "Debian" ;

###
# Load data files
###

open PASSWD, "$datafiles_prefix/passwd" ;
open SHADOW, "$datafiles_prefix/shadow" ;
open GROUP, "$datafiles_prefix/group" ;

while (<PASSWD>) {
    my ($login, $name, @list) ;
    chomp ;
    @list = split /:/ ;
    $login = $list[0] ;
    $passwd{$login} = {
	login 	  => $list[0],
	passwd 	  => $list[1],
	uid 	  => $list[2],
	gid 	  => $list[3],
	gecos 	  => $list[4],
	directory => $list[5],
	shell 	  => $list[6],
    } ;
    ($name, undef) = split /,/, $passwd{$login}{gecos}, 2;
    $name = '' unless (defined ($name)) ;
    $passwd{$login}{name} = $name ;
    $uid2login{$passwd{$login}{uid}} = $login ;
}

while (<GROUP>) {
    my ($groupname, @list) ;
    chomp ;
    @list = split /:/ ;
    $groupname = $list[0] ;
    $group{$groupname} = {
	group 	  => $list[0],
	passwd 	  => $list[1],
	gid 	  => $list[2],
	users 	  => $list[3],
    } ;
    $group{$groupname}{users} = "" if ! defined ($group{$groupname}{users}) ;
    $gid2groupname{$group{$groupname}{gid}} = $groupname ;
}

while (<SHADOW>) {
    my @list ;
    chomp ;
    @list = split /:/ ;
    $shadow{$list[0]} = {
	login 	  	       => $list[0],
	passwd 	  	       => $list[1],
	lastchangeddate        => $list[2],
	daysbeforechangeable   => $list[3],
	daysbeforemustchange   => $list[4],
	dayswarnbeforeexpire   => $list[5],
	daysafterexpiredisable => $list[6],
	disableddate 	       => $list[7],
	reserved 	       => $list[8],
    } ;
}

close PASSWD ;
close SHADOW ;
close GROUP ;

# print Dumper \%passwd ;
# print Dumper \%group ;
# print Dumper \%shadow ;
# print Dumper \%uid2login ;
# print Dumper \%gid2groupname ;

###
# Process the loaded data
###

foreach my $user (keys %passwd) {
    my ($gid, $gname) ;
    $users{$user}{user_id} 	      	= $passwd{$user}{uid} ;
    $users{$user}{user_name} 	      	= $user ;
    $users{$user}{email} 	      	= "$user\@debian.org" ;
    $users{$user}{user_pw} 	      	= 'UNKNOWN / OUT OF DATE' ;
    $users{$user}{realname} 	      	= $passwd{$user}{name} ;
    $users{$user}{shell} 	      	= $passwd{$user}{shell} ;
    $users{$user}{unix_pw} 	      	= $shadow{$user}{passwd} ;
    if ($shadow{$user}{passwd} =~ /^[x!]$/) {
	$users{$user}{status} 	      	        = 'N' ;
	$users{$user}{unix_status} 	      	= 'N' ;
    } else {
	$users{$user}{status} 	      	        = 'A' ;
	$users{$user}{unix_status} 	      	= 'A' ;
    }
    $users{$user}{unix_uid} 	      	= $passwd{$user}{uid} ;
    # $users{$user}{unix_box} 	      	= '' ;
    $users{$user}{add_date} 	      	= time() ;
    # $users{$user}{confirm_hash}       = '' ;
    # $users{$user}{mail_siteupdates}   = '' ;
    # $users{$user}{mail_va} 	      	= '' ;
    # $users{$user}{authorized_keys}    = '' ;
    # $users{$user}{email_new} 	      	= '' ;
    # $users{$user}{people_view_skills} = '' ;
    # $users{$user}{people_resume}      = '' ;
    # $users{$user}{timezone} 	      	= '' ;
    # $users{$user}{language} 	      	= '' ;
    # $users{$user}{block_ratings}      = '' ;

    $gid = $passwd{$user}{gid} ;
    $gname = $gid2groupname{$gid} ;

    $user_group{$gname}{$user} = 1
    }

# print "\%users:\n", Dumper \%users ;

foreach my $group (keys %group) {
    my @users ;
    $groups{$group}{group_id} 	       = $group{$group}{gid} ;
    $groups{$group}{group_name}        = "$organisation_name group " . $group{$group}{group} ;
    $groups{$group}{homepage} 	       = $group{$group}{group} . ".$domain_name" ;
    $groups{$group}{is_public} 	       = 0 ;
    $groups{$group}{status} 	       = 'A' ;
    $groups{$group}{unix_group_name}   = $group{$group}{group} ;
    $groups{$group}{unix_box}          = "shell";
    $groups{$group}{http_domain}       = $group{$group}{group} . ".$domain_name" ;
    $groups{$group}{short_description} = "$organisation_name group " . $group{$group}{group} ;
    $groups{$group}{cvs_box} 	       = "cvs.$domain_name" ;
    $groups{$group}{license} 	       = "Local group" ;
    $groups{$group}{register_purpose}  = "$organisation_name group " . $group{$group}{group} ;
    $groups{$group}{license_other}     = "" ;
    $groups{$group}{register_time}     = time () ;
    # $groups{$group}{rand_hash} 	       = "" ;
    # $groups{$group}{use_mail}          = 1 ;
    # $groups{$group}{use_survey}        = 1 ;
    # $groups{$group}{use_forum}         = 1 ;
    # $groups{$group}{use_pm}            = 1 ;
    # $groups{$group}{use_cvs}           = 1 ;
    # $groups{$group}{use_news}          = 1 ;
    # $groups{$group}{type}              = 1 ;
    # $groups{$group}{use_docman}        = 1 ;
    # $groups{$group}{new_task_address}  = "" ;
    # $groups{$group}{send_all_tasks}    = 0 ;
    # $groups{$group}{use_pm_depend_box} = 1 ;
    # $groups{$group}{use_ftp}           = 1 ;
    # $groups{$group}{use_tracker}       = 1 ;
    # $groups{$group}{use_frs}           = 1 ;
    # $groups{$group}{use_stats}         = 1 ;
    # $groups{$group}{enable_pserver}    = 1 ;
    # $groups{$group}{enable_anoncvs}    = 1 ;

    @users = split /,/, $group{$group}{users} ;
    foreach my $user (@users) {
	$user_group{$group}{$user} = 1 ;
    }
}

# print "\%groups:\n", Dumper \%groups ;

# print "\%user_group:\n", Dumper \%user_group ;

###
# Inject the data into the database
###

&db_connect ;
# debug "Connected to the database OK." ;

$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;

eval {
    my ($sth, @array) ;
    debug "Starting users injection." ;

    ###
    # Inject users
    ###

    foreach my $user (keys %users) {
	$query = "SELECT count(*) FROM users WHERE user_name = '$user'";
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	@array = $sth->fetchrow_array () ;
	$sth->finish () ;

	if ($array [0] == 0) {
	    debug "User $user does not exist, creating." ;
	    my $realname = $users{$user}{realname} ;
	    $realname = substr ($realname, 0, 32) ;
	    $realname = $dbh->quote ($realname) ;
	    my $unix_pw = qx(/usr/bin/makepasswd --minchar 8 --maxchar 8) ;
	    $unix_pw = $dbh->quote ($unix_pw) ;
	    $query = "INSERT INTO users (user_name, email,
                                         user_pw, realname, status,
                                         shell, unix_pw, unix_status,
                                         unix_uid, add_date)
                      VALUES ('$users{$user}{user_name}',
                              '$users{$user}{email}',
                              $unix_pw,
                              $realname,
                              '$users{$user}{status}',
                              '$users{$user}{shell}',
                              '$users{$user}{unix_pw}',
                              '$users{$user}{unix_status}',
                              $users{$user}{unix_uid},
                              $users{$user}{add_date})" ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	} else {
	    debug "User $user already exists, updating." ;
	    my $realname = $users{$user}{realname} ;
	    $realname = substr ($realname, 0, 32) ;
	    $realname = $dbh->quote ($realname) ;
	    $query = "UPDATE users
                      SET email       = '$users{$user}{email}',
                          realname    = $realname,
                          status      = '$users{$user}{status}',
                          shell       = '$users{$user}{shell}',
                          unix_status = '$users{$user}{unix_status}'
                      WHERE user_name = '$users{$user}{user_name}'" ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
    }

    debug "Committing..." ;
    $dbh->commit () ;
    debug "Users injection completed correctly." ;

    debug "Starting groups injection." ;

    ###
    # Inject groups
    ###

    foreach my $group (keys %groups) {
	$query = "SELECT count(*) FROM groups WHERE unix_group_name = '$group'";
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	@array = $sth->fetchrow_array () ;
	$sth->finish () ;

	if ($array [0] == 0) {
	    debug "Group $group does not exist, creating." ;
	    $query = "INSERT INTO groups (group_id, group_name,
                                          homepage, is_public, status,
                                          unix_group_name, unix_box,
                                          http_domain,
                                          short_description, cvs_box,
                                          license, register_purpose,
                                          license_other, register_time)
                      VALUES ($groups{$group}{group_id},
                              '$groups{$group}{group_name}',
                              '$groups{$group}{homepage}',
                              $groups{$group}{is_public},
                              '$groups{$group}{status}',
                              '$groups{$group}{unix_group_name}',
                              '$groups{$group}{unix_box}',
                              '$groups{$group}{http_domain}',
                              '$groups{$group}{short_description}',
                              '$groups{$group}{cvs_box}',
                              '$groups{$group}{license}',
                              '$groups{$group}{register_purpose}',
                              '$groups{$group}{license_other}',
                              $groups{$group}{register_time})" ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	} else {
	    debug "Group $group already exists, nothing to do." ;
	}
    }


    debug "Committing..." ;
    $dbh->commit () ;
    debug "Groups injection completed correctly." ;

    debug "Starting user-group relationships injection." ;

    ###
    # Inject user-groups relationships
    ###

    foreach my $group (keys %user_group) {
	if (defined ($group{$group}{gid})) {
	    my $gid = $group{$group}{gid} ;
	    foreach my $user (keys %{$user_group{$group}}) {
		if (defined ($passwd{$user}{uid})) {
		    my $uid = $passwd{$user}{uid} ;
		    $query = "SELECT count(*)
                      FROM user_group
                      WHERE group_id = $gid
                        AND user_id  = (SELECT user_id FROM users WHERE unix_uid=$uid)";
		    # debug $query ;
		    $sth = $dbh->prepare ($query) ;
		    $sth->execute () ;
		    @array = $sth->fetchrow_array () ;
		    $sth->finish () ;
		    
		    if ($array [0] == 0) {
			debug "User-group relation $uid - $gid does not exist, creating." ;
			$query = "INSERT INTO user_group (user_id, group_id)
                          VALUES ((SELECT user_id FROM users WHERE user_name='$users{$user}{user_name}'), $gid)" ;
			# debug $query ;
			$sth = $dbh->prepare ($query) ;
			$sth->execute () ;
			$sth->finish () ;
		    } else {
			debug "User-group relation $uid - $gid already exists, nothing to do." ;
		    }
		}
	    }
	}
    }

    debug "Committing..." ;
    $dbh->commit () ;
    debug "User-group relationships injection completed correctly." ;

    # There should be a commit at the end of every block above.
    # If there is not, then it might be symptomatic of a problem.
    # For safety, we roll back.
    $dbh->rollback ();
};

if ($@) {
    warn "Transaction aborted because $@" ;

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

sub debug ( $ ) {
    my $v = shift ;
    chomp $v ;
    print STDERR "$v\n" ;
}
