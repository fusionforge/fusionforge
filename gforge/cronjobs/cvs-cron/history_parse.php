#! /usr/bin/php4 -f
<?php

require_once('squal_pre.php');
/**
 *
 * Recurses through the /cvsroot directory tree and parses each projects
 * '~/CVSROOT/history' file, building agregate stats on the number of
 * checkouts, commits, and adds to each project over the past 24 hours.
 *
 * @version   $Id$
 */

$cvsroot="/cvsroot";
if (!chdir($cvsroot)) {
	print("Unable to make $cvsroot the working directory.\n");
	exit;
}

function rundate($historyfile, $group_id, $mon, $day, $year, $day_begin, $day_end) {
	global $cvsroot;
	$cvs_co=$cvs_commit=$cvs_add=0;
	
	for ($i=0; $i<=count($historyfile)-1; $i++) {
		# Split the cvs history entry into its 6 fields.
		$fields = explode('|',trim($historyfile[$i]));
		$cvstime=$fields[0];	
		$user=$fields[1];	
		$curdir=$fields[2];	
		$module=$fields[3];	
		$rev=$fields[4];	
		$file=$fields[5];	

		$type = substr($cvstime, 0, 1);
		$time_parsed = hexdec( substr($cvstime, 1, 8) );
	
		## See if the entry was made for the specified day
		if ($time_parsed > $day_begin && $time_parsed < $day_end) {
			if ($type == "M") {
				$cvs_commit++;
			} elseif ( $type == "A" ) {
				$cvs_add++;
			} elseif ( $type == "O" ) {
				$cvs_co++;
			}
		}
	}

	$sql = "INSERT INTO stats_cvs_group (month,day,group_id,checkouts,commits,adds) VALUES ('$year$mon','$day','$group_id',$cvs_co,$cvs_commit,$cvs_add)";
	#print $sql."\n";
	$res = db_query($sql);	
	print db_error();
}

function generateTodayStats($historyfile, $group_id) {
	$today=mktime();
	$yesterday=mktime(0,0,0,strftime("%m", $today), strftime("%d", $today)-1, strftime("%Y", $today));
	$day = strftime("%d", $yesterday);
	$month = strftime("%m", $yesterday);
	$year = strftime("%Y", $yesterday);
	rundate($historyfile, $group_id, $month, $day, $year, $yesterday, $today);
}

function generateStats($historyfile, $group_id, $days) {
	for ($i =0; $i<$days; $i++) {
		$day_begin=mktime(0,0,0,5,1 + $i, 2003);
		$day_end=mktime(0,0,0,5,1 + $i +1, 2003);
		$day = strftime("%d", $day_begin);
		$month = strftime("%m", $day_begin);
		$year = strftime("%Y", $day_begin);
		rundate($historyfile, $group_id, $month, $day, $year, $day_begin, $day_end);
	}
}
function delete() {
	$sql = "delete from stats_cvs_group";
	$res = db_query($sql);	
	print db_error();
}

if ($argv[1] != nil && $argv[1] == "delete") {
	delete();
	exit();
}

# get each group
$sql = "select group_id, unix_group_name from groups where status='A'";
$result = db_query($sql);
$ids = util_result_column_to_array($result,0);
$names = util_result_column_to_array($result,1);
for ($i=0; $i<count($ids); $i++) {
	$group_id=$ids[$i];
	$group=$names[$i];
	if (!file_exists($group)) {
		continue;
	}
	
	print "Processing group $group\n";
	$historyfile = file("$cvsroot/$group/CVSROOT/history");
	if (!$historyfile) {
		print "Unable to open history for $group\n";
		continue;
	}
	if ($argv[1] == nil || $argv[1] == "") {
		generateTodayStats($historyfile, $group_id);
	} else {
		generateStats($historyfile, $group_id, $argv[1]);
	}
}

exit;


/*
## Set the time to collect stats for
if ( $ARGV[0] && $ARGV[1] && $ARGV[2] ) {

        $day_begin = mktime( 0, 0, 0, $ARGV[2], $ARGV[1] - 1, $ARGV[0] - 1900 );
        $day_end = mktime( 0, 0, 0, (gmtime( $day_begin + 86400 ))[3,4,5] );
	
$year = $ARGV[0];
$month = $ARGV[1];
	$day = $ARGV[2];

} else {
## Start at midnight last night.
$day_end = mktime( 0, 0, 0, 8,4,2003);
## go until midnight yesterday.
$day_begin = mktime( 0, 0, 0, 8,3,2003);
$year	= strftime("%Y", mktime( $day_begin ) );
$month	= strftime("%m", mktime( $day_begin ) );
$day	= strftime("%d", mktime( $day_begin ) );
}
*/
/*
if (file_exists("$base_log_dir")) {
	$daily_log_file = $base_log_dir."/".sprintf("%04d", $year);
	if (!file_exists("$daily_log_file")) {
		print "Making dest dir \'$daily_log_file\'\n";
		mkdir( $daily_log_file, 0755 ) || die("Could not mkdir $daily_log_file");
	} 
	$daily_log_file = $daily_log_file."/".sprintf("%02d", $month);
	if (!file_exists("$daily_log_file")) {
		print "Making dest dir \'$daily_log_file\'\n";
		mkdir( $daily_log_file, 0755 ) || die("Could not mkdir $daily_log_file");
	}
	$daily_log_file = $daily_log_file."/cvs_traffic_".sprintf("%04d%02d%02d",$year,$month,$day).".log";
} else {
	die("Base log directory $base_log_dir does not exist!");
}

if (!fopen($daily_log_file, "w")) {
	print "Unable to open the log file $daily_log_file\n\n";
	exit;
}
print "Opened log file at \'$daily_log_file\' for writing...\n";
*/
/*
	 ## Now, we'll print all of the results for that project, in the following format:
	 ## (G|U|E)::proj_name::user_name::checkouts::commits::adds
	 ## If 'G', then record is group statistics, and field 2 is a space...
	 ## If 'U', then record is per-user stats, and field 2 is the user name...
	 ## If 'E', then record is an error, and field 1 is a description, there are no other fields.
	if ( $cvs_co || $cvs_commit || $cvs_add ) {
		print "DATA\n";
		print 'DAYS_LOG "G::" . $group . ":: ::" . ($cvs_co?$cvs_co:"0") . "::" . ($cvs_commit?$cvs_commit:"0") . "::" . ($cvs_add?$cvs_add:"0") . "\n"';
		$keys = array_keys($usr_commit);
		foreach ($keys as $key) {
			print 'DAYS_LOG "U::" . $group . "::" . $key . "::0::" . ($usr_commit[$key]?$usr_commit[$key]:"0") . "::" . ($usr_add[$key]?$usr_add[$key]:"0") . "\n"';
		}
	}
*/
?>
