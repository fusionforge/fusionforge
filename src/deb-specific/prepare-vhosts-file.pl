#! /usr/bin/perl -w

# TODO : document what this script is doing

use DBI;
use File::Temp ;
use strict ;
use vars qw/$dbh $ifile $ofile @ilist %hash $key $val $cur $line $dbh $sys_dbname $sys_dbuser $sys_dbpasswd $token/ ;

my $source_path = `forge_get_config source_path`;
chomp $source_path;
my $config_path = `forge_get_config config_path`;
chomp $config_path;
my $data_path = `forge_get_config data_path`;
chomp $data_path;

require ("$source_path/lib/include.pl") ; 

%hash = () ;

&db_connect ;


$ifile = "$config_path/templates/httpd.vhosts" ;
$ofile = "$data_path/etc/templates/httpd.vhosts" ;

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

    foreach my $k (qw/groupdir_prefix log_path/) {
	$hash{$k} = &forge_get_config ($k) ;
    }

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
