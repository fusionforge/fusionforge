#!/usr/bin/perl -w
#
# Move the released files around to accomodate the new layout used by the FRS
# Roland Mas <lolando@debian.org>

use DBI ;
use strict ;
use diagnostics ;
use File::Temp qw/ :mktemp  /;

use vars qw/ $dbh $sys_lists_host $domain_name / ;

use vars qw// ;

sub debug ( $ ) ;

require ("/usr/lib/gforge/lib/include.pl") ; # Include all the predefined functions 
require ("/etc/gforge/local.pl") ;

my $download_path = "/var/lib/gforge/download" ;

&db_connect ;

$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;

my ($query, $sth, @array, @lines, $line) ;

$query = "SELECT groups.unix_group_name as gname, frs_package.name as pname, frs_release.name as rname, frs_file.filename as fname
          FROM frs_package,frs_release,frs_file,groups
          WHERE frs_release.release_id=frs_file.release_id
          AND groups.group_id=frs_package.group_id
          AND frs_release.package_id=frs_package.package_id";
$sth = $dbh->prepare ($query) ;
$sth->execute () ;
while (my @myarray = $sth->fetchrow_array ()) {
    my $gname = shift @myarray;
    my $pname = shift @myarray;
    my $rname = shift @myarray;
    my $fname = shift @myarray;

    $pname =~ s/[^-A-Za-z0-9_\.]//g;
    $rname =~ s/[^-A-Za-z0-9_\.]//g;
    $fname =~ s/[^-A-Za-z0-9~_\.]//g;

    my $oldname = "$download_path/$gname/$fname" ;
    my $newname = "$download_path/$gname/$pname/$rname/$fname" ;

    my $newdir = $newname;
    $newdir =~ s,/[^/]+$,,;
    if (! -d $newdir) {
	system "mkdir -p $newdir";
    }

    my $olddir = $oldname;
    $olddir =~ s,/[^/]+$,,;
    system "chown -R www-data:www-data $olddir";

    if (-e $oldname && ! -e $newname) {
	rename $oldname, $newname or die $!;
    }
}
$sth->finish () ;
