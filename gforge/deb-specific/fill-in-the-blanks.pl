#! /usr/bin/perl -w

use strict ;

use vars qw/$ifile $ofile $prefix $tmpfile %hash $key $val $cur $code $line $token/ ;

# use Debconf::Client::ConfModule ':all';

if ($#ARGV < 1) {
    print STDERR "Missing parameter.\n\n" ;
    print STDERR "Usage: fill-in-the-blanks.pl <in-template> <out-file>\n" ;
    exit 1 ;
}

$ifile = shift @ARGV ;
$ofile = shift @ARGV ;
%hash = () ;

open CONF, "/etc/sourceforge/sourceforge.conf" ;
while ($line = <CONF>) {
    chomp $line ;
    next if $line =~ m/^\s*#/ ;
    ($key, $val) = split ('=', $line, 2) ;
    $hash{$key} = $val ;
}
close CONF ;

umask 0077 ;
open (IFILE, $ifile)
    or die "Can't open input file '$ifile'" ;
if (-e $ofile) {
    unlink $ofile
	or die "Can't unlink output file '$ofile'" ;
}
open (OFILE, "> $ofile")
    or die "Can't open output file '$ofile'" ;

while ($line = <IFILE>) {
    chomp $line ;
    foreach $cur (keys %hash) {
	$token = "{$cur}" ;
	$line =~ s/$token/$hash{$cur}/g ;
    }
    print OFILE "$line\n";
}
close IFILE ;
close OFILE ;
