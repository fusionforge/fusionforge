#! /usr/bin/perl -T

use strict ;
use vars qw/ $file $dirty_file $user $dirty_user $group $dirty_group
    $real_file $dirty_real_file $src_file $dest_dir $dest_file $retval
    $homedir_prefix / ;
use subs qw/ &fileforge &tmpfilemove &wash_string / ;
no locale ;

# Clean up our environment
delete @ENV{qw(IFS CDPATH ENV BASH_ENV PATH)};

# Check access to secret
require ("/etc/sourceforge/local.pl") ;
unless ($sys_dbpasswd == $ENV{'sys_dbpassword'}) {
    die "You are not authorized to run this script" ;
}

# Initialize a constant
$homedir_prefix = "/var/lib/sourceforge/chroot/home/users/" ;

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
    $src_file = $homedir_prefix ;
    $src_file .= $user ;
    $src_file .= "/incoming/" ;
    $src_file .= $file ;

    # Check and untaint $group here
    $group = &wash_string ($dirty_group, "group") ;

    # Compute destination dir name
    $dest_dir = "/var/lib/sourceforge/download/" ;
    $dest_dir .= $group ;
    $dest_dir .= "/" ;

    unless ( -d $dest_dir ) {
	die "Destination directory '$dest_dir' does not exist" ;
    }

    # print "Moving '$src_file' to '$dest_dir'.\n" ;

    $retval = system "/bin/mv $src_file $dest_dir" ;
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
    $dirty_file = $ARGV [0] ;
    $dirty_real_file = $ARGV [1] ;
    $dirty_user = $ARGV [2] ;

    # Check and untaint $file and $real_file here
    $file = &wash_string ($dirty_file, "file") ;
    $real_file = &wash_string ($dirty_real_file, "real_file") ;

    # Compute source file name
    $src_file = "/tmp/" ;
    $src_file .= $file ;

    # Check and untaint $user here
    $user = &wash_string ($dirty_user, "user") ;

    # Compute destination file name
    $dest_file = $homedir_prefix ;
    $dest_file .= $user ;
    $dest_file .= "/incoming/" ;
    $dest_file .= $real_file ;

    # print "Moving '$src_file' to '$dest_file'.\n" ;

    $retval = system "/bin/mv $src_file $dest_file" ;
    if ($retval == -1) {
	die "Could not execute /bin/mv: $!" ;
    }
    if ($retval != 0) {
	die "Error moving file" ;
    }
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
