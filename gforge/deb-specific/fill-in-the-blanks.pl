#! /usr/bin/perl -w

use strict ;

use vars qw/$ifile $ofile $prefix $tmpfile %hash $cur $code $line $token/ ;

# use Debconf::Client::ConfModule ':all';

if ($#ARGV < 2) {
    print STDERR "Missing parameter.\n\n" ;
    print STDERR "Usage: fill-in-the-blanks.pl <in-template> <out-file> <prefix> [ <variable> ... ]\n" ;
    exit 1 ;
}

$ifile = shift @ARGV ;
$ofile = shift @ARGV ;
$prefix = shift @ARGV ;
%hash = () ;

foreach $cur (@ARGV) {
    ($code, $hash{$cur}) = get ($prefix.$cur) ;
    if ($code) {
	exit 1 ;
    }
}

umask 077 ;
open (IFILE, $ifile)
    or die "Can't open input file '$ifile'" ;
if (-e $ofile) {
    unlink $ofile
	or die "Can't unlink output file '$ofile'" ;
}
open (OFILE, "> $ofile")
    or die "Can't open output file '$ofile'" ;

while ($line = <IFILE>) {
    foreach $cur (keys %hash) {
	$token = "{$cur}" ;
	$line =~ s/$token/$hash{$cur}/ ;
    }
    print OFILE $line;
}

close IFILE ;
close OFILE ;
