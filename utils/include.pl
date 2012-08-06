# include.pl - Include file for all the perl scripts that contains reusable functions
#

##############################
# Global Variables
##############################
$dummy_uid      =       getpwnam('scm-gforge');                  # UserID of the dummy user that will own group's files
$date           =       int(time()/3600/24);    # Get the number of days since 1/1/1970 for /etc/shadow

@possible_paths = (
    '/usr/share/gforge/bin',
    '/usr/share/fusionforge/bin',
    '/usr/local/share/gforge/bin',
    '/usr/local/share/fusionforge/bin',
    '/opt/gforge/bin',
    '/opt/fusionforge/bin',
    '/usr/bin',
    '/usr/local/bin') ;
foreach $p (@possible_paths) {
    if (-x "$p/forge_get_config") {
	$fgc = "$p/forge_get_config";
	last;
    }
}

%forge_config_cache = ();

sub forge_get_config ($$) {
    my $var = shift;
    my $sec = shift || 'core';

    if (!defined $forge_config_cache{$sec}{$var}) {
	$forge_config_cache{$sec}{$var} = qx!$fgc $var $sec!;
	chomp $forge_config_cache{$sec}{$var};
    }
    return $forge_config_cache{$sec}{$var};
}

$sys_default_domain = &forge_get_config ('web_host') ;
$sys_scm_host = &forge_get_config ('web_host') ;
$domain_name = &forge_get_config ('web_host') ;
$sys_users_host = &forge_get_config ('users_host') ;
$sys_lists_host = &forge_get_config ('lists_host') ;
$sys_name = &forge_get_config ('forge_name') ;
$sys_themeroot = &forge_get_config ('themes_root') ;
$sys_news_group = &forge_get_config ('news_group') ;
$sys_dbhost = &forge_get_config ('database_host') ;
$sys_dbport = &forge_get_config ('database_port') ;
$sys_dbname = &forge_get_config ('database_name') ;
$sys_dbuser = &forge_get_config ('database_user') ;
$sys_dbpasswd = &forge_get_config ('database_password') ;
$sys_ldap_base_dn = &forge_get_config ('ldap_base_dn') ;
$sys_ldap_host = &forge_get_config ('ldap_host') ;
$server_admin = &forge_get_config ('admin_email') ;
$chroot_prefix = &forge_get_config ('chroot') ;
$homedir_prefix = &forge_get_config ('homedir_prefix') ;
$grpdir_prefix = &forge_get_config ('groupdir_prefix') ;
$file_dir = &forge_get_config ('data_path') ;

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

#######################
# Display a backtrace #
#######################
sub
debug_print_backtrace
{
	my $i = 1;

	print "Call Trace:\n";
	while ((my @call_details = (caller($i++)))) {
		print " + " . $call_details[1] . ":" . $call_details[2] .
		    " in function " . $call_details[3] . "\n";
	}
}
