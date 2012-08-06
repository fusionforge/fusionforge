#!/usr/bin/perl -w
#
# get_assoc_sites_projects.pl: script to get the projects of 
#   sites associated, to try to make a ring of gforges sindicates
#   by Vicente J. Ruiz Jurado (vjrj AT ourproject.org) Mar-2004
#
# depends: libgetopt-mixed-perl (Getopt::Long), libdbi-perl (DBI),
#          libxml-rss-perl (XML::RSS), libwww-perl (LWP::Simple),
#          libcrypt-ssleay-perl (for https sites),
#          libunicode-string-perl (Unicode::String)
#
use DBI;
use Getopt::Long qw(:config require_order);
use XML::RSS;
use LWP::UserAgent ;
use Unicode::String qw(latin1 utf8);

use strict;

require("/usr/share/gforge/lib/include.pl");  # Include all predefined functions

# DB 
#-------------------------------------------------------------------------------
use vars qw/ $dbh / ; # Predeclaration of global vars

# Variables
#-------------------------------------------------------------------------------

my $numArgs = @ARGV;
my @args = @ARGV;

sub usage();
sub changeSiteStatus($$);
sub deleteProjectsOfSite($);
sub myescape($);


my $debug;
my $debugsql;
my $assoc_site;
my @results_array;
my ($projecttitle, $projectlink, $projectdesc);

my $ua = new LWP::UserAgent ;
# Default get timeout to 30 seconds
$ua->timeout(30);
$ua->env_proxy ;

# Options check
#-------------------------------------------------------------------------------

my $resultOptions = GetOptions(
"debug" => \$debug,
"debugsql" => \$debugsql
);

unless (($debug && $resultOptions == 2) || 
                                ($debugsql && $resultOptions == 2) || 
                                (($debug) && ($debugsql) && $resultOptions == 3) || 
                                ($resultOptions == 1)) {
        usage();
        exit(1);
}

# Start to get de RSS
#-------------------------------------------------------------------------------

if ($debug) {print STDERR "Getting the associated sites.\n"};

&db_connect;
$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;

my $query = "SELECT assoc_site_id, title, link, onlysw, status_id, rank 
        FROM plugin_globalsearch_assoc_site WHERE enabled='t' ORDER BY rank";

my $sth = $dbh->prepare($query);
$sth->execute();

while (my @array = $sth->fetchrow_array ()) {
        push @results_array, \@array ;
}
$sth->execute() or die "Problems with the query '$query' in DB";
$sth->finish() or die "Problems with the query '$query' in DB";
$dbh->commit or die $dbh->errstr;

foreach $assoc_site (@results_array) {
        my ($assoc_site_id, $title, $link, $onlysw, $enabled, $status_id, $rank) = @{$assoc_site};
        my $urlrss = $link."/export/rss_sfprojects.php?showall=1";
        my $response = $ua->get($urlrss);
	
        if ($response->is_success) {
   	        my $content = $response->content ;
                # Correct RSS get
                if ($debug) {print STDERR "$title get ok.\n"};

                my $rss = new XML::RSS (version => '0.91', encoding => "UTF-8");

                eval {$rss->parse($content);}; 
                if ($@) {
                        # Parse error
                        if ($debug) {print STDERR "ERROR parsing $title.\n"};

                        # Site in db to status unparsable
                        changeSiteStatus($assoc_site_id, 4);
                        deleteProjectsOfSite($assoc_site_id);
                }
                else {
                        # Parse ok
                        deleteProjectsOfSite($assoc_site_id);
                        # Insert projects in db
                        if ($debug) {print STDERR "Inserting site $title projects\n"};
                        foreach my $item (@{$rss->{'items'}}) {
                                next unless (defined($item->{'title'}) && defined($item->{'link'}) && defined($item->{'description'}));
                                # utf8 checking
                                $projecttitle = myescape(utf8($item->{'title'}));
                                $projectlink = myescape(utf8($item->{'link'}));
                                $projectdesc = myescape(utf8($item->{'description'}));

                                my $insert = "INSERT INTO plugin_globalsearch_assoc_site_project ".
                                        "(assoc_site_id, project_title, project_link, project_description) ".
                                        "VALUES ('$assoc_site_id','$projecttitle','$projectlink','$projectdesc')";

                                if ($debugsql) {print STDERR "SQL: $insert\n"};

                                my $sth = $dbh->prepare($insert);
                                $sth->execute() or die "Problems with the insert of '$insert' in DB";
                                $sth->finish() or die "Problems with the insert of '$insert' in DB";
                                $dbh->commit or die $dbh->errstr;                                
                        }

                        # Site in db to status ok
                        changeSiteStatus($assoc_site_id, 2);
                }
        }
        else {
                # Incorrect RSS get
                if ($debug) {
		    print STDERR "$urlrss get fail:\n" ;
		    print STDERR $response->status_line()."\n" ;
		};

                # RSS in db to status fail
                changeSiteStatus($assoc_site_id, 3);
                deleteProjectsOfSite($assoc_site_id);
        }
}

if ($debug) {print STDERR "get_assoc_sites_projects process finished ok\n"};
exit(0);

# Funcitions
#-------------------------------------------------------------------------------

sub usage() {
        print STDERR "usage: get_assoc_sites_projects.pl [--debug] [--debugsql]\n";
}


sub changeSiteStatus($$) {
        my $site = shift;
        my $status = shift;

        my $update = "UPDATE plugin_globalsearch_assoc_site SET status_id='$status' WHERE assoc_site_id='$site'";

        if ($debugsql) {print STDERR "SQL: $update\n"};
        my $sth = $dbh->prepare($update);
        $sth->execute() or die "Problems with the update of '$update' in DB";
        $sth->finish() or die "Problems with the update of '$update' in DB";
        $dbh->commit or die $dbh->errstr;

        return 0;
}


sub deleteProjectsOfSite($) {
        my $site = shift;

        if ($debug) {print STDERR "Deleting the projects of site $site.\n"};

        my $delete = "DELETE FROM plugin_globalsearch_assoc_site_project WHERE assoc_site_id='$site'";

        if ($debugsql) {print STDERR "SQL: $delete\n"};

        my $sth = $dbh->prepare($delete);
        $sth->execute() or die "Problems with the delete of '$delete' in DB";
        $sth->finish() or die "Problems with the delete of '$delete' in DB";
        $dbh->commit or die $dbh->errstr;

        return 0;
}

sub myescape($) {
        my $stringtoesc = shift;
  $stringtoesc =~ s/'/''/g;
  $stringtoesc =~ s/\\/\\\\/g;

        return $stringtoesc;
}
