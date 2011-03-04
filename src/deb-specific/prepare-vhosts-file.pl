#! /usr/bin/perl -w

# TODO : document what this script is doing

use DBI;
use File::Temp ;
use strict ;
use vars qw/$dbh $ifile $ofile @ilist %hash $key $val $cur $line $dbh $sys_dbname $sys_dbuser $sys_dbpasswd $token/ ;

require ("/usr/share/gforge/lib/include.pl") ; 

%hash = () ;

&db_connect ;

$ifile = '/usr/share/gforge/etc/templates/httpd.vhosts' ;
$ofile = '/var/lib/gforge/etc/templates/httpd.vhosts' ;

open (IFILE, $ifile)
    or die "Can't open input file '$ifile'" ;
@ilist = <IFILE> ;
close IFILE ;

open (OFILE, "> $ofile")
    or die "Can't open output file '$ofile'" ;

my $query = "select vh.vhost_name, vh.docdir, vh.cgidir from prweb_vhost vh, groups g where g.status = 'A' and vh.group_id = g.group_id order by vh.vhost_name";
my $c = $dbh->prepare($query);
$c->execute();

while(my ($vhost_name, $docdir, $cgidir) = $c->fetchrow()) {

    $hash{vhost_name} = $vhost_name ;
    $hash{docdir} = $docdir ;
    $hash{cgidir} = $cgidir ;

    foreach my $tmpl_line (@ilist) {
	my $line = $tmpl_line ;
	chomp $line ;
	foreach $cur (keys %hash) {
	    $token = "{$cur}" ;
	    $line =~ s/$token/$hash{$cur}/g ;
	}
	print OFILE "$line\n";
    }

    print OFILE "\n" ;
}

close OFILE ;
