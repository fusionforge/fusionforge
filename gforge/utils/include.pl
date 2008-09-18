# $Id$
#
# include.pl - Include file for all the perl scripts that contains reusable functions
#

##############################
# Global Variables
##############################
$db_include	=	"/etc/gforge/local.pl";	# Local Include file for database username and password

$dummy_uid      =       getpwnam('scm-gforge');                  # UserID of the dummy user that will own group's files
$date           =       int(time()/3600/24);    # Get the number of days since 1/1/1970 for /etc/shadow

require $db_include;
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
sub db_connect ( ) {
    &parse_local_inc;

    $dbh ||= DBI->connect("DBI:Pg:dbname=$sys_dbname;host=$sys_dbhost","$sys_dbuser","$sys_dbpasswd") ;
    if (! $dbh) {
	die "Error while connecting to database: $!" ;
    }
}

sub db_disconnect ( ) {
      $dbh->disconnect ;
      $dbh = "" ;
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
	$res->execute () ;
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

        use File::Temp qw(tempfile);
        use File::Basename qw(dirname);

        my ($fd, $filename) = tempfile( DIR => dirname($file_name), UNLINK => 0) ;
	return 1 unless ($fd && $filename) ;

        foreach (@file_array) { 
                if ($_ ne '') { 
                        print $fd $_;
                }       
        }       

        close($fd);
        unless (rename ($filename, $file_name)) {
                unlink $filename;
                return 1;
        }
        return 0;
}      
