#!/usr/bin/perl
#
# $Id: db_top_groups_calc.pl,v 1.9 2000/12/06 19:04:51 tperdue Exp $
#
# use strict;
use DBI;
use Time::Local;
use POSIX qw( strftime );

require("../include.pl");
&db_connect();

#my ($sql, $rel);
my ($day_begin, $day_end, $mday, $year, $mon, $week, $day);
my $verbose = 1;

##
## Set begin and end times (in epoch seconds) of day to be run
## Either specified on the command line, or auto-calculated
## to run yesterday's data.
##
if ( $ARGV[0] && $ARGV[1] && $ARGV[2] ) {

        $day_begin = timegm( 0, 0, 0, $ARGV[2], $ARGV[1] - 1, $ARGV[0] - 1900 );
        $day_end = timegm( 0, 0, 0, (gmtime( $day_begin + 86400 ))[3,4,5] );

} else {

           ## Start at midnight last night.
        $day_end = timegm( 0, 0, 0, (gmtime( time() ))[3,4,5] );
           ## go until midnight yesterday.
        $day_begin = timegm( 0, 0, 0, (gmtime( time() - 86400 ))[3,4,5] );

        print "$day_begin $day_end \n";
}

   ## Preformat the important date strings.
$year   = strftime("%Y", gmtime( $day_begin ) );
$mon    = strftime("%m", gmtime( $day_begin ) );
$week   = strftime("%U", gmtime( $day_begin ) );    ## GNU ext.
$day    = strftime("%d", gmtime( $day_begin ) );
print "Running week $week, day $day month $mon year $year \n" if $verbose;


# get all groups, and group_names
$query = "SELECT group_id,group_name FROM groups WHERE type=1 AND status='A' AND is_public='1'";
$rel = $dbh->prepare($query);
$rel->execute();

while( ($group_id,$group_name) = $rel->fetchrow() ) {
	$top[$group_id][0] = $group_name;
	if ( $group_id > $max_group_id ) {
		$max_group_id = $group_id;
	}
}

# get forumposts_week stats
$query = "SELECT forum_group_list.group_id AS group_id,
    count(*) AS count 
    FROM forum,forum_group_list 
    WHERE forum.group_forum_id=forum_group_list.group_forum_id 
    GROUP BY forum_group_list.group_id 
    ORDER BY count DESC";
my $rel = $dbh->prepare($query);
$rel->execute();

$currentrank = 1;
while(my ($group_id,$count) = $rel->fetchrow()) {
	$top[$group_id][12] = $count;
	$top[$group_id][13] = $currentrank;
	$currentrank++;
}

##
##	wrap this process inside a transaction
##
my $rel = $dbh->do("BEGIN WORK;");

my $query = "DELETE FROM top_group";
my $rel = $dbh->do($query);

# store new data
for ($i=1;$i<$max_group_id;$i++) {
	my $query = "INSERT INTO top_group (group_id,group_name,downloads_all,"
		."rank_downloads_all,rank_downloads_all_old,downloads_week,"
		."rank_downloads_week,rank_downloads_week_old,userrank,rank_userrank,"
		."rank_userrank_old,forumposts_week,rank_forumposts_week,"
		."rank_forumposts_week_old,pageviews_proj,rank_pageviews_proj,"
		."rank_pageviews_proj_old) VALUES "
		."('$i',".$dbh->quote($top[$i][0]).",'$top[$i][5]','$top[$i][6]','$top[$i][1]',"
		."'$top[$i][7]','$top[$i][8]','$top[$i][2]',"
		."'0','0','$top[$i][3]','$top[$i][12]','$top[$i][13]','$top[$i][4]',"
		."'$top[$i][10]','$top[$i][11]','$top[$i][9]')";
	my $rel = $dbh->prepare($query);
	$rel->execute();

#	print "Group ID $i: $top[$i][0], $top[$i][1], $top[$i][2], $top[$i][3], $top[$i][4], "
#		."$top[$i][5], $top[$i][6]\n";
}

##
##      wrap this process inside a transaction
##
my $rel = $dbh->do("COMMIT WORK;");

