# include.pl - Include file for all the perl scripts that contains reusable functions
#

##############################
# Global Variables
##############################

$dummy_uid      =       getpwnam('scm-gforge');                  # UserID of the dummy user that will own group's files
$date           =       int(time()/3600/24);    # Get the number of days since 1/1/1970 for /etc/shadow

chomp($sys_dbuser=`/usr/share/gforge/bin/forge_get_config database_user`);
chomp($sys_dbpasswd=`/usr/share/gforge/bin/forge_get_config database_password`);
chomp($sys_dbhost=`/usr/share/gforge/bin/forge_get_config database_host`);
chomp($sys_dbname=`/usr/share/gforge/bin/forge_get_config database_name`);
chomp($sys_dbport=`/usr/share/gforge/bin/forge_get_config database_port`);
chomp($file_dir=`/usr/share/gforge/bin/forge_get_config data_path`);
chomp($grpdir_prefix=`/usr/share/gforge/bin/forge_get_config groupdir_prefix`);
chomp($homedir_prefix=`/usr/share/gforge/bin/forge_get_config homedir_prefix`);

##############################
# Database Connect Functions
##############################
sub db_connect ( ) {
    my $str = "DBI:Pg:dbname=$sys_dbname" ;
    if ($sys_dbhost ne '') {
	$str .= ";host=$sys_dbhost" ;
    }
    if ($sys_dbport ne '') {
	$str .= ";port=$sys_dbport" ;
    }
    $dbh ||= DBI->connect($str,"$sys_dbuser","$sys_dbpasswd") ;
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
