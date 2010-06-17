#! /usr/bin/perl -w
#
# "Shell" for a restricted account, limiting the available commands
# Roland Mas, debian-sf (Sourceforge for Debian)
#
# Inspired from the grap.c file in Sourceforge 2.5

use strict ;
use vars qw/ @allowed_options @allowed_commands $errmsg @cmd / ;
use subs qw/ &reject / ;
no locale ;

@allowed_options = ('-c', '-e') ;
@allowed_commands = ('cvs') ;

# Clean up our environment
delete @ENV{qw(IFS CDPATH ENV BASH_ENV PATH)};

if ($#ARGV != 1) {
    if ($#ARGV < 1) {
	$errmsg = "Not enough arguments." ;
    } else {
	$errmsg = "Too many arguments." ;
    }
    &reject ;
}

if (scalar (grep { $_ eq $ARGV[0] } @allowed_options) == 0) {
    $errmsg = "Option not allowed." ;
    &reject ;
}

@cmd = split (/ +/, $ARGV[1]) ;

if (scalar (grep { $_ eq $cmd[0] } @allowed_commands) == 0) {
    $errmsg = "Command not allowed." ;
    &reject ;
}

exec @cmd ;

sub reject {
    print "This is a restricted account.\n" . 
	"You cannot execute anything here.\n" . 
	# $errmsg . "\n" .
	"Goodbye.\n" ;
    exit 1 ;
}
