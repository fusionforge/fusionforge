#! /usr/bin/perl -w

use strict ;
use diagnostics ;

require "/usr/lib/sourceforge/lib/sqlparser.pm" ;

sub sort_sql ( $$ ) {
    my $f = shift ;
    my $g = shift ;
    open G, "> $g" or die $! ;
    my @l = @{ &parse_sql_file ($f) } ;
    foreach my $s (sort @l) {
	print G "$s\n" ;
    }
    close G ;
}

sub uncomment ( $$ ) {
    my $f = shift ;
    my $g = shift ;
    open F, $f or die $! ;
    open G, "> $g" or die $! ;
    while (my $l = <F>) {
	chomp $l ;
	next if $l =~ /^\s*$/ ;
	next if $l =~ /^--/ ;
	next if $l =~ /^\\connect/ ;
	$l = join (" ", split (/\s+/, $l)) ;
	print G "$l\n" ;
    } ;
    close F ;
    close G ;
}

uncomment "dump-2.6", "tmpfile-2.6" ;
sort_sql "tmpfile-2.6", "schema-2.6.sorted" ;
uncomment "dump-2.6-from-2.5", "tmpfile-2.6-from-2.5" ;
sort_sql "tmpfile-2.6-from-2.5", "schema-2.6-from-2.5.sorted" ;
