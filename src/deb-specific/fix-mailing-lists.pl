#!/usr/bin/perl -w
#
# Fix the mailing-lists if they have been broken in previous versions
# Roland Mas <lolando@debian.org>

use DBI ;
use strict ;
use diagnostics ;
use File::Temp qw/ :mktemp  /;

use vars qw/ $dbh $sys_lists_host $sys_dbname $sys_dbuser $sys_dbpasswd $domain_name / ;

use vars qw// ;

sub debug ( $ ) ;

require("/usr/share/gforge/lib/include.pl");  # Include all the predefined functions

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
              WHERE mail_group_list.status = 3
                    AND mail_group_list.list_admin = users.user_id" ; # Status = 3: list already created
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
	my ($cmd) ;

	($group_list_id, $listname, $user_name, $password, $description)= @array ;

	$tmp = mktemp ("/tmp/XXXXXX") ;
	$cmd = "/usr/lib/mailman/bin/config_list -o $tmp $listname" ;
	#print "cmd = <$cmd>\n" ;
	system ($cmd) ;
	open CONFIG, ">>$tmp" ;
	print CONFIG "description = \"$description\"\n" ;
	print CONFIG "host_name = '$sys_lists_host'\n" ;
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
	$cmd= "/usr/lib/mailman/bin/withlist -l -r fix_url $listname -u $sys_lists_host" ;
	#print "cmd = <$cmd>\n" ;
	system ($cmd) ;

	#debug "Rolling back -- nothing should have changed anyway." ;
	$dbh->rollback () ;
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
