#!/usr/bin/php -q
<?php

require_once('squal_pre.php');
require ('common/include/cron_utils.php');

/**
 *
 * Recurses through the /cvsroot directory tree and parses each projects
 * '~/CVSROOT/history' file, building agregate stats on the number of
 * checkouts, commits, and adds to each project over the past 24 hours.
 *
 * @version	 $Id$
 */

$ARGV = $GLOBALS['argv'];
$verbose = 1;
$cvsroot = "/cvsroot";

if ( $ARGV[1] && $ARGV[2] && $ARGV[3] ) {

	$day_begin = gmmktime( 0, 0, 0, $ARGV[2], $ARGV[3], $ARGV[1] );
	//	$day_begin = timegm( 0, 0, 0, $ARGV[2], $ARGV[1] - 1, $ARGV[0] - 1900 );
	$day_end = $day_begin + 86400;

	$year = $ARGV[1];
	$month = $ARGV[2];
	$day = $ARGV[3];

} else {
	$local_time = localtime();
		## Start at midnight last night.
	$day_end = gmmktime( 0, 0, 0, $local_time[4] + 1, $local_time[3], $local_time[5] );

	//	$day_end = gmmktime( 0, 0, 0, (gmtime( time() ))[3,4,5] );
					 ## go until midnight yesterday.
	$day_begin = $day_end - 86400;
	//	$day_begin = timegm( 0, 0, 0, (gmtime( time() - 86400 ))[3,4,5] );

	$year	= gmstrftime("%Y", $day_begin );
	$month	= gmstrftime("%m", $day_begin );
	$day	= gmstrftime("%d", $day_begin );
}

/*
$err .= <<<EOF
db: $day_begin
de: $day_end
dy: $day
mn: $month
yr: $year
EOF;
*/

$month_string = sprintf( "%04d%02d", $year, $month );
// $err .= "$month_string\n";

if ( $verbose ) {
	$err .= "Parsing cvs logs looking for traffic on day $day, " .
	"month $month, year $year.\n";
}

db_begin();

$root_dir =& opendir( $cvsroot );
while ( $group = readdir( $root_dir ) ) {
	if ( $group == '.' || $group == '..' ) 
		continue;
	if ( ! is_dir( "$cvsroot/$group" ) ) 
		continue;

	//$err .= "\n$group\n\n";

	$group_res = db_query( "SELECT group_id FROM groups WHERE
		unix_group_name='$group' AND
		status='A'" );
	$group_id_row_count = db_numrows($group_res);
	if ( $group_id_row = db_fetch_array($group_res) ) {
		$group_id = $group_id_row['group_id'];
	} else {
		$err .= "Group $group does not appear to be active...	skipping.\n";
		continue;
	}
	if ( $group_id_row_count > 1 ) {
		$err .= "Group results are ambiguous... using group_id $group_id.\n";
	}

	$cvs_co		= 0;
	$cvs_commit = 0;
	$cvs_add	= 0;
	$usr_commit = array();
	$usr_add	= array();

	$hist_file =& fopen( "$cvsroot/$group/CVSROOT/history", 'r' );
	if ( ! $hist_file ) 
		continue;
	$hist_cont = fread( $hist_file, filesize( "$cvsroot/$group/CVSROOT/history" ) );
	fclose( $hist_file );
	$hist_lines = explode( "\n", $hist_cont );

	foreach ( $hist_lines as $hist_line ) {
		if ( preg_match( '/^\s*$/', $hist_line ) ) 
			continue;
		list( $cvstime,$user,$curdir,$module,$rev,$file ) = explode( '|', $hist_line );

		$type = substr($cvstime, 0, 1);
		$time_parsed = hexdec( substr($cvstime, 1, 8) );

		if ( ($time_parsed > $day_begin) && ($time_parsed < $day_end) ) {
			// $err .= "type = $type, tp = $time_parsed\n";

			if ( $type == "M" ) {
				$cvs_commit++;
				$usr_commit{$user}++;

				// $err .= "Commit:	$cvs_commit\n";
				// $err .= "User:		$user\n";
				// $err .= "UserCom: " . $usr_commit{$user} . "\n";
				next;
			}

			if ( $type == "A" ) {
				$cvs_add++;
				$usr_add{$user}++;
				// $err .= "Add	 :	$cvs_add\n";
				// $err .= "User:		$user\n";
				// $err .= "UserAdd: " . $usr_add{$user} . "\n";
				next;
			}

			if ( $type == "O" ) {
				$cvs_co++;
				// we don't care about checkouts on a per-user
				// most of them will be anon anyhow.
				// $err .= "CO		:	$cvs_co\n";
				next;
			}

		} elseif ( $time_parsed > $day_end ) {
			if ( $verbose >= 2 ) {
				$err .= "Short circuting execution, parsed date " .
					"exceeded current threshold.\n";
			}
			break;
		}

	}

	$sql = "INSERT INTO stats_cvs_group
		(month,day,group_id,checkouts,commits,adds)
		VALUES
		('$month_string',
		'$day',
		'$group_id',
		'$cvs_co',
		'$cvs_commit',
		'$cvs_add')";

	if ( $verbose ) 
		$err .= "$sql\n";
	db_query( $sql );
	$err .= db_error();

	$user_list = array_unique( array_merge( array_keys( $usr_add ), array_keys( $usr_commit ) ) );

	foreach ( $user_list as $user ) {
		//$err .= "$user\n";

		$user_res = db_query( "SELECT user_id FROM users WHERE
			user_name='$user'" );
		if ( $user_row = db_fetch_array($user_res) ) {
			$user_id = $user_row['user_id'];
		} else {
			$err .= "User $user was not found...	skipping.\n";
			continue;
		}

		$sql = "INSERT INTO stats_cvs_user
			(month,day,group_id,user_id,commits,adds) VALUES
			('$month_string',
			'$day',
			'$group_id',
			'$user_id',
			'" . ($usr_commit{$user}?$usr_commit{$user}:0) . "',
			'" . ($usr_add{$user}?$usr_add{$user}:0) . "')";

		if ( $verbose ) {
			$err .= "$sql\n";
		}
		db_query( $sql );
		$err .= db_error();

	}
	

}

db_commit();
cron_entry(14,$err);

?>
