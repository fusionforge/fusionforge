#! /usr/bin/perl -T

use strict ;
use vars qw/ $file $user $group $dirty_file $dirty_user $dirty_group
    $src_file $dest_dir $retval / ;
use subs qw/ &fileforge &tmpfilemove &wash_string / ;
no locale ;

# Clean up our environment
delete @ENV{qw(IFS CDPATH ENV BASH_ENV PATH)};

# Check access to secret
require ("/etc/sourceforge/local.pl") ;
unless ($sys_dbpasswd == $ENV{'sys_dbpassword'}) {
    die "You are not authorized to run this script" ;
}

# Check which mode we're in
# Normal fileforge
if ($0 == "/usr/lib/sourceforge/bin/fileforge.pl") {
    &fileforge ;
    exit 0 ;
}
# Temporary moving of files (for quick release system)
if ($0 == "/usr/lib/sourceforge/bin/tmpfilemove.pl") {
    &tmpfilemove ;
    exit 0 ;
}
# If we're not in one of these two modes, then fail
print "You must call this script as one of:
* /usr/lib/sourceforge/bin/fileforge.pl (normal execution)
* /usr/lib/sourceforge/bin/tmpfilemove.pl (for QRS)" ;
die "Unauthorized invocation '$0'" ;

sub &fileforge {
    if ($#ARGV != 2) {
	die "Usage: fileforge.pl file user group" ;
    }

    # "Parse" command-line options
    $dirty_file = $ARGV [0] ;
    $dirty_user = $ARGV [1] ;
    $dirty_group = $ARGV [2] ;

    # Check and untaint $user and $file here
    $file = &wash_string ($dirty_file, "file") ;
    $user = &wash_string ($dirty_user, "user") ;

    # Compute source file name
    $src_file = "/var/lib/sourceforge/chroot/home/users/" ;
    $src_file .= $user ;
    $src_file .= "/incoming/" ;
    $src_file .= $file ;

    # Check and untaint $group here
    $group = &wash_string ($dirty_group, "group") ;

    # Compute destination file name
    $dest_dir = "/var/lib/sourceforge/download/" ;
    $dest_dir .= $group ;
    $dest_dir .= "/" ;

    unless ( -d $dest_dir ) {
	die "Destination directory '$dest_dir' does not exist" ;
    }

    # print "Moving '$src_file' to '$dest_dir'.\n" ;

    $retval = system "/bin/echo /bin/mv $src_file $dest_dir" ;
    if ($retval == -1) {
	die "Could not execute /bin/mv: $!" ;
    }
    if ($retval != 0) {
	die "Error moving file" ;
    }
}

sub &tmpfilemove {
    if ($#ARGV != 2) {
	die "Usage: tmpfilemove.pl temp_filename real_filename user_unix_name" ;
    }

    die "Not implemented yet" ;
}

sub wash_string {
    my $string = shift ;
    my $name = shift ;

    # Empty strings are not allowed
    if (length $string == 0) {
	die "Forbidden empty $name '$string'" ;
    }
    
    # Only allowed characters are alphanumerical . + _ -
    if ($string =~ m,[^\w.+_-],) {
	die "Forbidden characters in $name '$string'" ;
    }

    # No .. sequence is allowed
    if ($string =~ m,\.\.,) {
	die "Forbidden '..' sequence in $name 'string'" ;
    }
    
    my $clean = '' ;
 
    if ($string =~ /^([\w.+_-]+)$/) {
	$clean = $1 ;
    } else {
	die "Unexpected error while untainting $name '$string'" ;
    }

    return $clean ;
}
