#!/usr/bin/perl -w
#
# get_news_notapproved.pl: script to get the news not yet approved 
#   by Vicente J. Ruiz Jurado (vjrj AT ourproject.org) Apr-2004
#
# depends: libgetopt-mixed-perl (Getopt::Long), libdbi-perl (DBI),
#          libtext-autoformat-perl (Text::Autoformat)
#          libmail-sendmail-perl (Mail::Sendmail)
#
use DBI;
use Text::Autoformat;
use Getopt::Long qw(:config require_order);
use Mail::Sendmail;

use strict;

require("/usr/share/gforge/lib/include.pl");  # Include all predefined functions

use vars qw/ $server_admin $sys_name $sys_default_domain /;

# DB 
#-------------------------------------------------------------------------------
use vars qw/ $dbh / ; # Predeclaration of global vars

# Variables
#-------------------------------------------------------------------------------

my $numArgs = @ARGV;
my @args = @ARGV;
my $therearenews = 0;

sub usage();

my $debug;
my @results_array;
my $emailformatted;

# Options check
#-------------------------------------------------------------------------------

my $resultOptions = GetOptions(
"debug" => \$debug
);

unless (($debug && $resultOptions == 2) || ($resultOptions == 1)) {
	usage();
	exit(1);
}

chomp($server_admin=`/usr/share/gforge/bin/forge_get_config admin_email`);

# Start to get de News
#-------------------------------------------------------------------------------

if ($debug) {print STDERR "Getting the news not approved.\n"};

&db_connect;
$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;

my $old_date = time()-60*60*24*30;
my $query = "SELECT group_name,summary,details 
	FROM news_bytes n, groups g 
	WHERE is_approved = 0 
	AND n.group_id=g.group_id
	AND n.post_date > '$old_date'
	AND g.is_public=1
	AND g.status='A'
	ORDER BY post_date";

my $sth = $dbh->prepare($query);
$sth->execute();

while (my @array = $sth->fetchrow_array ()) {
	push @results_array, \@array ;
}
$sth->execute() or die "Problems with the query '$query' in DB";
$sth->finish() or die "Problems with the query '$query' in DB";
$dbh->commit or die $dbh->errstr;

foreach my $newsnotapprob (@results_array) {
	$therearenews = 1;
	my ($group_name, $summary, $details) = @{$newsnotapprob};
	my $title = "$group_name: $summary\n";
	$emailformatted .= autoformat $title, {  all => 1, left=>0, right=>78 };
	$emailformatted .= "----------------------------------------------------------------------\n";
	$emailformatted .= autoformat $details, {  all => 1, left=>8, right=>78 };
	$emailformatted .= "\n\n";
}

if ($therearenews) {
	if ($debug) {print STDERR "Sending the news not approved.\n"};
  $emailformatted .= "Please visit: http://$sys_default_domain/news/admin/";
	$emailformatted .= "\n\n";
	my %mail = ( To      => "$server_admin",
		     From    => "noreply\@$sys_default_domain",
		     Subject => "$sys_name pending news",
		     Message => $emailformatted
		   );
	$mail{'Content-type'} = 'text/plain; charset="UTF-8"';
	sendmail(%mail) or die $Mail::Sendmail::error;
}
else {
	if ($debug) {print STDERR "No news to approved.\n"};
}

if ($debug) {print STDERR "get_news_notapproved process finished ok\n"};
exit(0);

# Funcitions
#-------------------------------------------------------------------------------

sub usage() {
	print STDERR "usage: get_news_notapproved.pl [--debug]\n";
}
