# $Id$
#
# include.pl - Include file for all the perl scripts that contains reusable functions
#

##############################
# Global Variables
##############################
$db_include	=	"/etc/sourceforge/local.pl";	# Local Include file for database username and password
$tar_dir	=	"/tmp";			# Place to put deleted user's accounts
$uid_add	=	"20000";		# How much to add to the database uid to get the unix uid
$gid_add	=	"10000";			# How much to add to the database gid to get the unix uid
$homedir_prefix =	"/var/lib/sourceforge/chroot/home/users/";		# What prefix to add to the user's homedir
$grpdir_prefix  =	"/var/lib/sourceforge/chroot/home/groups/";	# What prefix to add to the user's homedir
$file_dir	=	"/var/lib/sourceforge/";	# Where should we stick files we're working with
$cvs_root       =       "/var/lib/sourceforge/chroot/cvsroot/";    # Where should we stick files we're working with
$dummy_uid      =       "9999";                  # UserID of the dummy user that will own group's files
$date           =       int(time()/3600/24);    # Get the number of days since 1/1/1970 for /etc/shadow
$ldap_prefix	=	"/usr/local/ldap/bin/";	# Where OpenLDAP tools installed

##################################
# Configuration parsing Functions
##################################
sub parse_local_inc {
    require $db_include;
#  	my ($foo, $bar);
#  	# open up database include file and get the database variables
#  	open(FILE, $db_include) || die "Can't open $db_include: $!\n";
#  	while (<FILE>) {
#  		next if ( /^\s*\/\// );
#  		($foo, $bar) = split /=/;
#  		if ($foo) { eval $_ };
#  	}
#  	close(FILE);
}

##############################
# Database Connect Functions
##############################
sub db_connect {
	&parse_local_inc;
	
	# connect to the database
	$dbh ||= DBI->connect("DBI:Pg:dbname=$sys_dbname;host=$sys_dbhost", "$sys_dbuser", "$sys_dbpasswd");

	die "Cannot connect to database: $!" if ( ! $dbh );
}

sub db_drop_table_if_exists {
    my ($sql, $res, $n, $tn) ;
    $tn = shift ;
    $sql = "SELECT COUNT(*) FROM pg_class WHERE relname='$tn'";
    $res = $dbh->prepare($sql);
    $res->execute();
    ($n) = $res->fetchrow() ;
    $res->finish () ;
    if ($n != 0) {
	$sql = "DROP TABLE $tn";
	$res = $dbh->prepare($sql);
	$res->finish () ;
    }
}

##############################
# File open function, spews the entire file to an array.
##############################
sub open_array_file {
        my $filename = shift(@_);
        
        open (FD, $filename) || die "Can't open $filename: $!.\n";
        @tmp_array = <FD>;
        close(FD);
        
        return @tmp_array;
}       

#############################
# File write function.
#############################
sub write_array_file {
        my ($file_name, @file_array) = @_;
        
        open(FD, ">$file_name") || die "Can't open $file_name: $!.\n";
        foreach (@file_array) { 
                if ($_ ne '') { 
                        print FD;
                }       
        }       
        close(FD);
}      
