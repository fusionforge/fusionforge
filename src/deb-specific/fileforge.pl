#! /usr/bin/perl -Tw

use strict ;
use vars qw/ $file $dirty_file $user $dirty_user $group $dirty_group
    $real_file $dirty_real_file $src_file $dest_dir $dest_file $retval
    $homedir_prefix $sys_dbpasswd / ;
use subs qw/ &fileforge &tmpfilemove &wash_string / ;
no locale ;

# Clean up our environment
delete @ENV{qw(IFS CDPATH ENV BASH_ENV PATH)};

# Check access to secret
require("/usr/share/gforge/lib/include.pl");
unless ( (defined $sys_dbpasswd)
	 and (defined $ENV{'sys_dbpasswd'})
	 and ($sys_dbpasswd eq $ENV{'sys_dbpasswd'}) ) {
    die "You are not authorized to run this script" ;
}

# Initialize a constant
$homedir_prefix = "/var/lib/gforge/chroot/home/users/" ;

# Check which mode we're in
# Normal fileforge
if ($0 eq "/usr/share/gforge/bin/fileforge.pl") {
    &fileforge ;
    exit 0 ;
}
# Temporary moving of files (for quick release system)
if ($0 eq "/usr/share/gforge/bin/tmpfilemove.pl") {
    &tmpfilemove ;
    exit 0 ;
}
# If we're not in one of these two modes, then fail
print STDERR "You must call this script as one of:
* /usr/share/gforge/bin/fileforge.pl (normal execution)
* /usr/share/gforge/bin/tmpfilemove.pl (for QRS)" ;
die "Unauthorized invocation '$0'" ;

sub fileforge {
    if ($#ARGV != 2) {
	die "Usage: fileforge.pl file user group" ;
    }

    # "Parse" command-line options
    $dirty_file = $ARGV [0] ;
    $dirty_user = $ARGV [1] ;
    $dirty_group = $ARGV [2] ;

    # Check and untaint $user and $file here
    $file = &wash_string ($dirty_file, "file", 1) ;
    $user = &wash_string ($dirty_user, "user", 0) ;

    # Compute source file name
    $src_file = $homedir_prefix ;
    $src_file .= $user ;
    $src_file .= "/incoming/" ;
    $src_file .= $file ;

    # Check and untaint $group here
    $group = &wash_string ($dirty_group, "group", 0) ;

    # Compute and test destination dir name
    $dest_dir = "/var/lib/gforge/download/" ;
    $dest_dir .= $group ;
    $dest_dir .= "/" ;
    unless ( -d $dest_dir ) {
	mkdir $dest_dir, 0755 or die $! ;
	chown 0, 0, $dest_dir or die $! ;
    }
    unless ( -d $dest_dir ) {
	die "Destination directory '$dest_dir' does not exist" ;
    }

    chmod 0400, $src_file ;
    chown 0, 0, $src_file ;
    chmod 0644, $src_file ;
    $retval = system "/bin/mv $src_file $dest_dir" ;
    if ($retval == -1) {
	die "Could not execute /bin/mv: $!" ;
    }
    if ($retval != 0) {
	die "Error moving file" ;
    }
}

sub tmpfilemove {
    if ($#ARGV != 2) {
	die "Usage: tmpfilemove.pl temp_filename real_filename user_unix_name" ;
    }
    $dirty_file = $ARGV [0] ;
    $dirty_real_file = $ARGV [1] ;
    $dirty_user = $ARGV [2] ;

    # Check and untaint variables here
    $file = &wash_string ($dirty_file, "file", 1) ;
    $real_file = &wash_string ($dirty_real_file, "real_file", 1) ;
    $user = &wash_string ($dirty_user, "user", 0) ;

    # Compute source file name
    $src_file = "/tmp/" ;
    $src_file .= $file ;

    # Insure the source file is good
    chmod 0400, $src_file ;
    $retval = system "/bin/chown $user:$user $src_file" ;
    if ($retval == -1) {
	die "Could not execute '/bin/chmod $user:$user $src_file': $!" ;
    }
    if ($retval != 0) {
	die "Error reattributing file" ;
    }
    chmod 0644, $src_file ;

    # Compute and test destination directory name
    $dest_dir = $homedir_prefix ;
    $dest_dir .= $user ;
    $dest_dir .= "/incoming/" ;
    unless ( -d $dest_dir ) {
	die "Destination directory '$dest_dir' does not exist" ;
    }
    
    # Compute destination file name
    $dest_file = $dest_dir . $real_file ;

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
    my $allowtilde = shift ;

    # Empty strings are not allowed
    if (length $string == 0) {
	die "Forbidden empty $name '$string'" ;
    }
    
    if ($allowtilde) {
	# Only allowed characters are alphanumerical . + _ - ~
	if ($string =~ m,[^\w.+_~-],) {
		die "Forbidden characters in $name '$string'" ;
	}
    } else {
	# Only allowed characters are alphanumerical . + _ -
	if ($string =~ m,[^\w.+_-],) {
		die "Forbidden characters in $name '$string'" ;
	}
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
