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
require("../../../utils/include.pl");  # Include all the predefined functions

$|++;

#######################
##  CONF VARS

	my $verbose = 1;

##
#######################

#
#
#   This script simply takes the numbers from stats_ftp_downloads
#   and stats_http_downloads and combines them into frs_dlstats_file_agg
#
#   This is done by the day because postgres does not appear to support 
#   full outer joins yet, at least not in this case.
#   
#

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

print "Running year $year, month $month, day $day frs download aggregation\n" if $verbose;

$dbh->{AutoCommit} = 0;

print "Deleting any existing records... " if $verbose;
$query = "DELETE FROM frs_dlstats_file_agg WHERE month='" . sprintf("%d%02d", $year, $month) . "' AND day='" . sprintf("%02d", $day) . "'";
$dbh->do( $query );
print "done.\n" if $verbose;

$sql="INSERT INTO frs_dlstats_file_agg
SELECT * FROM (
    SELECT
	'" . sprintf("%d%02d", $year, $month) . "'::int AS month,
        '" . sprintf("%02d",$day) . "'::int AS day,
        frs_file.file_id,
	(coalesce(sf.downloads,0) + coalesce(sh.downloads,0)) AS downloads
    FROM frs_file 
	LEFT JOIN stats_http_downloads sh ON (sh.day='" . sprintf("%d%02d%02d", $year, $month, $day) . "' 
	    AND frs_file.file_id=sh.filerelease_id)
	LEFT JOIN stats_ftp_downloads sf ON (sf.day='" . sprintf("%d%02d%02d", $year, $month, $day) . "' 
	    AND frs_file.file_id=sf.filerelease_id)
) mess 
WHERE downloads > 0;";


print "Beginning query...\n" if $verbose;
## print $sql if $verbose;

$dbh->do($sql);
$dbh->commit;

print "Done\n" if $verbose;

##
## EOF
##
