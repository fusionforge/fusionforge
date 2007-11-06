#! /usr/bin/perl -w
#
# DSF-Helper, a Debhelper-inspired helper for Debian Sourceforge
#
# Roland Mas <lolando@debian.org>
# This code is copyright Roland Mas 2002
# You are welcome to use, modify and redistribute this file under the terms of
# the GNU General Public License version 2.

use strict ;
use vars qw! @known_files @file_list @package_list %chunks ! ;
use subs qw! &pkgfile &get_chunk ! ;
use diagnostics ;

###
# STATIC DATA

@known_files = qw/ config templates preinst postinst prerm postrm / ;

###
# HELPER FUNCTIONS

sub pkgfile {
    my $package=shift;
    my $filename=shift;

    if (-f "debian/$package.$filename.dsfh-in") {
	return "debian/$package.$filename.dsfh-in";
    }
    else {
	return "";
    }
} ;

sub get_chunk {
    my $varname = shift ;
    my $function = shift ;

    unless (defined $chunks{$varname}) {
	# First time this variable is encountered, creating slot
	# print "Creating data structure for var $varname.\n" ;
	$chunks{$varname} = {} ;
    } ;
    
    unless (defined $chunks{$varname}{$function}) {
	# First time this instance is accessed, loading data
	# print "Loading data for var $varname and function $function.\n" ;
	$chunks{$varname}{$function} = '' ;
	my $fname = "debian/dsf-helper/$varname.$function" ; 
	unless (-f $fname) {
	    print "File $fname file not found, aborting.\n" ;
	    exit 1 ;
	}
	
	open F, $fname ;
	while (<F>) {
	    $chunks{$varname}{$function} .= $_ ;
	}
	close F ;
    }

    return $chunks{$varname}{$function} ;
}

###
# DO THE JOB
@package_list = split /\n/, qx! dh_listpackages ! ;

if ($ARGV[0] && $ARGV[0] eq "--clean") {
 EXTLOOP: for my $ext (@known_files) {
   PKGLOOP: for my $pkg (@package_list) {
       my $srcfile = &pkgfile ($pkg, $ext) ;
       next PKGLOOP unless $srcfile ;
       my $destfile = $srcfile ;
       $destfile =~ s/\.dsfh-in$// ;
       do {
	   # print "Removing $destfile\n" ;
	   unlink $destfile ;
       } if -f $destfile ;
   }
 }
   exit 0 ;
}

 EXTLOOP: for my $ext (@known_files) {
     # print "Extension: $ext\n" ;
   PKGLOOP: for my $pkg (@package_list) {
       # print "  Package: $pkg\n" ;
       my $srcfile = &pkgfile ($pkg, $ext) ;
       next PKGLOOP unless $srcfile ;
       my $destfile = $srcfile ;
       $destfile =~ s/\.dsfh-in$// ;
       print "$srcfile -> $destfile\n" ;

       open S, "< $srcfile" ;
       my $dest = "" ;
       
       while (my $l = <S>) {
	   chomp $l ;
	   while ($l =~ m!\#DSFHELPER:([-_a-zA-Z0-9/]+)\#!) {
	       my $chunkname = $1 ;
	       my $chunk = get_chunk ($chunkname, $ext) ;
	       $l =~ s!\#DSFHELPER:$chunkname\#!$chunk! ;
	   }
	   $dest .= "$l\n"
       }

       if ($ext eq "templates") {
	   # Need to remove a few extra blank lines
	   $dest =~ s/^#.*\n//gm ;
	   $dest =~ s/^\n*//g ;
	   $dest =~ s/\n\n+/\n\n/g ;
       }

       open D, "> $destfile" ;
       print D "$dest" ;
       close D ;
       close S ;
   }
 }
