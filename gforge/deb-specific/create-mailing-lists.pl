#!/usr/bin/perl -w
#
# $Id$
#
# Create the mailing-lists from the database
# Roland Mas, debian-sf (Sourceforge for Debian)

use DBI ;
use strict ;
use diagnostics ;
use File::Temp qw/ :mktemp  /;

use vars qw/ $dbh $sys_lists_host $domain_name / ;

use vars qw// ;

sub debug ( $ ) ;

require ("/usr/lib/sourceforge/lib/include.pl") ; # Include all the predefined functions 
require ("/etc/sourceforge/local.pl") ;

&db_connect ;

$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;
eval {
    my ($query, $sth, @array, @lines, $line) ;

    $query = "SELECT mail_group_list.group_list_id,
                     mail_group_list.list_name,
                     users.user_name,
                     mail_group_list.password,
                     mail_group_list.description
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
	my ($group_list_id, $listname, $user_name, $password, $description) ;
	my ($tmp) ;

	($group_list_id, $listname, $user_name, $password, $description)= @array ;
	my $cmd = "/usr/sbin/newlist -q $listname $user_name\@users.$domain_name $password >/dev/null 2>&1" ;
	#print "cmd = <$cmd>\n" ;
	system ($cmd) ;

	$query = "UPDATE mail_group_list SET status = 2 where group_list_id = group_list_id" ; # Status = 2: list created on Mailman
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;

	$tmp = mktemp ("/tmp/XXXXXX") ;
	$cmd = "/usr/lib/mailman/bin/config_list -o $tmp $listname" ;
	#print "cmd = <$cmd>\n" ;
	system ($cmd) ;
	open CONFIG, ">>$tmp" ;
	print CONFIG "description = '$description'\n" ;
	print CONFIG "host_name = '$sys_lists_host'\n" ;
	print CONFIG "web_page_url = 'http://$sys_lists_host/cgi-bin/mailman/'\n" ;
	close CONFIG ;
	$cmd = "/usr/lib/mailman/bin/config_list -i $tmp $listname" ;
	#print "cmd = <$cmd>\n" ;
	system ($cmd) ;
	unlink $tmp ;

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
