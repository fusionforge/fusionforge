#!/usr/bin/perl
#/**
#  *
#  * SourceForge: Breaking Down the Barriers to Open Source Development
#  * Copyright 1999-2001 (c) VA Linux Systems
#  * http://sourceforge.net
#  *
#  * @version   $Id$
#  *
#  */

use DBI;
use Time::Local;
use POSIX qw( strftime );
require("/usr/lib/sourceforge/lib/include.pl");  # Include all the predefined functions

$|++;

#######################
##  CONF VARS

	my $verbose = 1;
	my $chronolog_basedir = "/home/ftplogs";

##
#######################

my ( $filerel, $query, $rel, %groups, %filerelease, $bytes, $filepath, $group_name, $filename, $files );

&db_connect;

if ( $ARGV[0] && $ARGV[1] && $ARGV[2] ) {
	   ## Set params manually, so we can run
	   ## regressive log parses.
	$year = $ARGV[0];
	$month = $ARGV[1];
	$day = $ARGV[2];
} else {
	   ## Otherwise, we just parse the logs for yesterday.
#	($day, $month, $year) = (gmtime(timegm( 0, 0, 0, (gmtime( time() - 86400 ))[3,4,5] )))[3,4,5];
	($day, $month, $year) = (localtime(timelocal( 0, 0, 0, (localtime( time() - 86400 ))[3,4,5] )))[3,4,5];
	$year += 1900;
	$month += 1;
}

$file = "$chronolog_basedir/$year/" . sprintf("%02d",$month) . "/ftp_xferlog_$year" 
	. sprintf("%02d",$month) . sprintf("%02d",$day) . ".log";

print "Running year $year, month $month, day $day from \'$file\'\n" if $verbose;
print "Caching file release information out of the database..." if $verbose;

   ## It's makes things a whole lot faster for us if we cache the filerelease/group infor beforehand.
$query  = "SELECT frs_file.file_id,groups.group_id,groups.unix_group_name,frs_file.filename "
        . "FROM frs_file,frs_release,frs_package,groups "
        . "WHERE ( groups.group_id = frs_package.group_id "
        . "AND frs_package.package_id = frs_release.package_id "
        . "AND frs_release.release_id = frs_file.release_id )";
$rel = $dbh->prepare($query);
$rel->execute();
while( $filerel = $rel->fetchrow_arrayref() ) {
        $file_ident = ${$filerel}[2] . ":" . ${$filerel}[3];
        $filerelease{$file_ident} = ${$filerel}[0];
	$groups{${$filerel}[0]} = ${$filerel}[1];
}

print " done.\n" if $verbose;

if ( -f $file ) {
	open(LOGFILE, "< $file" ) || die "Cannot open $file";
} elsif( -f "$file.gz" ) {
	$file .= ".gz";
	open(LOGFILE, "/bin/gunzip -c $file |" ) || die "Cannot open gunzip pipe for $file";
}

print "Begining processing for logfile \'$file\'..." if $verbose;			
while (<LOGFILE>) {

	## This commented out line, and the one below for $filepath, are for dates prior to 20000717
	## if ( $_ =~ m/\/u7\/ftp\/pub\/sourceforge/ ) {

	## NOTE: this line reflects ftp file paths from 20000717 to some unknown date in 2001.
        ## jbyers 2001/04/17
	## if ( $_ =~ m/\/home\/ftp\/mounts\/u3\/sourceforge/ ) {
        
	if ( $_ =~ m/\/home\/ftp\/pub\/sourceforge/ ) {

		$_ =~ m/^(\w+) (\w+)\s+(\d+) (\d\d):(\d\d):(\d\d) (\d\d\d\d) (\d+) ([^\s]+) (\d+) ([^\s]+) /;
		$bytes = $10;
		$filepath = $11;

		## $filepath =~ m/^(\/u7\/ftp\/pub\/sourceforge\/)([^\/]+)\//;
		## $filepath =~ m/^(\/home\/ftp\/mounts\/u3\/sourceforge\/)([^\/]+)\//;
                $filepath =~ m/^(\/home\/ftp\/pub\/sourceforge\/)([^\/]+)\//;
		$group_name = $2;

		$filepath =~ m/\/([^\/]+)$/;
		$filename = $1;

		$file_ident = $group_name . ":" . $filename;

		if ( $filerelease{$file_ident} ) {
			$downloads{$filerelease{$file_ident}}++;
		} 
	} 
}
close(LOGFILE);

print " done.\n" if $verbose;

print "Deleting any existing records for day=" . sprintf("%d%02d%02d", $year, $month, $day) . ".\n" if $verbose;

$dbh->{AutoCommit} = 0;

$query = "DELETE FROM stats_ftp_downloads WHERE day='" . sprintf("%d%02d%02d", $year, $month, $day) . "'";
$dbh->do( $query );


print "Inserting records into database: stats_ftp_downloads..." if $verbose;

foreach $id ( keys %downloads ) {
	$query  = "INSERT INTO stats_ftp_downloads (day,filerelease_id,group_id,downloads) ";
	$query .= "VALUES (\'" . sprintf("%d%02d%02d", $year, $month, $day) . "\',\'";
	$query .= $id . "\',\'" . $groups{$id} . "\',\'" . $downloads{$id} . "\')";
	$dbh->do( $query );
}

$dbh->commit;

print " done.\n" if $verbose;

##
## EOF
##
