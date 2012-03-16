#!/usr/bin/perl -w
#
# Create the mailing-lists from the database
# Copyright 2001-2003, 2009 Roland Mas <lolando@debian.org>
# Copyright 2003, 2004, Christian Bayle <bayle@debian.org>
# Copyright 2005, INRIA (David Margery and Soraya Arias)

use DBI ;
use strict ;
use diagnostics ;
use File::Temp qw/ :mktemp  /;

use vars qw/ $dbh $sys_lists_host $sys_users_host / ;

use vars qw// ;

sub debug ( $ ) ;

require ("/usr/share/gforge/lib/include.pl") ; # Include all the predefined functions 

&db_connect ;

$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;
eval {
    my ($query, $sth, @array, @lines, $line) ;

    $query = "SELECT mail_group_list.group_list_id,
                     mail_group_list.list_name,
                     users.user_name,
                     mail_group_list.password,
                     mail_group_list.description,
                     mail_group_list.is_public
              FROM mail_group_list, users
              WHERE mail_group_list.status = 1
                    AND mail_group_list.list_admin = users.user_id" ; # Status = 1: list just created on the website
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    while (my @myarray = $sth->fetchrow_array ()) {
	push @lines, \@myarray ;
    }
    $sth->finish () ;

    foreach $line (@lines) {
	@array = @{$line} ;
	my ($group_list_id, $listname, $user_name, $password, $description, $is_public) ;
	my ($tmp) ;

	($group_list_id, $listname, $user_name, $password, $description, $is_public)= @array ;
	next if $listname eq '' ;
	next if $listname eq '.' ;
	next if $listname eq '..' ;
	next if $listname !~ /^[a-z0-9\-_\.]*$/ ;

	my $cmd = "/usr/sbin/newlist -q $listname $user_name\@$sys_users_host $password >/dev/null 2>&1" ;
	#print "cmd = <$cmd>\n" ;
	system ($cmd) ;

	$query = "UPDATE mail_group_list SET status = 2 where group_list_id = group_list_id" ; # Status = 2: list created on Mailman
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;

	$tmp = mktemp ("/tmp/XXXXXX") ;
	$cmd = "/usr/lib/mailman/bin/config_list -o $tmp $listname" ;
	#print "cmd = <$cmd>\n" ;
# Commented out on Matt Hope <dopey@debian.org> advice
# To be revised by Roland Mas
#	system ($cmd) ;
	open CONFIG, ">>$tmp" ;
	print CONFIG "description = \"$description\"\n" ;
	print CONFIG "host_name = '$sys_lists_host'\n" ;
	if (!$is_public) {
	    print CONFIG "archive_private = True\n" ;
	    print CONFIG "advertised = False\n" ;
	    print CONFIG "subscribe_policy = 3\n" ;
	    ## Reject mails sent by non-members
	    print CONFIG "generic_nonmember_action = 2\n";
	    ## Do not forward auto discard message
	    print CONFIG "forward_auto_discards = 0\n";
	} else {
	    print CONFIG "archive_private = False\n" ;
	    print CONFIG "advertised = True\n" ;
	    print CONFIG "subscribe_policy = 1\n" ;
	}
	close CONFIG ;
	$cmd = "/usr/lib/mailman/bin/config_list -i $tmp $listname" ;
	#print "cmd = <$cmd>\n" ;
	system ($cmd) ;
	unlink $tmp ;

	my $urlpattern;
	if (&forge_get_config ('use_ssl') eq 'yes') {
	    $urlpattern = 'https://%s/cgi-bin/mailman/';
	} else {
	    $urlpattern = 'http://%s/cgi-bin/mailman/';
	}
	$cmd= "/usr/lib/mailman/bin/withlist -l -r fix_url $listname -u $sys_lists_host -p '$urlpattern'" ;
	#print "cmd = <$cmd>\n" ;
	system ($cmd) ;

	$query = "UPDATE mail_group_list SET status = 3 where group_list_id = group_list_id" ; # Status = 3: list configured on Mailman
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;

	#debug "Committing." ;
	$dbh->commit () ;
    }

    $query = "SELECT mail_group_list.group_list_id,
                     mail_group_list.list_name,
                     users.user_name,
                     mail_group_list.password,
                     mail_group_list.description,
                     mail_group_list.is_public
              FROM mail_group_list, users
              WHERE mail_group_list.status = 4
                    AND mail_group_list.list_admin = users.user_id" ; # Status = 4: password reset requested
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    while (my @myarray = $sth->fetchrow_array ()) {
	push @lines, \@myarray ;
    }
    $sth->finish () ;

    foreach $line (@lines) {
	@array = @{$line} ;
	my ($group_list_id, $listname, $user_name, $password, $description, $is_public) ;
	my ($tmp) ;

	($group_list_id, $listname, $user_name, $password, $description, $is_public)= @array ;
	next if $listname eq '' ;
	next if $listname eq '.' ;
	next if $listname eq '..' ;
	next if $listname !~ /^[a-z0-9\-_\.]*$/ ;

	my $cmd = "/usr/lib/mailman/bin/change_pw -l $listname >/dev/null 2>&1" ;
	system ($cmd) ;

	$query = "UPDATE mail_group_list SET status = 3 where group_list_id = group_list_id" ; # Status = 3: list configured on Mailman
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;

	#debug "Committing." ;
	$dbh->commit () ;
    }
    
    # There should be a commit at the end of every block above.
    # If there is not, then it might be symptomatic of a problem.
    # For safety, we roll back.
    $dbh->rollback ();
};

if ($@) {
    warn "Transaction aborted because $@" ;
    debug "Transaction aborted because $@" ;
    $dbh->rollback ;
    debug "Please report this bug on the Debian bug-tracking system." ;
    debug "Please include the previous messages as well to help debugging." ;
    exit 1 ;
}

$dbh->rollback ;
$dbh->disconnect ;

sub debug ( $ ) {
    my $v = shift ;
    chomp $v ;
    print STDERR "$v\n" ;
}
